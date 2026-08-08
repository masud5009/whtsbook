<!doctype html>
<html lang="en" lang="{{ $defaultLang->code }}" dir="{{ $defaultLang->rtl == 1 ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('pageTitle')</title>
    <link rel="icon"
        href="{{ \App\Http\Helpers\Uploader::getImageUrl(Constant::WEBSITE_FAVICON, $userBs->favicon) }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/vendor/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/vendor/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tenant-front/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tenant-front/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tenant-front/css/payment-link.css') }}">
</head>

<body>
    @php
        $preloaderInfo = DB::table('user_basic_settings')
            ->where('user_id', $booking->user_id)
            ->select('preloader_status', 'preloader')
            ->first();
    @endphp

    @if ($preloaderInfo && $preloaderInfo->preloader_status == 1)
        <div id="preloader">
            <img src="{{ asset('assets/tenant/img/favicon/' . $preloaderInfo->preloader) }}" alt="Loading...">
        </div>
    @endif

    <div class="topbar mb-4">
        <div class="container">
            <nav class="navbar navbar-expand-lg py-2">
                {{-- Left: Hotel name --}}
                <a class="navbar-brand d-flex align-items-center gap-2 m-0"
                    href="{{ route('payment.redirect', ['id' => $booking->id]) }}">
                    <span class=" m-0">
                        <span class="brand">
                            <img src="{{ asset('assets/tenant/img/logo/' . $userBs->logo) }}" alt=""
                                width="150">
                        </span>
                    </span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topbarNav"
                    aria-controls="topbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>


                <div class="collapse navbar-collapse" id="topbarNav">
                    <div
                        class="ms-auto d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 gap-lg-3 py-2 py-lg-0">

                        <div class="lang-wrap">
                            <select class="form-select lang-select" id="topbarLanguage" name="language"
                                data-href="{{ route('user.langaugeChange', ['code' => '__CODE__', 'user_id' => '__USER_ID__']) }}">
                                @foreach ($languages as $language)
                                    <option value="{{ $language->code }}" data-user_id="{{ $language->user_id }}"
                                        {{ session('user_lang_' . $language->user_id) == $language->code ? 'selected' : '' }}>
                                        {{ $language->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>


    <div class="container pb-5">
        @yield('content')
    </div>

    <script src="{{ asset('assets/tenant-front/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/tenant-front/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/tenant-front/js/select2.min.js') }}"></script>
    <script>
        $(window).on('load', function() {
            $('#preloader').fadeOut(400);
        });
        $("#topbarLanguage").select2({
            width: "100%",
            minimumResultsForSearch: Infinity
        });
        $('#topbarLanguage').on('change', function() {
            let code = $(this).val();
            let user_id = $(this).find('option:selected').data('user_id');
            let base = $(this).data('href');

            let url = base.replace('__CODE__', code).replace('__USER_ID__', user_id);

            window.location.href = url;
        });
    </script>

    @yield('scripts')
</body>

</html>
