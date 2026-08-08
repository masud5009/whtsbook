<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="@yield('meta-description')">
    <meta name="keywords" content="@yield('meta-keywords')">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @yield('og-meta')
    <title>{{ $bs->website_title }} @yield('pagename')</title>

    <!-- favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/front/img/' . $bs->favicon) }}" type="image/x-icon">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/vendor/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/vendor/fontawesome.min.css">
    <!-- icomoon -->
    <link rel="stylesheet" href="assets/fonts/icomoon/style.css">
    <!-- Link Swiper's CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/vendor/swiper-bundle.min.css">
    <!-- slick slider CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/vendor/slick/slick.css">
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/vendor/slick/slick-theme.css">
    <!-- Aos animate css -->
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/vendor/aos.min.css">
    <!-- wow animate css -->
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/vendor/animate.css">
    <!-- Nice-select -->
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/vendor/nice-select.css">
    <!-- toastr Icon -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/toastr.min.css') }}">
    <!-- whatsapp Style CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/floating-whatsapp.css') }}">
    <!-- magnific Popup -->
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/vendor/magnific-popup.min.css">
    <!-- select2 -->
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/vendor/select2.min.css">

    <!-- bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/vendor/bootstrap.min.css">

    <!-- main style CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/base.css">
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/header/header.css">
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/homepage/home-page.css">
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/popup.css">
    <link rel="stylesheet" href="{{ asset('assets/front/css') }}/footer/footer.css">
    <link rel="stylesheet" href="{{ asset('assets/front/css/cookie-alert.css') }}">

     @if (!request()->routeIs('front.index'))
     <link rel="stylesheet" href="{{ asset('assets/front/css') }}/inner-page.css">
     @endif

    @if ($rtl == 1)
        <link rel="stylesheet" href="{{ asset('assets/front/css/base-rtl.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/front/css/header/header-footer-rtl.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/front/css/homepage/home-page-rtl.css') }}">
    @endif
    @yield('styles')

    <style>
        :root {
            --bs-primary: #{{ $bs->base_color }};
        }

        .page-title-area {
            background-image: url({{ asset('assets/front/img/' . $bs->breadcrumb) }});
        }
    </style>
</head>

<body>

    <!-- pages -->
    <div class="pages">
        @if ($bs->preloader_status == 1)
            <!-- Start preloader -->
            <div id="preLoader" class="preloader">
                <div class="loader">
                    @if (!empty($bs->preloader))
                        <img src="{{ asset('assets/front/img/' . $bs->preloader) }}" alt="preloader"
                            style="max-width: 120px; height: auto;">
                    @else
                        <svg viewBox="0 0 80 80">
                            <rect x="8" y="8" width="64" height="64"></rect>
                        </svg>
                    @endif
                </div>
            </div>
            <!-- End preloader -->
        @endif


        <!-- Magic cursor -->
        <div class="magic-cursor"></div>
        <div class="magic-cursor-inner"></div>

        @include('front.partials.header')

        @if (!request()->routeIs('front.index'))
            <div class="section-breadcrumb  header-next bg-cover bg-img"
                data-bg-image="{{ $bs->breadcrumb ? asset('assets/front/img/' . $bs->breadcrumb) : asset('assets/admin/img/noimage.jpg') }}">
                <div class="overley"></div>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="breadcrumbs-content text-center">
                                <h2 class="mb-14 title">@yield('breadcrumb-title')</h2>
                                <nav aria-label="breadcrumb ">
                                    <ol class="breadcrumb justify-content-center">
                                        <li class="breadcrumb-item"><a
                                                href="{{ route('front.index') }}">{{ __('Home') }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">@yield('breadcrumb-link')</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        @yield('content')

        @includeIf('front.partials.footer')

        <!-- backtotop area start -->
        <div class="scrollToTop">
            <div class="arrowUp">
                <i class="fa-light fa-arrow-up"></i>
            </div>
            <div class="water">
                <svg viewBox="0 0 560 20" class="water_wave water_wave_back">
                    <use xlink:href="#wave"></use>
                </svg>
                <svg viewBox="0 0 560 20" class="water_wave water_wave_front">
                    <use xlink:href="#wave"></use>
                </svg>
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 560 20" style="display: none;">
                    <symbol id="wave">
                        <path
                            d="M420,20c21.5-0.4,38.8-2.5,51.1-4.5c13.4-2.2,26.5-5.2,27.3-5.4C514,6.5,518,4.7,528.5,2.7c7.1-1.3,17.9-2.8,31.5-2.7c0,0,0,0,0,0v20H420z"
                            fill="#"></path>
                        <path
                            d="M420,20c-21.5-0.4-38.8-2.5-51.1-4.5c-13.4-2.2-26.5-5.2-27.3-5.4C326,6.5,322,4.7,311.5,2.7C304.3,1.4,293.6-0.1,280,0c0,0,0,0,0,0v20H420z"
                            fill="#"></path>
                        <path
                            d="M140,20c21.5-0.4,38.8-2.5,51.1-4.5c13.4-2.2,26.5-5.2,27.3-5.4C234,6.5,238,4.7,248.5,2.7c7.1-1.3,17.9-2.8,31.5-2.7c0,0,0,0,0,0v20H140z"
                            fill="#"></path>
                        <path
                            d="M140,20c-21.5-0.4-38.8-2.5-51.1-4.5c-13.4-2.2-26.5-5.2-27.3-5.4C46,6.5,42,4.7,31.5,2.7C24.3,1.4,13.6-0.1,0,0c0,0,0,0,0,0l0,20H140z"
                            fill="#"></path>
                    </symbol>
                </svg>
            </div>
        </div>
        <!-- backtotop area end -->

    </div><!-- End pages -->

    @if ($be->cookie_alert_status == 1)
        <div class="cookie">
            @include('cookie-consent::index')
        </div>
    @endif
    @includeIf('front.partials.popups')
    <div id="WAButton"></div>
    <script>
        "use strict"
        var show_more_text = "{{ __('Show More') }}";
        var show_less_text = "{{ __('Show Less') }}";
    </script>
    <!-- jQuery Js -->
    <script src="{{ asset('assets/front/js') }}/vendor/jquery-min.js"></script>
    <!-- jquery-ui -->
    <script src="{{ asset('assets/front/js') }}/vendor/jquery-ui.min.js"></script>
    <!-- bootstrap -->
    <script src="{{ asset('assets/front/js') }}/vendor/bootstrap.bundle.min.js"></script>
    <!-- swiper -->
    <script src="{{ asset('assets/front/js') }}/vendor/swiper-bundle.min.js"></script>
    <!-- slick Slider js -->
    <script src="{{ asset('assets/front/js') }}/vendor/slick/slick.min.js"></script>
    <!-- nice-select -->
    <script src="{{ asset('assets/front/js') }}/vendor/jquery.nice-select.min.js"></script>
    <!-- Aos js -->
    <script src="{{ asset('assets/front/js') }}/vendor/aos.min.js"></script>
    <!-- wow js -->
    <script src="{{ asset('assets/front/js') }}/vendor/wow.min.js"></script>
    <!-- lazy Image -->
    <script src="{{ asset('assets/front/js') }}/vendor/lazyimage/lazy.image.js"></script>
    <script src="{{ asset('assets/front/js') }}/vendor/lazyimage/lazysizes.min.js"></script>
    <!-- magnific popup -->
    <script src="{{ asset('assets/front/js') }}/vendor/jquery.magnific-popup.min.js"></script>
    <script src="{{ asset('assets/front/js') }}/popup.js"></script>
    <!-- mouse hover tab -->
    <script src="{{ asset('assets/front/js') }}/vendor/mouse-hover-move.js"></script>
    {{-- syotimer --}}
    <script src="{{ asset('assets/front/js/jquery-syotimer.min.js') }}"></script>
    <!-- select2 -->
    <script src="{{ asset('assets/front/js') }}/vendor/select2.min.js"></script>
    <!-- Toastr JS -->
    <script src="{{ asset('assets/front/js/toastr.min.js') }}"></script>
    <!-- svg-injector -->
    <script src="{{ asset('assets/front/js') }}/vendor/svg-injector.min.js"></script>
    <!-- header-menu JS -->
    <script src="{{ asset('assets/front/js') }}/vendor/header-menu.js"></script>
    <!-- back-to-top -->
    <script src="{{ asset('assets/front/js') }}/vendor/back-to-top.js"></script>
    <!-- whats app -->
    <script src="{{ asset('assets/front/js/floating-whatsapp.js') }}"></script>
    <!-- custom -->
    <script src="{{ asset('assets/front/js') }}/script.js"></script>


    <script>
        "use strict";
        var rtl = {{ $rtl }};
        const __Processing__  = "{{ __('Processing...') }}";
    </script>

    @yield('scripts')

    @yield('vuescripts')

    @if (session()->has('success'))
        <script>
            "use strict";
            toastr['success']("{{ __(session('success')) }}");
        </script>
    @endif

    @if (session()->has('error'))
        <script>
            "use strict";
            toastr['error']("{{ __(session('error')) }}");
        </script>
    @endif

    @if (session()->has('warning'))
        <script>
            "use strict";
            toastr['warning']("{{ __(session('warning')) }}");
        </script>
    @endif
    <script>
        "use strict";

        function handleLanguageChange(elm) {
            window.location.href = "{{ route('changeLanguage', '') }}" + "/" + elm.value;
        }

        function handleSelect(elm) {
            handleLanguageChange(elm);
        }
    </script>

    {{-- whatsapp init code --}}
    @if ($bs->is_whatsapp == 1)
        <script type="text/javascript">
            "use strict";
            var whatsapp_popup = {{ $bs->whatsapp_popup }};
            var whatsappImg = "{{ asset('assets/front/img/whatsapp.svg') }}";
            $(function() {

                $('#WAButton').floatingWhatsApp({
                    phone: "{{ $bs->whatsapp_number }}",
                    headerTitle: "{{ $bs->whatsapp_header_title }}",
                    popupMessage: `{!! !empty($bs->whatsapp_popup_message) ? nl2br($bs->whatsapp_popup_message) : '' !!}`,
                    showPopup: whatsapp_popup == 1 ? true : false,
                    buttonImage: '<img src="' + whatsappImg + '" />',
                    position: "right"

                });
            });
        </script>
    @endif

    @if ($bs->is_tawkto == 1)
        @php
            $directLink = str_replace('tawk.to', 'embed.tawk.to', $bs->tawkto_chat_link);
            $directLink = str_replace('chat/', '', $directLink);
        @endphp
        <!--Start of Tawk.to Script-->
        <script type="text/javascript">
            "use strict";
            var Tawk_API = Tawk_API || {},
                Tawk_LoadStart = new Date();
            (function() {
                var s1 = document.createElement("script"),
                    s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src = '{{ $directLink }}';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>

        <!--End of Tawk.to Script-->
    @endif
</body>

</html>
