<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Helpers\Uploader;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Helpers\StaffAuthHelper;
use App\Models\User\AiKnowledgeVault;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AiKnowledgeVaultController extends Controller
{

    public function index()
    {
        $this->ensureAccess();

        $user = Auth::guard('web')->user();

        $data['vaultItem'] = AiKnowledgeVault::where('user_id', $user->id)->first();

        return view('user.ai-knowledge-vault.index', $data);
    }

    public function store(Request $request)
    {
        $this->ensureAccess();

        if (is_array($request->file('document'))) {
            return response()->json([
                'errors' => [
                    'document' => [__('Only one document can be uploaded at a time.')],
                ],
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'file', 'mimes:pdf,docx,txt,md,csv,json,xml,html,htm,log', 'max:51200'],
            'extracted_text' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray(),
            ], 422);
        }

        $user = Auth::guard('web')->user();
        $file = $request->file('document');
        $userVaultItems = AiKnowledgeVault::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();
        $vaultToKeep = $userVaultItems->first();

        if (!$file) {
            if (!$vaultToKeep) {
                return response()->json([
                    'errors' => [
                        'document' => [__('Please upload a document first.')],
                    ],
                ], 422);
            }

            $payload = [
                'processed_at' => now(),
            ];

            $normalizedText = $this->normalizeNullableText($request->input('extracted_text'));
            if ($normalizedText !== null) {
                $payload['extracted_text'] = $normalizedText;
            }

            $vaultToKeep->update($payload);

            $this->forgetKnowledgeContextCache($user->id);
            Session::flash('success', __('Extracted text updated successfully'));

            return 'success';
        }

        $fileMeta = [
            'original_name' => (string) $file->getClientOriginalName(),
            'extension' => strtolower((string) $file->getClientOriginalExtension()),
            'mime_type' => (string) ($file->getClientMimeType() ?: 'application/octet-stream'),
            'size' => (int) ($file->getSize() ?? 0),
        ];
        $storedFile = $this->storeDocument($user->id, $file);

        try {
            $absolutePath = public_path($storedFile['stored_path'] . '/' . $storedFile['stored_filename']);

            $extractedRaw = $this->extractTextFromFile($absolutePath, $fileMeta['extension']);
            $cleaned = $this->cleanExtractedText($extractedRaw);

            $payload = [
                'user_id' => $user->id,
                'title' => $request->input('title', $fileMeta['original_name']),
                'original_filename' => $fileMeta['original_name'],
                'stored_filename' => $storedFile['stored_filename'],
                'stored_path' => $storedFile['stored_path'],
                'mime_type' => $fileMeta['mime_type'],
                'file_extension' => $fileMeta['extension'],
                'file_size' => $fileMeta['size'],
                'extracted_text' => mb_substr($cleaned, 0, 6000) ?? null,
                'processed_at' => now(),
            ];

            if ($vaultToKeep) {
                $oldStoredPath = $vaultToKeep->stored_path;
                $oldStoredFilename = $vaultToKeep->stored_filename;

                $vaultToKeep->fill($payload);
                $vaultToKeep->save();

                if ($oldStoredPath && $oldStoredFilename) {
                    Uploader::remove(public_path($oldStoredPath), $oldStoredFilename);
                }
            } else {
                $vaultToKeep = AiKnowledgeVault::create($payload);
            }

            $redundantItems = $userVaultItems->filter(function (AiKnowledgeVault $item) use ($vaultToKeep) {
                return (int) $item->id !== (int) $vaultToKeep->id;
            });

            foreach ($redundantItems as $redundantItem) {
                if ($redundantItem->stored_path && $redundantItem->stored_filename) {
                    Uploader::remove(public_path($redundantItem->stored_path), $redundantItem->stored_filename);
                }
                $redundantItem->delete();
            }

            $this->forgetKnowledgeContextCache($user->id);
        } catch (\Throwable $e) {
            Uploader::remove(public_path($storedFile['stored_path']), $storedFile['stored_filename']);

            return response()->json([
                'errors' => [
                    'document' => [__('AI could not process the uploaded file. Please try another file or simplify the content.')],
                ],
            ], 422);
        }

        Session::flash('success', __('Document uploaded and analyzed successfully'));

        return 'success';
    }

    public function destroy(Request $request)
    {
        $this->ensureAccess();

        $userId = Auth::guard('web')->user()->id;
        $vaultId = (int) $request->vault_id;

        $vault = AiKnowledgeVault::where('user_id', $userId)
            ->findOrFail($vaultId);

        if (!empty($vault->stored_path) && !empty($vault->stored_filename)) {
            Uploader::remove(public_path($vault->stored_path), $vault->stored_filename);
        }

        $vault->update([
            'original_filename' => '',
            'stored_filename' => '',
            'stored_path' => '',
            'mime_type' => null,
            'file_extension' => null,
            'file_size' => 0,
        ]);

        $this->forgetKnowledgeContextCache($userId);

        Session::flash('success', __('File removed successfully'));

        return back();
    }

    private function ensureAccess(): void
    {
        abort_unless(StaffAuthHelper::hasPermission('AI Knowledge Vault'), 403);
    }


    private function storeDocument(int $userId, UploadedFile $file): array
    {
        $relativeDirectory = 'assets/tenant/ai-knowledge-vaults/' . $userId . '/' . now()->format('Y/m');
        $absoluteDirectory = public_path($relativeDirectory);
        $uploaded = Uploader::upload_file($absoluteDirectory, $file, $userId);

        return [
            'stored_path' => $relativeDirectory,
            'stored_filename' => $uploaded['uniqueName'],
        ];
    }


    private function extractTextFromFile(string $absolutePath, string $extension): string
    {
        $text = '';

        try {
            switch (strtolower($extension)) {
                case 'txt':
                case 'md':
                case 'log':
                case 'html':
                case 'htm':
                    $raw  = @file_get_contents($absolutePath);
                    $text = is_string($raw) ? strip_tags($raw) : '';
                    break;

                case 'csv':
                    $handle = @fopen($absolutePath, 'r');
                    if ($handle) {
                        $lines = [];
                        while (($row = fgetcsv($handle)) !== false && count($lines) < 200) {
                            $lines[] = implode(', ', $row);
                        }
                        fclose($handle);
                        $text = implode("\n", $lines);
                    }
                    break;

                case 'json':
                    $raw     = @file_get_contents($absolutePath);
                    $decoded = $raw ? json_decode($raw, true) : null;
                    $text    = is_array($decoded)
                        ? json_encode($decoded, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE)
                        : (is_string($raw) ? $raw : '');
                    break;

                case 'xml':
                    $raw  = @file_get_contents($absolutePath);
                    $text = is_string($raw) ? strip_tags($raw) : '';
                    break;

                case 'pdf':
                    try {
                        $config = new \Smalot\PdfParser\Config();
                        $config->setRetainImageContent(false);

                        $parser = new \Smalot\PdfParser\Parser([], $config);
                        $pdf    = $parser->parseFile($absolutePath);
                        $text   = $pdf->getText();

                        if (trim($text) === '') {
                            throw new \RuntimeException('PdfParser returned empty text');
                        }
                    } catch (\Throwable $pdfEx) {
                        $escaped = escapeshellarg($absolutePath);
                        $out     = @shell_exec("pdftotext {$escaped} - 2>/dev/null");
                        $text    = (is_string($out) && trim($out) !== '') ? $out : '';
                    }
                    break;

                case 'docx':
                    // DOCX is a ZIP — extract word/document.xml
                    $zip = new \ZipArchive();
                    if ($zip->open($absolutePath) === true) {
                        $xml  = $zip->getFromName('word/document.xml');
                        $zip->close();
                        $text = $xml ? strip_tags($xml) : '';
                    }
                    break;

                default:
                    $raw  = @file_get_contents($absolutePath);
                    $text = is_string($raw) ? $raw : '';
            }
        } catch (\Throwable $unused) {
        }

        return trim((string) $text);
    }

    private function normalizeNullableText($value): ?string
    {
        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }

    private function forgetKnowledgeContextCache(int $userId): void
    {
        Cache::forget('ai_knowledge_context_' . $userId);
    }

    private function cleanExtractedText(string $text): string
    {
        // Bullet character artifacts
        $text = preg_replace('/^\s*[•·▪▸\-]\s*/m', '', $text);

        // Non-alphanumerics except basic punctuation and common symbols
        $text = preg_replace('/^[^a-zA-Z0-9\x{0980}-\x{09FF}$€£\-\/]+$/mu', '', $text);

        // Multiple blank lines
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Multiple spaces
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);

        return trim($text);
    }
}
