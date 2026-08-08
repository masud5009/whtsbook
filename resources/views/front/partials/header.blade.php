<header class="header-area">
    <nav class="navbar navbar-expand-xl hover-menu">
        <div class="container">
            <!-- Logo - Dynamic -->
            <a class="navbar-brand" href="{{ route('front.index') }}" target="_self">
                <img src="{{ $bs->logo ? asset('assets/front/img/' . $bs->logo) : asset('assets/tenant/img/defaultlogo.png') }}"
                    alt="{{ $bs->website_title ?? 'Brand Logo' }}">
            </a>
            <button class="menu-toggler d-block d-xl-none" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#mobilemenu-offcanvas" aria-controls="mobilemenu-offcanvas">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="collapse navbar-collapse" id="main_nav">
                <ul id="mainMenu" class="navbar-nav justify-content-center ms-auto">
                    @php
                        $links = json_decode($menus, true);
                    @endphp

                    @foreach ($links as $link)
                        @php
                            $href = getHref($link);
                        @endphp

                        @if (!array_key_exists('children', $link))
                            <!-- Single menu item -->
                            <li class="nav-item">
                                <a class="nav-link" target="{{ $link['target'] }}" href="{{ $href }}">
                                    {{ strtoupper($link['text']) }}
                                </a>
                            </li>
                        @else
                            <!-- Dropdown menu item -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                    {{ strtoupper($link['text']) }}
                                </a>
                                <ul class="dropdown-menu shadow">
                                    @foreach ($link['children'] as $level2)
                                        @php
                                            $l2Href = getHref($level2);
                                        @endphp

                                        @if (!array_key_exists('children', $level2))
                                            <li>
                                                <a class="dropdown-item" href="{{ $l2Href }}"
                                                    target="{{ $level2['target'] }}">
                                                    {{ $level2['text'] }}
                                                </a>
                                            </li>
                                        @else
                                            <li>
                                                <a class="dropdown-item submenu-toggle"
                                                    href="#">{{ $level2['text'] }}</a>
                                                <ul class="submenu dropdown-menu shadow">
                                                    @foreach ($level2['children'] as $level3)
                                                        <li>
                                                            <a class="dropdown-item" href="{{ getHref($level3) }}"
                                                                target="{{ $level3['target'] }}">
                                                                {{ $level3['text'] }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                </ul>

                <div class="navbar-right ms-auto">
                    <div class="language">
                        <i class="fa-solid fa-globe"></i>
                        @if (!empty($currentLang))
                            <select class="niceselect nice-select" onchange="handleLanguageChange(this)">
                                @foreach ($langs as $lang)
                                    <option value="{{ $lang->code }}"
                                        {{ $currentLang->code === $lang->code ? 'selected' : '' }}>
                                        {{ $lang->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    @guest
                        <a class="user-btn" href="{{ route('user.login') }}">
                            {{ __('Login') }} <i class="fa-regular fa-arrow-right"></i>
                        </a>
                    @else
                        <a class="user-btn" href="{{ route('user-dashboard') }}">
                            {{ __('Dashboard') }} <i class="fa-regular fa-arrow-right"></i>
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>
</header>


<!-- Start Mobile-menu -->
<div class="offcanvas mobilemenuoffcanvas offcanvas-start" data-bs-scroll="true" data-bs-backdrop="true" tabindex="-1"
    id="mobilemenu-offcanvas">
    <div class="offcanvas-header align-items-center justify-content-between px-20 pt-20">
        <a class="navbar-brand" href="{{ route('front.index') }}">
            <img width="150" class="lazyload blur-up"
                src="{{ $bs->logo ? asset('assets/front/img/' . $bs->logo) : asset('assets/tenant/img/defaultlogo.png') }}"
                alt="logo">
        </a>
        <a href="#" class="menu-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="fa-light fa-xmark"></i>
        </a>
    </div>
    <div class="offcanvas-body">
        <!-- mobile-menu clone -->
        <nav id="mobileMenu" class="mobile-menu mb-40">

        </nav>
        <!-- menu-action-item-wrapper -->
        <div class="menu-action-item-wrapper">
            <div class="navbar-right">
                <div class="language">
                    <i class="fa-solid fa-globe"></i>
                    @if (!empty($currentLang))
                        <select class="niceselect nice-select" onchange="handleLanguageChange(this)">
                            @foreach ($langs as $lang)
                                <option value="{{ $lang->code }}"
                                    {{ $currentLang->code === $lang->code ? 'selected' : '' }}>
                                    {{ $lang->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
                @guest
                    <a class="user-btn" href="{{ route('user.login') }}">
                        {{ __('Login') }} <i class="fa-regular fa-arrow-right"></i>
                    </a>
                @else
                    <a class="user-btn" href="{{ route('user-dashboard') }}">
                        {{ __('Dashboard') }} <i class="fa-regular fa-arrow-right"></i>
                    </a>
                @endguest
            </div>

        </div>
    </div>
</div>
<!-- End Mobile-menu -->
