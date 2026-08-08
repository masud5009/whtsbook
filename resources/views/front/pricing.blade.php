@extends('front.layout')

@section('pagename')
    - {{ __('Pricing') }}
@endsection

@section('meta-description', !empty($seo) ? $seo->pricing_meta_description : '')
@section('meta-keywords', !empty($seo) ? $seo->pricing_meta_keywords : '')

@section('breadcrumb-title', !empty($heading) ? $heading->pricing_title : __('Pricing'))
@section('breadcrumb-link', !empty($heading) ? $heading->pricing_title : __('Pricing'))


@section('content')
    <section class="pricing-area pt-lg-100 pt-60 pb-90">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                        @if (count($terms) > 1)
                            <div class="tabs-navigation pricing-tab-navigation mb-40 text-center mx-auto">
                                <ul class="nav nav-tabs" data-hover="fancyHover">
                                    @foreach ($terms as $term)
                                        <li class="nav-item {{ $loop->first ? 'active' : '' }}">
                                            <button class="nav-link hover-effect {{ $loop->first ? 'active' : '' }}"
                                                data-bs-toggle="tab" data-bs-target="#{{ __($term) }}"
                                                type="button">{{ __($term) }}</button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if (count($terms) > 0)
                <div class="tab-content" id="pricingTabContent" data-aos="fade-up" data-aos-delay="300">
                    @foreach ($terms as $term)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ __($term) }}"
                            role="tabpanel" tabindex="1">
                            <div class="row">
                                @php
                                    $packages = \App\Models\Package::where('status', '1')
                                        ->where('term', strtolower($term))
                                        ->get();
                                @endphp
                                @if (count($packages) == 0)
                                    <div class="col-12">
                                        <div class="bg-light text-center py-5 d-block w-100">
                                            <h3>{{ __('No Pricing Plan Found!') }}</h3>
                                        </div>
                                    </div>
                                @else
                                    @foreach ($packages as $package)
                                        @php
                                            $pFeatures = json_decode($package->features, true);
                                            $priceLabel = $package->price == 0 ? __('Free') : $package->price;
                                            $currencyLeft =
                                                $package->price != 0 && $be->base_currency_symbol_position == 'left'
                                                    ? $be->base_currency_symbol
                                                    : '';
                                            $currencyRight =
                                                $package->price != 0 && $be->base_currency_symbol_position == 'right'
                                                    ? $be->base_currency_symbol
                                                    : '';
                                            $description = $package->meta_description
                                                ? strip_tags($package->meta_description)
                                                : '';
                                        @endphp
                                        <div class="col-lg-4">
                                            <div class="pricing-card-wrap mb-30">
                                                <div class="pricing-card">
                                                    <span class="blur-shape">
                                                        <img class="blur-up lazyload"
                                                            src="{{ asset('assets/front/img/placeholder.png') }}"
                                                            data-src="{{ asset('assets/front/images/pricing/blur-shape.png') }}"
                                                            alt="blur-shape">
                                                    </span>
                                                    <div class="pricing-card-top">
                                                        <div class="d-flex mb-20 gap-10 align-items-center">
                                                            <h2 class="mb-0 price">
                                                                {{ $currencyLeft }}{{ $priceLabel }}{{ $currencyRight }}
                                                            </h2>
                                                            <span class="mb-0 period">/ {{ __($package->term) }}</span>
                                                        </div>
                                                        <h4 class="mb-20 pricing-title">{{ __($package->title) }}</h4>
                                                        @if (!empty($description))
                                                            <p class="mb-24">{{ $description }}</p>
                                                        @endif
                                                        <div class="d-grid gap-2 mb-20">
                                                            @if ($package->is_trial == '1' && $package->price != 0)
                                                                <a href="{{ route('front.register.view', ['status' => 'trial', 'id' => $package->id]) }}"
                                                                    class="btn pricing-btn">{{ __('Trial') }}</a>
                                                            @endif
                                                            @if ($package->price == 0)
                                                                <a href="{{ route('front.register.view', ['status' => 'regular', 'id' => $package->id]) }}"
                                                                    class="btn pricing-btn">{{ __('Signup') }}</a>
                                                            @else
                                                                <a href="{{ route('front.register.view', ['status' => 'regular', 'id' => $package->id]) }}"
                                                                    class="btn pricing-btn">{{ __('Purchase') }}</a>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="list-item-area">
                                                        <h4 class="mb-18 fw-medium body-font list-title">
                                                            {{ __('Whats Included') }}</h4>
                                                        <ul class="item-list list-unstyled p-0 mb-0 toggle-list"
                                                            data-toggle-list="amenitiesToggle" data-toggle-show="5">
                                                            <li>
                                                                <i class="fal fa-check"></i>
                                                                ({{ human_number($package->total_ai_token ?? 0) }})
                                                                {{ __('AI Credit') }}
                                                            </li>
                                                            <li>
                                                                <i class="fal fa-check"></i>
                                                                ({{ $package->whatsapp_limit ?? 1 }})
                                                                {{ __('Whatsapp Business Number') }}
                                                            </li>
                                                            <li>
                                                                <i class="fal fa-check"></i>
                                                                @if ($package->language_limit >= 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    ({{ $package->language_limit ?? 1 }})
                                                                @endif
                                                                {{ __('Additional Language') }}
                                                            </li>
                                                            <li>
                                                                <i class="fal fa-check"></i>
                                                                @if ($package->room_limit >= 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    ({{ $package->room_limit ?? 1 }})
                                                                @endif
                                                                {{ __('Rooms') }}
                                                            </li>
                                                            <li>
                                                                <i class="fal fa-check"></i>
                                                                @if ($package->room_categories_limit >= 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    ({{ $package->room_categories_limit ?? 1 }})
                                                                @endif
                                                                {{ __('Categories') }}
                                                            </li>
                                                            <li>
                                                                <i class="fal fa-check"></i>
                                                                @if ($package->room_booking_limit >= 999999)
                                                                    {{ __('Unlimited') }}
                                                                @else
                                                                    ({{ $package->room_booking_limit ?? 1 }})
                                                                @endif
                                                                {{ __('Bookings') }}
                                                            </li>
                                                            @foreach ($allPfeatures as $feature)
                                                                @if (in_array($feature, ['QR Builder']))
                                                                    @php
                                                                        $hasFeature =
                                                                            is_array($pFeatures) &&
                                                                            in_array($feature, $pFeatures);
                                                                    @endphp
                                                                    <li class="{{ $hasFeature ? '' : 'disabled' }}">
                                                                        <i
                                                                            class="{{ $hasFeature ? 'fal fa-check' : 'fa-solid fa-xmark' }}"></i>
                                                                        {{ __($feature) }}
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                            @foreach ($allPfeatures as $feature)
                                                                @if (in_array($feature, ['Support Ticket', 'Advertisement']))
                                                                    @php
                                                                        $hasFeature =
                                                                            is_array($pFeatures) &&
                                                                            in_array($feature, $pFeatures);
                                                                    @endphp
                                                                    <li class="{{ $hasFeature ? '' : 'disabled' }}">
                                                                        <i
                                                                            class="{{ $hasFeature ? 'fal fa-check' : 'fa-solid fa-xmark' }}"></i>
                                                                        {{ __($feature) }}
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                        <span class="show-more font-sm" data-toggle-btn="toggleListBtn">
                                                            {{ __('Show More') }} +
                                                        </span>
                                                    </div>
                                                </div>
                                                @if ($package->recommended == '1')
                                                    <div class="suggest_package">
                                                        <span
                                                            class="fw-semibold">{{ __('Recommended Package') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-light text-center py-5 d-block w-100">
                    <h3>{{ __('No Pricing Plan Found!') }}</h3>
                </div>
            @endif
        </div>
    </section>
@endsection
