@php
    $default = \App\Models\Language::where('is_default', 1)->first();
    $admin = Auth::guard('admin')->user();
    if (!empty($admin->role)) {
        $permissions = $admin->role->permissions;
        $permissions = json_decode($permissions, true);
    }
@endphp

<div class="sidebar sidebar-style-2" @if (request()->cookie('admin-theme') == 'dark') data-background-color="dark2" @endif>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <div class="user">
                <div class="avatar-sm float-left mr-2">
                    @if (!empty(Auth::guard('admin')->user()->image))
                        <img src="{{ asset('assets/admin/img/propics/' . Auth::guard('admin')->user()->image) }}"
                            alt="..." class="avatar-img rounded">
                    @else
                        <img src="{{ asset('assets/admin/img/propics/blank_user.jpg') }}" alt="..."
                            class="avatar-img rounded">
                    @endif
                </div>
                <div class="info">
                    <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                        <span>
                            {{ Auth::guard('admin')->user()->first_name }}
                            <span class="user-level">{{ __('Admin') }}</span>
                            <span class="caret"></span>
                        </span>
                    </a>
                    <div class="clearfix"></div>

                    <div class="collapse in" id="collapseExample">
                        <ul class="nav">
                            <li>
                                <a href="{{ route('admin.editProfile') }}">
                                    <span class="link-collapse">{{ __('Edit Profile') }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.changePass') }}">
                                    <span class="link-collapse">{{ __('Change Password') }}</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.logout') }}">
                                    <span class="link-collapse">{{ __('Logout') }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <ul class="nav nav-primary">
                <div class="row mb-2">
                    <div class="col-12">
                        <form action="">
                            <div class="form-group py-0">
                                <input name="term" type="text" class="form-control sidebar-search" value=""
                                    placeholder="{{ __('Search Menu Here') }}..."
                                    style="direction: {{ $dashboard_language->rtl == 1 ? 'rtl' : 'ltr' }} !important">
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Dashboard --}}
                <li class="nav-item @if (request()->path() == 'admin/dashboard') active @endif">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <p>{{ __('Dashboard') }}</p>
                    </a>
                </li>

                {{-- Package --}}
                @if (empty($admin->role) || (!empty($permissions) && in_array('Packages', $permissions)))
                    <li
                        class="nav-item
                    @if (request()->path() == 'admin/package/settings') active
                    @elseif(request()->path() == 'admin/packages') active
                    @elseif(request()->path() == 'admin/package/features') active
                    @elseif(request()->is('admin/package/**/edit')) active
                    @elseif(request()->path() == 'admin/coupon') active
                    @elseif(request()->path() == 'admin/subscription-log') active
                    @elseif(request()->routeIs('admin.coupon.edit')) active @endif">
                        <a data-toggle="collapse" href="#packageManagement">

                            <i class="fas fa-box-open"></i>
                            <p>{{ __('Packages') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->path() == 'admin/package/settings') show
                        @elseif(request()->path() == 'admin/packages') show
                        @elseif(request()->path() == 'admin/package/features') show
                        @elseif(request()->is('admin/package/**/edit')) show
                        @elseif(request()->path() == 'admin/coupon') show
                        @elseif(request()->path() == 'admin/subscription-log') show
                        @elseif(request()->routeIs('admin.coupon.edit')) show @endif"
                            id="packageManagement">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->path() == 'admin/package/settings') active @endif">
                                    <a href="{{ route('admin.package.settings') }}">
                                        <span class="sub-item">{{ __('Settings') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="@if (request()->path() == 'admin/coupon') active
                                @elseif(request()->routeIs('admin.coupon.edit')) active @endif">
                                    <a href="{{ route('admin.coupon.index') }}">
                                        <span class="sub-item">{{ __('Coupons') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/package/features') active @endif">
                                    <a href="{{ route('admin.package.features') }}">
                                        <span class="sub-item">{{ __('Package Features') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="@if (request()->path() == 'admin/packages') active
                                @elseif(request()->is('admin/package/**/edit')) active @endif">
                                    <a href="{{ route('admin.package.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('Packages') }}</span>
                                    </a>
                                </li>

                                <li class="
                    @if (request()->path() == 'admin/subscription-log') active @endif">
                                    <a href="{{ route('admin.payment-log.index') }}">
                                        <span class="sub-item">{{ __('Subscription Log') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if (empty($admin->role) || (!empty($permissions) && in_array('AI Credits', $permissions)))
                    <li
                        class="nav-item
                    @if (request()->routeIs('admin.ai-credit.index')) active @endif
                    @if (request()->routeIs('admin.ai-credit.price-settings')) active @endif
                    ">
                        <a data-toggle="collapse" href="#additional_ai_tokens">
                            <i class="fas fa-coins"></i>
                            <p>{{ __('AI Credits') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->routeIs('admin.ai-credit.index')) show @endif
                        @if (request()->routeIs('admin.ai-credit.price-settings')) show @endif"
                            id="additional_ai_tokens">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->routeIs('admin.ai-credit.price-settings')) active @endif">
                                    <a href="{{ route('admin.ai-credit.price-settings') }}">
                                        <span class="sub-item">{{ __('Price Settings') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->routeIs('admin.ai-credit.index')) active @endif">
                                    <a href="{{ route('admin.ai-credit.index') }}">
                                        <span class="sub-item">{{ __('Credit Purchase Requests') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                <!-- Users Management -->
                @if (empty($admin->role) || (!empty($permissions) && in_array('Users Management', $permissions)))
                    <li
                        class="nav-item
                        @if (request()->path() == 'admin/subscribers') active
                        @elseif(request()->path() == 'admin/mailsubscriber') active
                        @elseif (request()->path() == 'admin/register/users') active
                        @elseif(request()->is('admin/register/user/details/**')) active
                        @elseif(request()->is('admin/edit/register/user/**')) active
                        @elseif (request()->routeIs('admin.register.topup-ai-crdits')) active
                        @elseif (request()->routeIs('register.user.changePass')) active @endif
                        ">
                        <a data-toggle="collapse" href="#User_management">
                            <i class="fas fa-users"></i>
                            <p>{{ __('Users Management') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                                @if (request()->path() == 'admin/subscribers') show
                                @elseif(request()->path() == 'admin/mailsubscriber') show
                                @elseif (request()->path() == 'admin/register/users') show
                                @elseif(request()->is('admin/register/user/details/**')) show
                                @elseif(request()->is('admin/edit/register/user/**')) show
                                @elseif (request()->routeIs('admin.register.topup-ai-crdits')) show
                                @elseif (request()->routeIs('register.user.changePass')) show @endif
                                "
                            id="User_management">
                            <ul class="nav nav-collapse">
                                <li
                                    class="
                                    @if (request()->path() == 'admin/register/users') active
                                    @elseif(request()->is('admin/register/user/details/**')) active
                                    @elseif(request()->is('admin/edit/register/user/**')) active
                                    @elseif (request()->routeIs('admin.register.topup-ai-crdits')) active
                                    @elseif (request()->routeIs('register.user.changePass')) active @endif">
                                    <a href="{{ route('admin.register.user') }}">
                                        <span class="sub-item">{{ __('Registered Users') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="submenu
                                    @if (request()->path() == 'admin/subscribers') selected
                                    @elseif(request()->path() == 'admin/mailsubscriber') selected @endif">
                                    <a data-toggle="collapse" href="#subscribers">
                                        <span class="sub-item">{{ __('Subscribers') }}</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
                                        @if (request()->path() == 'admin/subscribers') show
                                        @elseif(request()->path() == 'admin/mailsubscriber') show @endif"
                                        id="subscribers">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="@if (request()->path() == 'admin/subscribers') active @endif">
                                                <a href="{{ route('admin.subscriber.index') }}">
                                                    <span class="sub-item">{{ __('Subscribers') }}</span>
                                                </a>
                                            </li>
                                            <li class="@if (request()->path() == 'admin/mailsubscriber') active @endif">
                                                <a href="{{ route('admin.mailsubscriber') }}">
                                                    <span class="sub-item">{{ __('Mail to Subscribers') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                <!-- Pages -->
                @if (empty($admin->role) || (!empty($permissions) && in_array('Pages', $permissions)))
                    <li
                        class="nav-item
                @if (request()->path() == 'admin/features') active
                @elseif(request()->path() == 'admin/introsection') active
                @elseif(request()->routeIs('admin.herosection.imgtext')) active
                @elseif(request()->is('admin/feature/**/edit')) active
                @elseif(request()->is('admin/process')) active
                @elseif(request()->is('admin/process/**/edit')) active
                @elseif(request()->path() == 'admin/testimonials') active
                @elseif(request()->is('admin/testimonial/**/edit')) active
                @elseif(request()->path() == 'admin/menu/section') active
                @elseif(request()->path() == 'admin/special/section') active
                @elseif(request()->path() == 'admin/herosection/video') active
                @elseif(request()->path() == 'admin/home-page-text-section') active
                @elseif(request()->path() == 'admin/partners') active
                @elseif(request()->is('admin/partner/**/edit')) active
                @elseif(request()->path() == 'admin/sections') active
                @elseif (request()->path() == 'admin/page/create') active
                @elseif(request()->path() == 'admin/pages') active
                @elseif(request()->is('admin/page/**/edit')) active
                @elseif (request()->path() == 'admin/footers') active
                @elseif(request()->path() == 'admin/ulinks') active
                @elseif (request()->path() == 'admin/bcategorys') active
                @elseif (request()->routeIs('admin.bcategory.edit')) active
                @elseif(request()->path() == 'admin/blogs') active
                @elseif(request()->is('admin/blog/**/edit')) active
                @elseif (request()->path() == 'admin/faqs') active
                @elseif (request()->path() == 'admin/contact') active
                @elseif(request()->routeIs('admin.additional_sections')) active
                @elseif(request()->routeIs('admin.additional_section.create')) active
                @elseif(request()->routeIs('admin.additional_section.edit')) active
                @elseif(request()->path() == 'admin/seo') active
                @elseif(request()->path() == 'admin/breadcrumb') active
                @elseif(request()->routeIs('admin.about_us.additional_section.create')) active
                @elseif(request()->routeIs('admin.about_us.additional_sections')) active
                @elseif(request()->routeIs('admin.about_us.additional_section.edit')) active
                @elseif(request()->routeIs('admin.abouts.section.hide_show')) active
                @elseif(request()->routeIs('admin.error_404')) active
                @elseif(request()->routeIs('admin.slider.index')) active
                @elseif(request()->routeIs('admin.platform_module.index')) active
                @elseif(request()->is('admin/menu-builder')) active
                @elseif (request()->path() == 'admin/breadcrumb') active
                @elseif(request()->path() == 'admin/headings') active
                @elseif(request()->routeIs('admin.aboutpage.imgtext')) active @endif">
                        <a data-toggle="collapse" href="#pages">
                            <i class="fas fa-file"></i>
                            <p>{{ __('Pages') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                    @if (request()->path() == 'admin/features') show
                    @elseif(request()->path() == 'admin/introsection') show
                    @elseif(request()->routeIs('admin.herosection.imgtext')) show
                    @elseif(request()->is('admin/feature/**/edit')) show
                    @elseif(request()->is('admin/process')) show
                    @elseif(request()->is('admin/process/**/edit')) show
                    @elseif(request()->path() == 'admin/testimonials') show
                    @elseif(request()->is('admin/testimonial/**/edit')) show
                    @elseif(request()->path() == 'admin/menu/section') show
                    @elseif(request()->path() == 'admin/special/section') show
                    @elseif(request()->path() == 'admin/herosection/video') show
                    @elseif(request()->path() == 'admin/home-page-text-section') show
                    @elseif(request()->path() == 'admin/partners') show
                    @elseif(request()->is('admin/partner/**/edit')) show
                    @elseif(request()->path() == 'admin/sections') show
                    @elseif (request()->path() == 'admin/page/create') show
                    @elseif(request()->path() == 'admin/pages') show
                    @elseif(request()->is('admin/page/**/edit')) show
                    @elseif (request()->path() == 'admin/footers') show
                    @elseif(request()->path() == 'admin/ulinks') show
                    @elseif (request()->path() == 'admin/bcategorys') show
                    @elseif(request()->path() == 'admin/blogs') show
                    @elseif (request()->routeIs('admin.bcategory.edit')) show
                    @elseif(request()->is('admin/blog/**/edit')) show
                    @elseif (request()->path() == 'admin/faqs') show
                    @elseif (request()->path() == 'admin/contact') show
                    @elseif(request()->routeIs('admin.additional_sections')) show
                    @elseif(request()->routeIs('admin.additional_section.create')) show
                    @elseif(request()->routeIs('admin.additional_section.edit')) show
                    @elseif(request()->path() == 'admin/seo') show
                    @elseif(request()->path() == 'admin/breadcrumb') show
                    @elseif(request()->routeIs('admin.about_us.additional_section.create')) show
                    @elseif(request()->routeIs('admin.about_us.additional_sections')) show
                    @elseif(request()->routeIs('admin.about_us.additional_section.edit')) show
                    @elseif(request()->routeIs('admin.abouts.section.hide_show')) show
                    @elseif(request()->is('admin/menu-builder')) show
                    @elseif (request()->path() == 'admin/breadcrumb') show
                    @elseif(request()->routeIs('admin.error_404')) show
                    @elseif(request()->routeIs('admin.slider.index')) show
                    @elseif(request()->routeIs('admin.platform_module.index')) show
                    @elseif(request()->path() == 'admin/headings') show
                    @elseif(request()->routeIs('admin.aboutpage.imgtext')) show @endif"
                            id="pages">
                            <ul class="nav nav-collapse">
                                <!---======= Home pages ==========--->
                                <li
                                    class="submenu
                        @if (request()->path() == 'admin/features') selected
                        @elseif(request()->path() == 'admin/introsection') selected
                        @elseif(request()->routeIs('admin.herosection.imgtext')) selected
                        @elseif(request()->is('admin/feature/**/edit')) selected
                        @elseif(request()->is('admin/process')) selected
                        @elseif(request()->is('admin/process/**/edit')) selected
                        @elseif(request()->path() == 'admin/testimonials') selected
                        @elseif(request()->is('admin/testimonial/**/edit')) selected
                        @elseif(request()->path() == 'admin/menu/section') selected
                        @elseif(request()->path() == 'admin/special/section') selected
                        @elseif(request()->path() == 'admin/herosection/video') selected
                        @elseif(request()->path() == 'admin/home-page-text-section') selected
                        @elseif(request()->path() == 'admin/partners') selected
                        @elseif(request()->is('admin/partner/**/edit')) selected
                        @elseif(request()->path() == 'admin/sections') selected
                        @elseif(request()->routeIs('admin.additional_sections')) selected
                        @elseif(request()->routeIs('admin.additional_section.create')) selected
                        @elseif(request()->routeIs('admin.slider.index')) selected
                        @elseif(request()->routeIs('admin.platform_module.index')) selected
                        @elseif(request()->routeIs('admin.additional_section.edit')) selected @endif">
                                    <a data-toggle="collapse" href="#home">
                                        <span class="sub-item">{{ __('Home Page') }}</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
                                @if (request()->path() == 'admin/features') show
                                @elseif(request()->path() == 'admin/introsection') show
                                @elseif(request()->routeIs('admin.herosection.imgtext')) show
                                @elseif(request()->is('admin/feature/**/edit')) show
                                @elseif(request()->is('admin/process')) show
                                @elseif(request()->is('admin/process/**/edit')) show
                                @elseif(request()->path() == 'admin/testimonials') show
                                @elseif(request()->is('admin/testimonial/**/edit')) show
                                @elseif(request()->path() == 'admin/special/section') show
                                @elseif(request()->path() == 'admin/home-page-text-section') show
                                @elseif(request()->path() == 'admin/partners') show
                                @elseif(request()->is('admin/partner/**/edit')) show
                                @elseif(request()->path() == 'admin/sections') show
                                @elseif(request()->routeIs('admin.additional_sections')) show
                                @elseif(request()->routeIs('admin.additional_section.create')) show
                                @elseif(request()->routeIs('admin.slider.index')) show
                                @elseif(request()->routeIs('admin.platform_module.index')) show
                                @elseif(request()->routeIs('admin.additional_section.edit')) show @endif"
                                        id="home">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="@if (request()->routeIs('admin.herosection.imgtext')) active @endif">
                                                <a
                                                    href="{{ route('admin.herosection.imgtext') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Images & Texts') }}</span>
                                                </a>
                                            </li>
                                            <li class="@if (request()->routeIs('admin.slider.index')) active @endif">
                                                <a
                                                    href="{{ route('admin.slider.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Hero Section Sliders') }}</span>
                                                </a>
                                            </li>
                                            <li class="@if (request()->routeIs('admin.platform_module.index')) active @endif">
                                                <a
                                                    href="{{ route('admin.platform_module.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Platform Modules') }}</span>
                                                </a>
                                            </li>


                                            <li
                                                class="@if (request()->path() == 'admin/features') active
                                        @elseif(request()->is('admin/feature/**/edit')) active @endif">
                                                <a
                                                    href="{{ route('admin.feature.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Features') }}</span>
                                                </a>
                                            </li>

                                            <li
                                                class=" @if (request()->path() == 'admin/process') active
                                @elseif(request()->is('admin/process/**/edit')) active @endif">
                                                <a
                                                    href="{{ route('admin.process.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Work Process') }}</span>
                                                </a>
                                            </li>

                                            <li
                                                class="@if (request()->path() == 'admin/testimonials') active
                                        @elseif(request()->is('admin/testimonial/**/edit')) active @endif">
                                                <a
                                                    href="{{ route('admin.testimonial.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Testimonials') }}</span>
                                                </a>
                                            </li>

                                            <li
                                                class="@if (request()->path() == 'admin/partners') active
                                        @elseif(request()->is('admin/partner/**/edit')) active @endif">
                                                <a
                                                    href="{{ route('admin.partner.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Partners') }}</span>
                                                </a>
                                            </li>

                                            <!---==== Additional Section ====--->
                                            <li class="submenu">
                                                <a data-toggle="collapse" href="#hoem-addi-section"
                                                    aria-expanded="{{ request()->routeIs('admin.additional_sections') ||
                                                    request()->routeIs('admin.additional_section.create') ||
                                                    request()->routeIs('admin.additional_section.edit')
                                                        ? 'true'
                                                        : 'false' }}">
                                                    <span class="sub-item">{{ __('Additional Sections') }}</span>
                                                    <span class="caret"></span>
                                                </a>
                                                <div id="hoem-addi-section"
                                                    class="collapse
                                      @if (request()->routeIs('admin.additional_sections') ||
                                              request()->routeIs('admin.additional_section.create') ||
                                              request()->routeIs('admin.additional_section.edit')) show @endif pl-3">
                                                    <ul class="nav nav-collapse subnav">
                                                        <li
                                                            class="{{ request()->routeIs('admin.additional_section.create') ? 'active' : '' }}">
                                                            <a href="{{ route('admin.additional_section.create') }}">
                                                                <span class="sub-item">{{ __('Add Section') }}</span>
                                                            </a>
                                                        </li>
                                                        <li
                                                            class="{{ request()->routeIs('admin.additional_sections') || request()->routeIs('admin.additional_section.edit') ? 'active' : '' }}">
                                                            <a
                                                                href="{{ route('admin.additional_sections', ['language' => $default->code]) }}">
                                                                <span class="sub-item">{{ __('Sections') }}
                                                                </span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>

                                            <!---==== End additional Section ====--->

                                            <li class="@if (request()->path() == 'admin/sections') active @endif">
                                                <a href="{{ route('admin.sections.index') }}">
                                                    <span class="sub-item">{{ __('Section Hide / Show') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <!---======= End home pages ==========--->

                                <!---=== About us ======-->
                                <li
                                    class="submenu
                            @if (request()->routeIs('admin.abouts.section.hide_show')) selected
                            @elseif (request()->routeIs('admin.about_us.additional_sections')) selected
                            @elseif (request()->routeIs('admin.about_us.additional_section.create')) selected
                            @elseif (request()->routeIs('admin.about_us.additional_section.edit')) selected
                            @elseif (request()->routeIs('admin.aboutpage.imgtext')) selected @endif">
                                    <a data-toggle="collapse" href="#aboutUs_section"
                                        aria-expanded="
                            @if (request()->routeIs('admin.abouts.section.hide_show')) true
                            @else false @endif">
                                        <span class="sub-item">{{ __('About Us') }}</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
                            @if (request()->routeIs('admin.abouts.section.hide_show')) show
                            @elseif (request()->routeIs('admin.about_us.additional_sections')) show
                            @elseif (request()->routeIs('admin.about_us.additional_section.create')) show
                            @elseif (request()->routeIs('admin.about_us.additional_section.edit')) show
                            @elseif (request()->routeIs('admin.aboutpage.imgtext')) show @endif"
                                        id="aboutUs_section">
                                        <ul class="nav nav-collapse subnav">

                                            <li class="@if (request()->routeIs('admin.aboutpage.imgtext')) active @endif">
                                                <a
                                                    href="{{ route('admin.aboutpage.imgtext') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Images & Texts') }}</span>
                                                </a>
                                            </li>
                                            <li class="submenu">
                                                <a data-toggle="collapse" href="#about-addition-section"
                                                    aria-expanded="{{ request()->routeIs('admin.about_us.additional_sections') ||
                                                    request()->routeIs('admin.about_us.additional_section.create') ||
                                                    request()->routeIs('admin.about_us.additional_section.edit')
                                                        ? 'true'
                                                        : 'false' }}">
                                                    <span class="sub-item">{{ __('Additional Sections') }}</span>
                                                    <span class="caret"></span>
                                                </a>
                                                <div id="about-addition-section"
                                                    class="collapse
                                    @if (request()->routeIs('admin.about_us.additional_sections') ||
                                            request()->routeIs('admin.about_us.additional_section.create') ||
                                            request()->routeIs('admin.about_us.additional_section.edit')) show @endif pl-3">
                                                    <ul class="nav nav-collapse subnav">
                                                        <li
                                                            class="{{ request()->routeIs('admin.about_us.additional_section.create') ? 'active' : '' }}">
                                                            <a
                                                                href="{{ route('admin.about_us.additional_section.create') }}">
                                                                <span class="sub-item">{{ __('Add Section') }}</span>
                                                            </a>
                                                        </li>
                                                        <li
                                                            class="{{ request()->routeIs('admin.about_us.additional_sections') || request()->routeIs('admin.about_us.additional_section.edit') ? 'active' : '' }}">
                                                            <a
                                                                href="{{ route('admin.about_us.additional_sections', ['language' => $default->code]) }}">
                                                                <span class="sub-item">{{ __('Sections') }}
                                                                </span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <li class="@if (request()->routeIs('admin.abouts.section.hide_show')) active @endif">
                                                <a href="{{ route('admin.abouts.section.hide_show') }}">
                                                    <span class="sub-item">{{ __('Section Hide/Show') }}</span>
                                                </a>
                                            </li>

                                        </ul>
                                    </div>
                                </li>
                                <!---===End about us ======-->


                                <!---======= Additional pages ==========--->
                                <li
                                    class="submenu
                            @if (request()->path() == 'admin/page/create') selected
                            @elseif(request()->path() == 'admin/pages') selected
                            @elseif(request()->is('admin/page/**/edit')) selected @endif">
                                    <a data-toggle="collapse" href="#additional_page">
                                        <span class="sub-item">{{ __('Additional Pages') }}</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
                            @if (request()->path() == 'admin/page/create') show
                            @elseif(request()->path() == 'admin/pages') show
                            @elseif(request()->is('admin/page/**/edit')) show @endif"
                                        id="additional_page">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="@if (request()->path() == 'admin/page/create') active @endif">
                                                <a href="{{ route('admin.page.create') }}">
                                                    <span class="sub-item">{{ __('Add Page') }}</span>
                                                </a>
                                            </li>
                                            <li
                                                class="
                                @if (request()->path() == 'admin/pages') active
                                @elseif(request()->is('admin/page/**/edit')) active @endif">
                                                <a
                                                    href="{{ route('admin.page.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('All Pages') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <!---=======End additional pages ==========--->
                                <!--=== Blogs ===--->
                                <li
                                    class="submenu
                            @if (request()->path() == 'admin/bcategorys') selected
                            @elseif(request()->routeIs('admin.bcategory.edit')) selected
                            @elseif(request()->path() == 'admin/blogs') selected
                            @elseif(request()->is('admin/blog/**/edit')) selected @endif">
                                    <a data-toggle="collapse" href="#blog">
                                        <span class="sub-item">{{ __('Blog') }}</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
                        @if (request()->path() == 'admin/bcategorys') show
                        @elseif(request()->routeIs('admin.bcategory.edit')) show
                        @elseif(request()->path() == 'admin/blogs') show
                        @elseif(request()->is('admin/blog/**/edit')) show @endif"
                                        id="blog">
                                        <ul class="nav nav-collapse subnav">
                                            <li
                                                class=" @if (request()->path() == 'admin/bcategorys') active
                                                        @elseif(request()->routeIs('admin.bcategory.edit')) active @endif">
                                                <a
                                                    href="{{ route('admin.bcategory.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Categories') }}</span>
                                                </a>
                                            </li>
                                            <li
                                                class="
                            @if (request()->path() == 'admin/blogs') active
                            @elseif(request()->is('admin/blog/**/edit')) active @endif">
                                                <a
                                                    href="{{ route('admin.blog.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Posts') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <!--=== End Blogs ===--->


                                <!--=== Faq Managements ===-->
                                <li class="@if (request()->path() == 'admin/faqs') active @endif">
                                    <a href="{{ route('admin.faq.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('FAQs') }}</span>
                                    </a>
                                </li>
                                <!--===End faq Managements ===-->

                                <!--=== Contact page ===-->
                                <li class="@if (request()->path() == 'admin/contact') active @endif">
                                    <a href="{{ route('admin.contact.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('Contact Page') }}</span>
                                    </a>
                                </li>
                                <!--===End contact page ===-->

                                <!--=== 404 page ===-->
                                <li class="@if (request()->routeIs('admin.error_404')) active @endif">
                                    <a href="{{ route('admin.error_404') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('404 Page') }}</span>
                                    </a>
                                </li>
                                <!--===End 404 page ===-->

                                <li
                                    class="submenu
                                        @if (request()->path() == 'admin/menu-builder') active @endif">
                                    <a href="{{ route('admin.menu_builder.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('Menu Builder') }}</span>
                                    </a>
                                </li>

                                <!--- === Footer pages ==-->
                                <li
                                    class="submenu
                                        @if (request()->path() == 'admin/footers') selected
                                        @elseif(request()->path() == 'admin/ulinks') selected @endif">
                                    <a data-toggle="collapse" href="#footer">
                                        <span class="sub-item">{{ __('Footer') }}</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
                                        @if (request()->path() == 'admin/footers') show
                                        @elseif(request()->path() == 'admin/ulinks') show @endif"
                                        id="footer">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="@if (request()->path() == 'admin/footers') active @endif">
                                                <a
                                                    href="{{ route('admin.footer.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Image & Text') }}</span>
                                                </a>
                                            </li>
                                            <li class="@if (request()->path() == 'admin/ulinks') active @endif">
                                                <a
                                                    href="{{ route('admin.ulink.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Useful Links') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <!--- === End footer pages ==-->

                                <!--===breadcrumb page ===-->
                                <li
                                    class="submenu
                                        @if (request()->path() == 'admin/breadcrumb') selected
                                        @elseif(request()->path() == 'admin/headings') selected @endif">
                                    <a data-toggle="collapse" href="#breadcrumbs">
                                        <span class="sub-item">{{ __('Breadcrumbs') }}</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
                                        @if (request()->path() == 'admin/breadcrumb') show
                                        @elseif(request()->path() == 'admin/headings') show @endif"
                                        id="breadcrumbs">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="@if (request()->path() == 'admin/breadcrumb') active @endif">
                                                <a href="{{ route('admin.breadcrumb') }}">
                                                    <span class="sub-item">{{ __('Image') }}</span>
                                                </a>
                                            </li>
                                            <li class="@if (request()->path() == 'admin/headings') active @endif">
                                                <a
                                                    href="{{ route('admin.breadcrumb.heading', ['language' => $default->code]) }}">
                                                    <span class="sub-item">{{ __('Headings') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <!--===breadcrumb page ===-->

                                <!--=== Seo page ===-->
                                <li class="@if (request()->path() == 'admin/seo') active @endif">
                                    <a href="{{ route('admin.seo', ['language' => $default->code]) }}">
                                        <span class="sub-item">{{ __('SEO Information') }}</span>
                                    </a>
                                </li>
                                <!--=== Seo page ===-->

                            </ul>
                        </div>
                    </li>
                @endif
                <!---======= End pages ==========--->

                {{-- Support Tickest --}}
                @if (empty($admin->role) || (!empty($permissions) && in_array('Support Tickets', $permissions)))
                    <li
                        class="nav-item
                    @if (request()->path() == 'admin/all/tickets') active
                    @elseif(request()->path() == 'admin/pending/tickets') active
                    @elseif(request()->path() == 'admin/open/tickets') active
                    @elseif(request()->routeIs('admin.ticket.messages'))active
                    @elseif(request()->path() == 'admin/closed/tickets') active
                    @elseif(request()->path() == 'admin/create/tickets') active @endif">
                        <a data-toggle="collapse" href="#ticket">
                            <i class="fas fa-ticket-alt"></i>
                            <p>{{ __('Support Tickets') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->path() == 'admin/all/tickets') show
                        @elseif(request()->path() == 'admin/pending/tickets') show
                        @elseif(request()->path() == 'admin/open/tickets') show
                        @elseif(request()->path() == 'admin/closed/tickets') show
                        @elseif(request()->routeIs('admin.ticket.messages'))show
                        @elseif(request()->path() == 'admin/create/tickets') show @endif"
                            id="ticket">
                            <ul class="nav nav-collapse">
                                <li
                                    class="@if (request()->path() == 'admin/all/tickets') active
                                           @elseif(request()->routeIs('admin.ticket.messages')) active @endif">
                                    <a href="{{ route('admin.tickets.all') }}">
                                        <span class="sub-item">{{ __('All Tickets') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/pending/tickets') active @endif">
                                    <a href="{{ route('admin.tickets.pending') }}">
                                        <span class="sub-item">{{ __('Pending Tickets') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/open/tickets') active @endif">
                                    <a href="{{ route('admin.tickets.open') }}">
                                        <span class="sub-item">{{ __('Open Tickets') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/closed/tickets') active @endif">
                                    <a href="{{ route('admin.tickets.closed') }}">
                                        <span class="sub-item">{{ __('Closed Tickets') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif


                {{-- Announcement Popup --}}
                @if (empty($admin->role) || (!empty($permissions) && in_array('Announcement Popup', $permissions)))
                    <li
                        class="nav-item
                    @if (request()->path() == 'admin/popup/create') active
                    @elseif(request()->path() == 'admin/popup/types') active
                    @elseif(request()->is('admin/popup/**/edit')) active
                    @elseif(request()->path() == 'admin/popups') active @endif">
                        <a data-toggle="collapse" href="#announcementPopup">
                            <i class="fas fa-bullhorn"></i>
                            <p>{{ __('Announcement Popup') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->path() == 'admin/popup/create') show
                        @elseif(request()->path() == 'admin/popup/types') show
                        @elseif(request()->path() == 'admin/popups') show
                        @elseif(request()->is('admin/popup/**/edit')) show @endif"
                            id="announcementPopup">
                            <ul class="nav nav-collapse">
                                <li
                                    class="@if (request()->path() == 'admin/popup/types') active
                                @elseif(request()->path() == 'admin/popup/create') active @endif">
                                    <a href="{{ route('admin.popup.types') }}">
                                        <span class="sub-item">{{ __('Add Popup') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="@if (request()->path() == 'admin/popups') active
                                @elseif(request()->is('admin/popup/**/edit')) active @endif">
                                    <a href="{{ route('admin.popup.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('Popups') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if (empty($admin->role) || (!empty($permissions) && in_array('Settings', $permissions)))
                    {{-- Basic Settings --}}
                    <li
                        class="nav-item
          @if (request()->path() == 'admin/basicinfo') active
          @elseif(request()->path() == 'admin/social') active
          @elseif(request()->is('admin/social/**')) active
          @elseif(request()->path() == 'admin/heading') active
          @elseif(request()->path() == 'admin/script') active
          @elseif(request()->path() == 'admin/maintainance') active
          @elseif(request()->path() == 'admin/cookie-alert') active
          @elseif(request()->path() == 'admin/mail-from-admin') active
          @elseif(request()->path() == 'admin/mail-to-admin') active
          @elseif(request()->path() == 'admin/email-templates') active
          @elseif(request()->routeIs('admin.product.tags')) active
          @elseif(request()->routeIs('admin.email.edit_mail_template')) active
          @elseif(request()->is('admin/edit_mail_template/**')) active
          @elseif(request()->routeIs('admin.mail_templates')) active
        @elseif (request()->path() == 'admin/languages') active
        @elseif(request()->is('admin/language/**/edit')) active
        @elseif(request()->is('admin/language/**/edit/keyword')) active
        @elseif(request()->is('admin/language/**/admin-dashboard/keyword')) active
        @elseif(request()->is('admin/language/**/user-dashboard/keyword')) active
        @elseif(request()->is('admin/language/**/user-frontend/keyword')) active
        @elseif (request()->path() == 'admin/gateways') active
        @elseif(request()->path() == 'admin/offline/gateways') active
        @elseif(request()->routeIs('admin.cache.clear')) active
        @elseif (request()->routeIs('admin.sitemap.index')) active @endif">
                        <a data-toggle="collapse" href="#basic">
                            <i class="far fa-cog"></i>
                            <p>{{ __('Settings') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
            @if (request()->path() == 'admin/basicinfo') show
            @elseif(request()->path() == 'admin/social') show
            @elseif(request()->is('admin/social/**')) show
            @elseif(request()->path() == 'admin/heading') show
            @elseif(request()->path() == 'admin/script') show
            @elseif(request()->path() == 'admin/maintainance') show
            @elseif(request()->path() == 'admin/cookie-alert') show
            @elseif(request()->path() == 'admin/mail-from-admin') show
            @elseif(request()->path() == 'admin/mail-to-admin') show
            @elseif(request()->path() == 'admin/email-templates') show
            @elseif(request()->routeIs('admin.product.tags')) show
            @elseif(request()->is('admin/edit_mail_template/**')) show
            @elseif(request()->routeIs('admin.email.edit_mail_template')) show
            @elseif(request()->routeIs('admin.mail_templates')) show
            @elseif (request()->path() == 'admin/languages') show
            @elseif(request()->is('admin/language/**/edit')) show
            @elseif(request()->is('admin/language/**/edit/keyword')) show
            @elseif(request()->is('admin/language/**/admin-dashboard/keyword')) show
            @elseif(request()->is('admin/language/**/user-dashboard/keyword')) show
            @elseif(request()->is('admin/language/**/user-frontend/keyword')) show
            @elseif (request()->path() == 'admin/gateways') show
            @elseif(request()->path() == 'admin/offline/gateways') show
            @elseif(request()->routeIs('admin.cache.clear')) show
            @elseif (request()->routeIs('admin.sitemap.index')) show @endif"
                            id="basic">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->path() == 'admin/basicinfo') active @endif">
                                    <a href="{{ route('admin.basicinfo') }}">
                                        <span class="sub-item">{{ __('General Settings') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="submenu
                                @if (request()->routeIs('admin.mail_from_admin')) selected
                                @elseif (request()->routeIs('admin.mail_to_admin')) selected
                                @elseif (request()->routeIs('admin.mail_templates')) selected
                                @elseif (request()->routeIs('admin.edit_mail_template')) selected @endif">
                                    <a data-toggle="collapse" href="#emailset"
                                        aria-expanded="{{ request()->path() == 'admin/mail-from-admin' || request()->path() == 'admin/mail-to-admin' || request()->routeIs('admin.mail_templates') || request()->routeIs('admin.edit_mail_template') ? 'true' : 'false' }}">
                                        <span class="sub-item">{{ __('Email Settings') }}</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse {{ request()->path() == 'admin/mail-from-admin' || request()->path() == 'admin/mail-to-admin' || request()->routeIs('admin.mail_templates') || request()->routeIs('admin.edit_mail_template') ? 'show' : '' }}"
                                        id="emailset">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="@if (request()->path() == 'admin/mail-from-admin') active @endif">
                                                <a href="{{ route('admin.mailFromAdmin') }}">
                                                    <span class="sub-item">{{ __('Mail from Admin') }}</span>
                                                </a>
                                            </li>
                                            <li class="@if (request()->path() == 'admin/mail-to-admin') active @endif">
                                                <a href="{{ route('admin.mailToAdmin') }}">
                                                    <span class="sub-item">{{ __('Mail to Admin') }}</span>
                                                </a>
                                            </li>
                                            <li
                                                class=" @if (request()->routeIs('admin.mail_templates')) active
                                @elseif (request()->routeIs('admin.edit_mail_template')) active @endif">
                                                <a href="{{ route('admin.mail_templates') }}">
                                                    <span class="sub-item">{{ __('Mail Templates') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>

                                <li
                                    class="submenu
                                        @if (request()->path() == 'admin/gateways') selected
                                        @elseif(request()->path() == 'admin/offline/gateways') selected @endif">
                                    <a data-toggle="collapse" href="#gateways">
                                        <span class="sub-item">{{ __('Payment Gateways') }}</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
                                        @if (request()->path() == 'admin/gateways') show
                                        @elseif(request()->path() == 'admin/offline/gateways') show @endif"
                                        id="gateways">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="@if (request()->path() == 'admin/gateways') active @endif">
                                                <a href="{{ route('admin.gateway.index') }}">
                                                    <span class="sub-item">{{ __('Online Gateways') }}</span>
                                                </a>
                                            </li>
                                            <li class="@if (request()->path() == 'admin/offline/gateways') active @endif">
                                                <a
                                                    href="{{ route('admin.gateway.offline') . '?language=' . $default->code }}">
                                                    <span class="sub-item">{{ __('Offline Gateways') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>

                                <li
                                    class="submenu
                                    @if (request()->path() == 'admin/languages') active
                                    @elseif(request()->is('admin/language/**/edit')) active
                                    @elseif(request()->is('admin/language/**/edit/keyword')) active
                                    @elseif(request()->is('admin/language/**/admin-dashboard/keyword')) active
                                    @elseif(request()->is('admin/language/**/user-dashboard/keyword')) active
                                    @elseif(request()->is('admin/language/**/user-frontend/keyword')) active @endif">
                                    <a href="{{ route('admin.language.index') }}">
                                        <span class="sub-item">{{ __('Languages') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/script') active @endif">
                                    <a href="{{ route('admin.script') }}">
                                        <span class="sub-item">{{ __('Plugins') }}</span>
                                    </a>
                                </li>

                                <li class="@if (request()->path() == 'admin/maintainance') active @endif">
                                    <a href="{{ route('admin.maintainance') }}">
                                        <span class="sub-item">{{ __('Maintainance Mode') }}</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/cookie-alert') active @endif">
                                    <a href="{{ route('admin.cookie.alert') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('Cookie Alert') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="@if (request()->path() == 'admin/social') active
                                @elseif(request()->is('admin/social/**')) active @endif">
                                    <a href="{{ route('admin.social.index') }}">
                                        <span class="sub-item">{{ __('Social Links') }}</span>
                                    </a>
                                </li>

                                {{-- Sitemap --}}
                                <li
                                    class="
                                    @if (request()->routeIs('admin.sitemap.index')) active @endif">
                                    <a href="{{ route('admin.sitemap.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">{{ __('Sitemap') }}</span>
                                    </a>
                                </li>

                                {{-- ====== Cache Clear ====== --}}
                                <li class="@if (request()->routeIs('admin.cache.clear')) active @endif">
                                    <a href="{{ route('admin.cache.clear') }}">
                                        <span class="sub-item">{{ __('Clear Cache') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif


                {{-- ====== Admins Management Page === --}}
                @if (empty($admin->role) || (!empty($permissions) && in_array('Admins Management', $permissions)))
                    <li
                        class="nav-item @if (request()->path() == 'admin/users-admin') active
                                @elseif(request()->is('admin/user/**/edit')) active
                                @elseif (request()->path() == 'admin/roles') active
                                @elseif(request()->is('admin/role/**/permissions/manage')) active @endif">
                        <a data-toggle="collapse" href="#adminManagement">
                            <i class="fas fa-users-cog"></i>
                            <p>{{ __('Admins Management') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                                @if (request()->path() == 'admin/users-admin') show
                                @elseif(request()->is('admin/user/**/edit')) show
                                @elseif (request()->path() == 'admin/roles') show
                                @elseif(request()->is('admin/role/**/permissions/manage')) show @endif"
                            id="adminManagement">
                            <ul class="nav nav-collapse">
                                <li
                                    class="
                                        @if (request()->path() == 'admin/roles') active
                                        @elseif(request()->is('admin/role/**/permissions/manage')) active @endif">
                                    <a href="{{ route('admin.role.index') }}">
                                        <span class="sub-item">{{ __('Role & Permissions') }}</span>
                                    </a>
                                </li>

                                <li
                                    class="@if (request()->path() == 'admin/users-admin') active
                                        @elseif(request()->is('admin/user/**/edit')) active @endif">
                                    <a href="{{ route('admin.user.index') }}">
                                        <span class="sub-item">{{ __('Registered Admins') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

            </ul>
        </div>
    </div>
</div>
