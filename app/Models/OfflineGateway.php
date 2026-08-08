<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineGateway extends Model
{
    protected $fillable = ['id', 'name', 'short_description', 'instructions', 'serial_number', 'status', 'is_receipt', 'receipt'];

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
}
