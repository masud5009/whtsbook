<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class BasicSetting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'language_id',
        'features_image',
        'features_title',
        'features_subtitle',
        'platform_modules_section_title',
        'platform_modules_section_bg_image',
        'team_section_title',
        'team_section_subtitle',
        'process_section',
        'featured_users_section',
        'pricing_section',
        'partners_section',
        'testimonial_section',
        'top_footer_section',
        'copyright_section',
        'footer_text',
        'copyright_text',
        'footer_logo',
        'maintainance_mode',
        'maintainance_text',
        'maintenance_img',
        'maintenance_status',
        'secret_path',
        'base_color2',
        'base_color',
        'about_additional_section_status',
        'additional_section_status',
        'about_features_section_status',
        'about_partner_section_status',
        'about_blog_section_status',
        'work_process_title',
        'work_process_subtitle',
        'preview_templates_title',
        'preview_templates_subtitle',
        'featured_users_title',
        'featured_users_subtitle',
        'partner_title',
        'partner_subtitle',
        'pricing_title',
        'testimonial_title',
        'blog_title',
        'blog_subtitle',
        'price_per_token',
        'gemini_price_per_token',
        'openai_price_per_token',
        'faq_title',
        'home_section',
        'features_section',
        'platform_modules_section',
        'faq_section',
        'additional_sections',
        'latitude',
        'longitude',
    ];

    public function language()
    {
        return $this->belongsTo('App\Models\Language');
    }

    public static function getAiPricing(?self $settings = null): array
    {
        static $hasProviderPriceColumns;

        $hasProviderPriceColumns ??=
            Schema::hasColumn('basic_settings', 'gemini_price_per_token') &&
            Schema::hasColumn('basic_settings', 'openai_price_per_token');

        if (!$settings) {
            $query = static::query()->select('ai_name', 'price_per_token');

            if ($hasProviderPriceColumns) {
                $query->addSelect('gemini_price_per_token', 'openai_price_per_token');
            }

            $settings = $query->first();
        }

        $legacyPrice = (string) ($settings->price_per_token ?? 0);
        $provider = $settings->ai_name ?? 'gemini';

        $geminiPrice = $hasProviderPriceColumns
            ? (string) ($settings->gemini_price_per_token ?? $legacyPrice)
            : $legacyPrice;

        $openaiPrice = $hasProviderPriceColumns
            ? (string) ($settings->openai_price_per_token ?? $legacyPrice)
            : $legacyPrice;

        return [
            'provider' => $provider,
            'legacy' => $legacyPrice,
            'gemini' => $geminiPrice,
            'openai' => $openaiPrice,
            'current' => $provider === 'openai' ? $openaiPrice : $geminiPrice,
        ];
    }
}
