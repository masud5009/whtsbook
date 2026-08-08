<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> {{ $userBs->website_title }} | {{ $details->title }}</title>
    <link rel="icon"
        href="{{ \App\Http\Helpers\Uploader::getImageUrl(Constant::WEBSITE_FAVICON, $userBs->favicon) }}">
    <link rel="stylesheet" href="{{ asset('assets/tenant-front/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tenant-front/css/magnific-popup.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tenant-front/css/room-details.css') }}">

    <style>
        :root {
            --color-primary: #{{ $userBs->primary_color }};
            --color-secondary: #{{ $userBs->secondary_color }};
        }
    </style>

</head>

<body>
    <div class="py-4"></div>

    <main class="container pb-5">
        <div class="row g-4 align-items-start">

            <!-- LEFT CONTENT -->
            <div class="col-lg-8">

                @php
                    $sliderImages = json_decode($details->room->slider_imgs, true) ?? [];
                    $firstMedia = $sliderImages[0] ?? null;
                    $videoExts = ['mp4', 'webm', 'ogg', 'mov'];
                    $keywords = json_decode($defaultLang->keywords, true);
                @endphp

                <div class="room-gallery card border-0 shadow-sm overflow-hidden">
                    @if ($firstMedia)
                        @php
                            $firstExt = strtolower(pathinfo($firstMedia, PATHINFO_EXTENSION));
                            $firstIsVideo = in_array($firstExt, $videoExts);
                            $firstPath = $firstIsVideo
                                ? asset('assets/tenant/videos/rooms/' . $firstMedia)
                                : asset('assets/tenant/img/rooms/slider_images/' . $firstMedia);
                        @endphp
                        <div id="mainRoomMedia">
                            @if ($firstIsVideo)
                                <video class="w-100 room-hero" controls>
                                    <source src="{{ $firstPath }}">
                                </video>
                            @else
                                <a class="gallery-item" href="{{ $firstPath }}">
                                    <img class="w-100 room-hero" src="{{ $firstPath }}" alt="Room">
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="p-5 text-center text-muted">
                            {{ $keywords['No image found'] ?? 'No image found' }}
                        </div>
                    @endif

                    <div class="thumb-strip p-3">
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach ($sliderImages as $index => $image)
                                @php
                                    $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
                                    $isVideo = in_array($ext, $videoExts);
                                    $mediaPath = $isVideo
                                        ? asset('assets/tenant/videos/rooms/' . $image)
                                        : asset('assets/tenant/img/rooms/slider_images/' . $image);
                                @endphp
                                <a class="thumb gallery-thumb {{ $index === 0 ? 'active' : '' }}"
                                    href="{{ $isVideo ? '#' : $mediaPath }}" data-src="{{ $mediaPath }}"
                                    data-type="{{ $isVideo ? 'video' : 'image' }}">
                                    @if ($isVideo)
                                        <video muted>
                                            <source src="{{ $mediaPath }}">
                                        </video>
                                    @else
                                        <img src="{{ $mediaPath }}" alt="">
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h1 class="room-title mb-2">{{ convertUtf8($details->title) }}</h1>
                </div>

                <!-- Tabs layout like screenshot -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4 col-lg-3">
                                <div class="nav flex-md-column nav-pills room-pills" id="roomTab" role="tablist">
                                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-price"
                                        type="button">
                                        {{ $keywords['Price Details'] ?? 'Price Details' }}
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-details"
                                        type="button">
                                        {{ $keywords['Room Details'] ?? 'Room Details' }}
                                    </button>
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-amenities"
                                        type="button">
                                        {{ $keywords['Amenities'] ?? 'Amenities' }}
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-8 col-lg-9">
                                <div class="tab-content">

                                    <!-- Price tab (improved formatting) -->
                                    <div class="tab-pane fade show active" id="tab-price">
                                        <h5 class="fw-bold mb-3">
                                            {{ $keywords['Price Details'] ?? 'Price Details' }}
                                        </h5>

                                        <div class="season-box">
                                            <div class="price-summary">
                                                <div class="rowx">
                                                    <span>{{ $keywords['Regular Price'] ?? 'Regular Price' }}</span>
                                                    <strong>{{ userPriceFormat($details->user_id, $details->regular_price) }}</strong>
                                                </div>

                                                @if ($details->weekend_price > 0)
                                                    <div class="rowx">
                                                        <span>{{ $keywords['Weekend Price'] ?? 'Weekend Price' }}</span>
                                                        <strong>{{ userPriceFormat($details->user_id, $details->weekend_price) }}</strong>
                                                    </div>
                                                @endif

                                                @if ($details->seasonal_price > 0)
                                                    <div class="rowx">
                                                        <span>{{ $keywords['Seasonal Price'] ?? 'Seasonal Price' }}</span>
                                                        <strong>{{ userPriceFormat($details->user_id, $details->seasonal_price) }}</strong>
                                                    </div>
                                                @endif

                                                @if ($details->seasonal_weekend_price > 0)
                                                    <div class="rowx">
                                                        <span>{{ $keywords['Seasonal Weekend Price'] ?? 'Seasonal Weekend Price' }}</span>
                                                        <strong>{{ userPriceFormat($details->user_id, $details->seasonal_weekend_price) }}</strong>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="divider"></div>

                                            <div class="small text-muted">
                                                @if (!empty($details->weekend))
                                                    <div class="mb-2">
                                                        <strong class="text-dark">
                                                            {{ $keywords['Weekend Days'] ?? 'Weekend Days' }}:
                                                        </strong>
                                                        {{ implode(', ', explode(',', $details->weekend)) }}
                                                    </div>
                                                @endif

                                                @if (!empty($details->seasonal_dates))
                                                    <div class="mb-2">
                                                        <strong class="text-dark">
                                                            {{ $keywords['Seasonal Dates Range'] ?? 'Seasonal Dates Range' }}:
                                                        </strong><br>
                                                        @php $dates = json_decode($details->seasonal_dates, true) ?? []; @endphp
                                                        @foreach ($dates as $date)
                                                            <div>
                                                                • {{ $date['start'] ?? '' }}
                                                                {{ $keywords['to'] ?? 'to' }}
                                                                {{ $date['end'] ?? '' }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if (!empty($details->seasonal_weekend))
                                                    <div>
                                                        <strong class="text-dark">
                                                            {{ $keywords['Seasonal Weekend Days'] ?? 'Seasonal Weekend Days' }}:
                                                        </strong>
                                                        {{ implode(', ', explode(',', $details->seasonal_weekend)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Details tab -->
                                    <div class="tab-pane fade" id="tab-details">
                                        <h5 class="fw-bold mb-2">
                                            {{ $keywords['Room Details'] ?? 'Room Details' }}
                                        </h5>
                                        <div class="summernote-content">
                                            {!! replaceBaseUrl($details->description, 'summernote') !!}
                                        </div>
                                    </div>

                                    <!-- Amenities tab -->
                                    <div class="tab-pane fade" id="tab-amenities">
                                        <h5 class="fw-bold mb-3">{{ $keywords['Amenities'] ?? 'Amenities' }}</h5>
                                        <div class="row g-2">
                                            @foreach ($amms as $key => $amm)
                                                <div class="col-sm-6">
                                                    <div class="review d-flex align-items-center gap-2">
                                                        <span class="fw-semibold">{{ $amm }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div><!-- tab-content -->
                            </div>
                        </div><!-- row -->
                    </div><!-- card-body -->
                </div>

            </div>

            <!-- RIGHT SIDEBAR (VIEW ONLY) -->
            <div class="col-lg-4">
                <div class="summary-sticky">
                    <div class="card border-0 shadow-sm info-card">
                        <div class="info-head">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div>
                                    <div class="info-price">
                                        {{ userPriceFormat($details->user_id, $details->regular_price) }}
                                        <span class="per">/ {{ $keywords['Night'] ?? 'Night' }}</span>
                                    </div>
                                    <span>{{ $keywords['Regular Price'] ?? 'Regular Price' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-3 p-lg-4">
                            <div class="facts">
                                <div class="fact">
                                    <div class="k">{{ $keywords['Beds'] ?? 'Beds' }}</div>
                                    <div class="v">{{ $details->room->bed }}</div>
                                </div>
                                <div class="fact">
                                    <div class="k">{{ $keywords['Baths'] ?? 'Baths' }}</div>
                                    <div class="v">{{ $details->room->bath }}</div>
                                </div>
                                <div class="fact">
                                    <div class="k">{{ $keywords['Adults'] ?? 'Adults' }}</div>
                                    <div class="v">{{ $details->room->adult ?? 0 }}</div>
                                </div>
                                <div class="fact">
                                    <div class="k">{{ $keywords['Children'] ?? 'Children' }}</div>
                                    <div class="v">{{ $details->room->child ?? 0 }}</div>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="price-summary">
                                <div class="rowx">
                                    <span>{{ $keywords['Regular Price'] ?? 'Regular Price' }}</span>
                                    <strong>{{ userPriceFormat($details->user_id, $details->regular_price) }}</strong>
                                </div>
                                @if ($details->weekend_price > 0)
                                    <div class="rowx">
                                        <span>{{ $keywords['Weekend Price'] ?? 'Weekend Price' }}</span>
                                        <strong>{{ userPriceFormat($details->user_id, $details->weekend_price) }}</strong>
                                    </div>
                                @endif
                                @if ($details->seasonal_price > 0)
                                    <div class="rowx">
                                        <span>{{ $keywords['Seasonal Price'] ?? 'Seasonal Price' }}</span>
                                        <strong>{{ userPriceFormat($details->user_id, $details->seasonal_price) }}</strong>
                                    </div>
                                @endif
                                @if ($details->seasonal_weekend_price > 0)
                                    <div class="rowx">
                                        <span>{{ $keywords['Seasonal Weekend'] ?? 'Seasonal Weekend' }}</span>
                                        <strong>{{ userPriceFormat($details->user_id, $details->seasonal_weekend_price) }}</strong>
                                    </div>
                                @endif
                            </div>

                            <div class="divider"></div>

                            <div class="small text-muted">
                                <div class="d-flex gap-2 align-items-start mb-2">
                                    <span>{{ $keywords['Customers can only view details from this page.'] ?? 'Customers can only view details from this page.' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- row -->
    </main>

    <script src="{{ asset('assets/tenant-front/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/tenant-front/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/tenant-front/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/tenant-front/js/room-details.js') }}"></script>
</body>

</html>
