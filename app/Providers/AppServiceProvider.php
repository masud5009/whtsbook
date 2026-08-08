<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\Social;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use App\Models\User\UserPermission;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use App\Http\Helpers\UserPermissionHelper;
use App\Models\User\Language as TenantLanguage;
use App\Models\User\BasicSetting as UserBasciSettings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function changePreferences($userId)
    {
        $currentPackage = UserPermissionHelper::currentPackage($userId);

        $preference = UserPermission::where([
            ['user_id', $userId],
        ])->first();

        // if current package does not match with 'package_id' of 'user_permissions' table, then change 'package_id' in 'user_permissions'
        if (!empty($currentPackage) && ($currentPackage->id != $preference->package_id)) {
            $preference->package_id = $currentPackage->id;

            $features = !empty($currentPackage->features) ? json_decode($currentPackage->features, true) : [];
            $features[] = "Contact";
            $preference->permissions = json_encode($features);
            $preference->package_id = $currentPackage->id;
            $preference->save();
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();


        if (!app()->runningInConsole()) {
            $socials = Social::orderBy('serial_number', 'ASC')->get();
            $langs = Language::all();
            $data = DB::table('user_basic_settings')->find(75);

            View::composer('*', function ($view) {
                if (session()->has('lang')) {
                    $currentLang = Language::where('code', session()->get('lang'))->first();
                } else {
                    $currentLang = Language::where('is_default', 1)->first();
                }

                $bs = $currentLang->basic_setting;
                $be = $currentLang->basic_extended;

                if (Menu::where('language_id', $currentLang->id)->count() > 0) {
                    $menus = Menu::where('language_id', $currentLang->id)->first()->menus;
                } else {
                    $menus = json_encode([]);
                }

                if ($currentLang->rtl == 1) {
                    $rtl = 1;
                } else {
                    $rtl = 0;
                }

                $view->with('bs', $bs);
                $view->with('be', $be);
                $view->with('currentLang', $currentLang);
                $view->with('menus', $menus);
                $view->with('rtl', $rtl);
            });

            View::composer(['admin.*'], function ($view) {
                if (session()->has('admin_lang')) {
                    $lang_code = str_replace('admin_', '', session()->get('admin_lang'));
                    $language = Language::where('code', $lang_code)->first();
                    if (empty($language)) {
                        $language = Language::where('is_default', 1)->first();
                    }
                } else {
                    $language = Language::where('is_default', 1)->first();
                }
                View::share('default', $language);
                View::share('dashboard_language', $language);
            });

            View::composer(['user.*'], function ($view) {
                if (Auth::check()) {
                    $userId = Auth::guard('web')->user()->id;
                    $this->changePreferences($userId);
                    $userBs = UserBasciSettings::query()
                        ->where('user_id', Auth::user()->id)
                        ->first();
                    $view->with('userBs', $userBs);

                    $language = TenantLanguage::where('is_default', 1)->where('user_id', Auth::guard('web')->user()->id)->first();

                    //for translate tenant dashboard start
                    $userDashboardLang = null;
                    if (Session::has('user_lang')) {
                        $userDashboardLang = TenantLanguage::where('user_id', Auth::guard('web')->user()->id)->where('code', Session::get('user_lang'))->first();
                    }
                    if (is_null($userDashboardLang)) {
                        $userDashboardLang = TenantLanguage::where('user_id', Auth::guard('web')->user()->id)->where('dashboard_default', 1)->first();
                    }
                    //not extis dashboard language

                    Session::put('user_lang', $userDashboardLang->code);
                    app()->setLocale('user_' . $userDashboardLang->code);

                    $cacheKey = 'context_slots_' . 8801306084771 . '_' . 83;
                    // dd(Cache::get($cacheKey));

                    $view->with('defaultLang', $language);
                    $view->with('currentLanguageInfo', $language);
                    $view->with('dashboard_language', $userDashboardLang);
                }
            });

            View::share('langs', $langs);
            View::share('defaultLang', $langs);
            View::share('socials', $socials);
            View::share(['userBs' => $data]);
        }
    }
}
