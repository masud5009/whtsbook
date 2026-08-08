<?php

namespace App\Models\User;

use App\Traits\UserGatewayStore;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use UserGatewayStore;

    public $timestamps = false;
    public $table = "user_payment_gateways";

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'keyword',
        'information',
        'status'
    ];

    public function convertAutoData()
    {
        return json_decode($this->information, true);
    }

    public function getAutoDataText()
    {
        $text = $this->convertAutoData();
        return is_array($text) ? (string) end($text) : '';
    }

    public function showKeyword()
    {
        return $this->keyword == null ? 'other' : $this->keyword;
    }

    public function showForm()
    {
        $data = $this->keyword == null ? 'other' : $this->keyword;
        $values = ['paypal'];
        return in_array($data, $values) ? 'no' : 'yes';
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
}
