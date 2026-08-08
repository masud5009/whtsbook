<?php

namespace App\Traits;

use App\Models\User\PaymentGateway;

trait UserGatewayStore
{
    /**
     * Store/update gateway settings (config driven).
     */
    public static function storeGateway($request, int $userId): array
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
                'user_id' => $userId,
                'keyword' => $keyword,
            ],
            $request->except(['_token', 'information', 'keyword']) + [
                'user_id' => $userId,
                'status' => (int) $request->status,
                'keyword' => $keyword,
                'name' => $meta['name'],
                'type' => $meta['type'],
                'information' => json_encode($information, JSON_UNESCAPED_UNICODE),
            ]
        );

        return ['status' => 'success', 'message' => __('Updated Successfully')];
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
    public static function allGateways(int $userId): array
    {
        $keywords = config('payment_gateways.keywords', []);
        $forms = config('payment_gateways.forms', []);

        $rows = PaymentGateway::query()
            ->where('user_id', $userId)
            ->whereIn('keyword', $keywords)
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
}
