<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\AiUsageTokenService;

class AiService
{
    protected $apiKey;
    protected $apiUrl;
    protected $provider;
    protected $model;

    public function __construct()
    {
        $bs = DB::table('basic_settings')
            ->select('ai_name', 'ai_api_key', 'ai_model_name')
            ->first();

        $this->apiKey = $bs->ai_api_key ?? null;
        $this->provider = $bs->ai_name ?? 'gemini';
        $this->model = $bs->ai_model_name ?? null;

        if ($this->provider === 'gemini') {
            $this->model = $this->model ?: 'gemini-1.5-flash';
            $this->apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
        } else {
            $this->model = $this->model ?: 'gpt-4o-mini';
            $this->apiUrl = 'https://api.openai.com/v1/chat/completions';
        }
    }

    public function getAiResponse($userMessage, $userId)
    {
        try {
            //Api key or url missing, log error for debugging
            if (empty($this->apiKey) || empty($this->apiUrl)) {
                return null;
            }

            if ($this->provider === 'openai') {
                $response = $this->openAiResponse($userMessage, $userId);
            } else {
                $response = $this->geminiResponse($userMessage, $userId);
            }

            if (!($response['status'] ?? false)) {
                return null;
            }

            if (($response['totalUsageToken'] ?? 0) > 0) {
                $ok = AiUsageTokenService::consumeAiTokens($userId, $response['totalUsageToken']);

                //insufficient tokens or race condition
                if (!$ok) {
                    return null;
                }
            }

            return trim($response['output'] ?? '');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function geminiResponse($userMessage, $userId)
    {
        $response = Http::retry(3, 2000)
            ->timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $userMessage]
                        ]
                    ]
                ]
            ]);

        if (!$response->successful()) {
            return [
                'status' => false,
                'totalUsageToken' => 0,
                'output' => null,
            ];
        }

        $data = $response->json();
        $usage = $data['usageMetadata'] ?? [];
        $totalTokens = (int) ($usage['totalTokenCount'] ?? 0);

        return [
            'status' => true,
            'totalUsageToken' => $totalTokens,
            'output' => trim($data['candidates'][0]['content']['parts'][0]['text'] ?? '')
        ];
    }

    private function openAiResponse($userMessage, $userId)
    {
        $response = Http::retry(3, 2000)
            ->timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $userMessage,
                    ]
                ],
            ]);

        if (!$response->successful()) {
            return [
                'status' => false,
                'totalUsageToken' => 0,
                'output' => null,
            ];
        }

        $data = $response->json();

        return [
            'status' => true,
            'totalUsageToken' => (int) ($data['usage']['total_tokens'] ?? 0),
            'output' => trim($data['choices'][0]['message']['content'] ?? '')
        ];
    }
}
