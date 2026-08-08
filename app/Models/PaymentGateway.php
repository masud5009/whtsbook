<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = ['title', 'details', 'subtitle', 'name', 'type', 'information', 'keyword'];
    public $timestamps = false;

    public function convertAutoData()
    {
        return json_decode($this->information, true);
    }

    public function getAutoDataText()
    {
        $text = $this->convertAutoData();
        return end($text);
    }

    public function showKeyword()
    {
        $data = $this->keyword == null ? 'other' : $this->keyword;
        return $data;
    }

    public function showForm()
    {
        $show = '';
        $data = $this->keyword == null ? 'other' : $this->keyword;
        $values = ['paypal'];
        if (in_array($data, $values)) {
            $show = 'no';
        } else {
            $show = 'yes';
        }
        return $show;
    }

    public function getNameAttribute($value)
    {
        return $this->normalizeGatewayName($value);
    }

    protected function normalizeGatewayName($value)
    {
        if (is_array($value)) {
            return $this->normalizeGatewayNameArray($value);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeGatewayNameArray($decoded);
            }
        }

        return $value;
    }

    protected function normalizeGatewayNameArray(array $value): string
    {
        $locale = app()->getLocale();
        if (isset($value[$locale]) && is_scalar($value[$locale])) {
            return (string) $value[$locale];
        }

        $first = reset($value);
        return is_scalar($first) ? (string) $first : '';
    }


    /**
     * Get validation rules from config.
     */
    public static function validationRules(string $keyword): array
    {
        $forms = config('payment_gateways.forms', []);
        if (!isset($forms[$keyword])) {
            return [
                'keyword' => 'required|in:' . implode(',', array_keys($forms)),
            ];
        }
        $base = [
            'keyword' => 'required|in:' . implode(',', array_keys($forms)),
            'status' => 'required|in:0,1',
        ];

        $rules = $forms[$keyword]['rules'] ?? [];
        unset($rules['keyword'], $rules['status']);

        return $base + $rules;
    }

    /**
     * Return gateways list for Blade.
     */
    public static function allGateways()
    {
        $keywords = config('payment_gateways.keywords', []);
        $forms = config('payment_gateways.forms', []);

        $rows = PaymentGateway::whereIn('keyword', $keywords)
            ->get()
            ->keyBy('keyword');

        $gateways = [];

        foreach ($forms as $keyword => $schema) {
            $gateways[] = [
                'keyword' => $keyword,
                'title' => $schema['title'] ?? ucfirst($keyword),
                'gateway' => $rows[$keyword] ?? null,
                'radios' => $schema['radios'] ?? [],
                'fields' => $schema['fields'] ?? [],
            ];
        }

        return $gateways;
    }

    /**
     * Store/update gateway settings (config driven).
     */
    public static function storeGateway($request)
    {
        $keyword = (string) ($request->keyword ?? '');
        $forms = config('payment_gateways.forms', []);

        if (!$keyword || !isset($forms[$keyword])) {
            return ['status' => 'error', 'message' => __('Invalid gateway keyword')];
        }

        $schema = $forms[$keyword];

        $meta = $schema['meta'] ?? [
            'name' => $schema['title'] ?? ucfirst($keyword),
            'type' => 'automatic'
        ];

        // Build information from config fields
        $infoFields = $schema['info_fields'] ?? [];
        $information = [];

        foreach ($infoFields as $field) {
            $information[$field] = $request->{$field} ?? null;
        }

        // Optional default text (show on checkout)
        $information['text'] = $schema['text'] ?? 'Pay via your account.';

        foreach (['sandbox_check'] as $boolField) {
            if (array_key_exists($boolField, $information)) {
                // Keep null if not sent, else cast to 0/1
                $information[$boolField] = is_null($information[$boolField]) ? null : (int) $information[$boolField];
            }
        }

        PaymentGateway::query()->updateOrCreate(
            [
                'keyword' => $keyword,
            ],
            $request->except(['_token', 'information', 'keyword']) + [
                'status' => (int) $request->status,
                'keyword' => $keyword,
                'name' => $meta['name'],
                'type' => $meta['type'],
                'information' => json_encode($information, JSON_UNESCAPED_UNICODE),
            ]
        );

        return ['status' => 'success', 'message' => __('Updated Successfully')];
    }
}
