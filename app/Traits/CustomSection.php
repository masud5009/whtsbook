<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Http\Helpers\UserPermissionHelper;

trait CustomSection
{

    public static function AdminFrontHomePage()
    {
        return [
            'hero_section',
            'partner_section',
            'work_process_section',
            'features_section',
            'platform_module_section',
            'pricing_section',
            'faq_section',
            'testimonial_section',
        ];
    }
    public static function AdminFrontAboutPage()
    {
        return [
            'features_section',
            'work_process_section',
            'testimonial_section',
            'blog_section',
        ];
    }
    public static function AboutUsPage()
    {
        return [
            'about_info_section',
            'facility_section',
            'testimonial_section',
        ];
    }
}
