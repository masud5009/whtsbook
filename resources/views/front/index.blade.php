@extends('front.layout')

@section('pagename')
    - {{ __('Home') }}
@endsection

@section('meta-description', !empty($seo) ? $seo->home_meta_description : '')
@section('meta-keywords', !empty($seo) ? $seo->home_meta_keywords : '')

@php
    $additional_section_status = json_decode($bs->additional_section_status, true);
@endphp
@section('content')
    @if ($bs->home_section == 1)
        <section class="hero-area">
            @if (!empty($heroSliders) && count($heroSliders) > 0)
                <div class="swiper hero-slider-main">
                    <div class="swiper-wrapper">
                        @foreach ($heroSliders as $slider)
                            <div class="swiper-slide">
                                <div class="hero-area-wrap bg-img bg-cover"
                                    data-bg-image="{{ asset('assets/front/img/hero_slider/' . $slider->img) }}">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="hero-content">
                                                    <p class="subtitle" data-aos="fade-up" data-aos-delay="100">
                                                        {{ $slider->title }}
                                                    </p>
                                                    <h1 class="title mb-20" data-aos="fade-up" data-aos-delay="200">
                                                        {!! $slider->subtitle !!}
                                                    </h1>
                                                    <p class="desc mb-lg-40 mb-30" data-aos="fade-up" data-aos-delay="300">
                                                        {{ $slider->description }}
                                                    </p>
                                                    @if (!empty($slider->btn_name))
                                                        <div class="hero-buttons" data-aos="fade-up" data-aos-delay="400">
                                                            <a href="{{ $slider->btn_url ?: '#' }}"
                                                                class="btn anim-btn">{{ $slider->btn_name }}</a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div thumbsSlider="" class="swiper hero-slider-thumb">
                    <div class="swiper-wrapper">
                        @foreach ($heroSliders as $slider)
                            <div class="swiper-slide">
                                <img src="{{ asset('assets/front/img/hero_slider/' . $slider->img) }}" alt="hero-thumb">
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-light text-center py-5 d-block w-100">
                    <h3>{{ __('No Hero Slider Found!') }}</h3>
                </div>
            @endif
        </section>
    @endif
    @if (count($after_hero) > 0)
        @foreach ($after_hero as $cusHero)
            @if (isset($additional_section_status[$cusHero->id]))
                @if ($additional_section_status[$cusHero->id] == 1)
                    @php
                        $cusHeroContent = App\Models\AdditionalSectionContent::where([
                            ['language_id', $language->id],
                            ['addition_section_id', $cusHero->id],
                        ])->first();

                    @endphp
                    {{-- @dd($cusHeroContent) --}}
                    @includeIf('front.additional-section', [
                        'data' => $cusHeroContent,
                        'possition' => $cusHero->possition,
                    ])
                @endif
            @endif
        @endforeach
    @endif


    @if ($bs->partners_section == 1)
        <section class="brand-area pt-80">
            <div class="container">
                <div class="brand-slider-area" data-aos="fade-up" data-aos-delay="200">
                    <h4 class="title">{{ $bs->partner_title }}</h4>
                    <div class="swiper brand-slider">
                        <div class="swiper-wrapper">
                            @foreach ($partners as $partner)
                                <div class="swiper-slide">
                                    <div class="item">
                                        <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}"
                                            alt="company-logo">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    @if (count($after_partner) > 0)
        @foreach ($after_partner as $cusHero)
            @if (isset($additional_section_status[$cusHero->id]))
                @if ($additional_section_status[$cusHero->id] == 1)
                    @php
                        $cusHeroContent = App\Models\AdditionalSectionContent::where([
                            ['language_id', $language->id],
                            ['addition_section_id', $cusHero->id],
                        ])->first();

                    @endphp
                    {{-- @dd($cusHeroContent) --}}
                    @includeIf('front.additional-section', [
                        'data' => $cusHeroContent,
                        'possition' => $cusHero->possition,
                    ])
                @endif
            @endif
        @endforeach
    @endif


    @if ($bs->process_section == 1)
        <section class="step-area pt-lg-100 pt-60">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title" data-aos="fade-up" data-aos-delay="100">
                            <h2 class="mb-lg-0 mb-60">{{ $bs->work_process_title }}</h2>
                        </div>
                    </div>
                </div>
                <div class="step-card-row row justify-content-center">
                    @foreach ($processes as $process)
                        <div class="step-card-col col-lg-4 col-md-6" data-aos="zoom-out" data-aos-delay="100">
                            <div class="step-card">
                                <div class="serial">
                                    {{ $loop->index + 1 }}
                                </div>
                                <div class="step-card-image">
                                    <img class="blur-up lazyload" src="assets/images/placeholder.png"
                                        data-src="{{ asset('assets/front/img/process/' . $process->image) }}"
                                        alt="step">
                                </div>
                                <div class="content">
                                    <h4 class="title mb-14 lc-1">
                                        <a href="javascript:void(0)">{{ $process->title }}</a>
                                    </h4>
                                    <p class="mb-0 lc-2">{{ $process->text }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    @if (count($after_work_process) > 0)
        @foreach ($after_work_process as $cusHero)
            @if (isset($additional_section_status[$cusHero->id]))
                @if ($additional_section_status[$cusHero->id] == 1)
                    @php
                        $cusHeroContent = App\Models\AdditionalSectionContent::where([
                            ['language_id', $language->id],
                            ['addition_section_id', $cusHero->id],
                        ])->first();

                    @endphp
                    @includeIf('front.additional-section', [
                        'data' => $cusHeroContent,
                        'possition' => $cusHero->possition,
                    ])
                @endif
            @endif
        @endforeach
    @endif


    @if (($bs->features_section ?? 1) == 1)
        <section class="about-area pt-lg-120 pt-60">
            <div class="about-area-wrapper">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="about-content mb-30" data-aos="fade-left" data-aos-delay="100">
                                <h2 class="mb-24">
                                    {{ $bs->features_title ?? 'How Our Ai Hotel Booking WhatsApp Automation Made Easy Solution.' }}
                                </h2>
                                <p class="mb-24">
                                    {{ $bs->features_subtitle ?? 'Our platform offers a secure, flexible, and user friendly Ai powered solution for appointment booking through WhatsApp application for your business.' }}
                                </p>

                                <div class="accordion about-accordion" id="accordionExample">
                                    @foreach ($features as $key => $feature)
                                        <div class="accordion-item {{ $key == 0 ? 'active' : '' }}">
                                            <h4 class="accordion-header">
                                                <button class="accordion-button {{ $key == 0 ? '' : 'collapsed' }}"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapse_{{ $key }}"
                                                    aria-expanded="{{ $key == 0 ? 'true' : 'false' }}"
                                                    aria-controls="collapse_{{ $key }}">
                                                    <div class="icon">
                                                        <img src="{{ asset('assets/front/img/feature/' . $feature->icon) }}"
                                                            alt="icon">
                                                    </div>
                                                    {{ $feature->title }}
                                                </button>
                                            </h4>

                                            <div id="collapse_{{ $key }}"
                                                class="accordion-collapse collapse {{ $key == 0 ? 'show' : '' }}"
                                                data-bs-parent="#accordionExample">
                                                <div class="accordion-body">
                                                    {{ $feature->text }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="about-image" data-aos="zoom-out" data-aos-delay="100">
                                @php
                                    $featuresImage = !empty($bs->features_image)
                                        ? asset('assets/front/img/' . $bs->features_image)
                                        : asset('assets/admin/img/noimage.jpg');
                                @endphp
                                <img class="blur-up lazyload" src="{{ asset('assets/front/img/placeholder.png') }}"
                                    data-src="{{ $featuresImage }}" alt="about-image">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    @if (count($after_features) > 0)
        @foreach ($after_features as $cusHero)
            @if (isset($additional_section_status[$cusHero->id]))
                @if ($additional_section_status[$cusHero->id] == 1)
                    @php
                        $cusHeroContent = App\Models\AdditionalSectionContent::where([
                            ['language_id', $language->id],
                            ['addition_section_id', $cusHero->id],
                        ])->first();

                    @endphp
                    @includeIf('front.additional-section', [
                        'data' => $cusHeroContent,
                        'possition' => $cusHero->possition,
                    ])
                @endif
            @endif
        @endforeach
    @endif

    @if ($bs->platform_modules_section == 1)
        <section class="product-area pt-lg-120 pt-60">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h2 class="sec-title" data-aos="fade-up" data-aos-delay="100">
                            {{ $bs->platform_modules_section_title ?? 'Control All Your Appointments from a Single Platform' }}
                        </h2>
                    </div>
                </div>
                @php
                    $platformModulesSectionBg = !empty($bs->platform_modules_section_bg_image)
                        ? asset('assets/front/img/' . $bs->platform_modules_section_bg_image)
                        : asset('assets/images/step/pro-bg.png');
                @endphp
                <div class="product-area-inner bg-cover bg-img" data-bg-image="{{ $platformModulesSectionBg }}">
                    <div class="row">
                        @if (!empty($platformModules) && count($platformModules) > 0)
                            @foreach ($platformModules as $module)
                                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                                    <div class="product-card mb-30">
                                        <div class="content">
                                            <div class="icon">
                                                <img src="{{ !empty($module->icon) ? asset('assets/front/img/platform_modules/' . $module->icon) : asset('assets/admin/img/noimage.jpg') }}"
                                                    alt="icon">
                                            </div>
                                            <h6 class="title mb-2"><a href="#">{{ $module->title }}</a></h6>
                                            <p class="desc mb-24">{{ $module->subtitle }}</p>
                                        </div>
                                        <div class="product-image">
                                            <div class="lazy-container ratio ratio-1-1">
                                                <img src="{{ !empty($module->image) ? asset('assets/front/img/platform_modules/' . $module->image) : asset('assets/admin/img/noimage.jpg') }}"
                                                    alt="image">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12">
                                <div class="text-center py-5 d-block w-100">
                                    <h3>{{ __('No Platform Module Found!') }}</h3>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif
    @if (count($after_platform_module) > 0)
        @foreach ($after_platform_module as $cusHero)
            @if (isset($additional_section_status[$cusHero->id]))
                @if ($additional_section_status[$cusHero->id] == 1)
                    @php
                        $cusHeroContent = App\Models\AdditionalSectionContent::where([
                            ['language_id', $language->id],
                            ['addition_section_id', $cusHero->id],
                        ])->first();

                    @endphp
                    @includeIf('front.additional-section', [
                        'data' => $cusHeroContent,
                        'possition' => $cusHero->possition,
                    ])
                @endif
            @endif
        @endforeach
    @endif

    @if ($bs->pricing_section == 1)
        <section class="pricing-area pt-lg-100 pt-60">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                            <h2 class="mb-40">{{ $bs->pricing_title }}</h2>
                            @if (count($terms) > 1)
                                <!-- tabs-navigation -->
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
                                                    $package->price != 0 &&
                                                    $be->base_currency_symbol_position == 'right'
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
                                                                src="assets/images/placeholder.png"
                                                                data-src="{{ asset('assets/front/images/pricing/blur-shape.png') }}"
                                                                alt="blur-shape">
                                                        </span>
                                                        <div class="pricing-card-top">
                                                            <div class="d-flex mb-20 gap-10 align-items-center">
                                                                <h2 class="mb-0 price">
                                                                    {{ $currencyLeft }}{{ $priceLabel }}{{ $currencyRight }}
                                                                </h2>
                                                                <span class="mb-0 period">/
                                                                    {{ __($package->term) }}</span>
                                                            </div>
                                                            <h4 class="mb-20 pricing-title">{{ __($package->title) }}</h4>
                                                            @if (!empty($description))
                                                                <p class="mb-24">{{ $description }}</p>
                                                            @endif
                                                            <div class="d-flex gap-2 mb-20">
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
                                                            <span class="show-more font-sm"
                                                                data-toggle-btn="toggleListBtn">
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
    @endif
    @if (count($after_pricing) > 0)
        @foreach ($after_pricing as $cusHero)
            @if (isset($additional_section_status[$cusHero->id]))
                @if ($additional_section_status[$cusHero->id] == 1)
                    @php
                        $cusHeroContent = App\Models\AdditionalSectionContent::where([
                            ['language_id', $language->id],
                            ['addition_section_id', $cusHero->id],
                        ])->first();

                    @endphp
                    @includeIf('front.additional-section', [
                        'data' => $cusHeroContent,
                        'possition' => $cusHero->possition,
                    ])
                @endif
            @endif
        @endforeach
    @endif

    @if ($bs->faq_section == 1)
        <section class="faq-section pt-lg-120 pt-60 pb-lg-100 pb-50">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h2 class="text-center title mb-lg-40 mb-30" data-aos="fade-up" data-aos-delay="200">
                            {{ $bs->faq_title ?? __('Frequently Asked Questions') }}</h2>
                    </div>
                </div>
                <div class="row" data-aos="fade-up" data-aos-delay="300">
                    <div class="col-lg-12">
                        <div class="accordion accordion-v3 faqAccordion mb-40 bg-img bg-cover" id="faqAccordion"
                            data-bg-image="{{ asset('assets/front/images') }}/pricing/faq-bg.png">
                            @if (!empty($faqs) && count($faqs) > 0)
                                @foreach ($faqs as $faq)
                                    <div class="accordion-item {{ $loop->first ? 'active' : '' }}">
                                        <h2 class="accordion-header">
                                            <button
                                                class="accordion-button fw-medium {{ $loop->first ? '' : 'collapsed' }}"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#faqCollapse{{ $faq->id }}"
                                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                                aria-controls="faqCollapse{{ $faq->id }}">
                                                <span><i
                                                        class="fa-regular fa-circle-question"></i></span>{{ $faq->question }}
                                            </button>
                                        </h2>
                                        <div id="faqCollapse{{ $faq->id }}"
                                            class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                            data-bs-parent="#faqAccordion">
                                            <div class="accordion-body pt-0">
                                                <p class="mb-0">{{ $faq->answer }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="bg-light text-center py-5 d-block w-100">
                                    <h3>{{ __('NO FAQ FOUND!') }}</h3>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    @if (count($after_faq) > 0)
        @foreach ($after_faq as $cusHero)
            @if (isset($additional_section_status[$cusHero->id]))
                @if ($additional_section_status[$cusHero->id] == 1)
                    @php
                        $cusHeroContent = App\Models\AdditionalSectionContent::where([
                            ['language_id', $language->id],
                            ['addition_section_id', $cusHero->id],
                        ])->first();

                    @endphp
                    @includeIf('front.additional-section', [
                        'data' => $cusHeroContent,
                        'possition' => $cusHero->possition,
                    ])
                @endif
            @endif
        @endforeach
    @endif


    @if ($bs->testimonial_section == 1)
        <section class="section-testimonial pb-lg-80 pb-30">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <h2 class="text-center title mb-lg-40 mb-30" data-aos="fade-up" data-aos-delay="100">
                            {{ $bs->testimonial_title }}
                        </h2>
                    </div>
                </div>
                <div class="testimonial-slider-wrap" data-aos="fade-up" data-aos-delay="300">
                    @if (!empty($testimonials) && count($testimonials) > 0)
                        <div class="swiper default-slider pb-60" id="default-slider-testimonial" data-slidespace="30"
                            data-xsmview="1" data-smview="1" data-mdview="1" data-lgview="2" data-xlview="2">
                            <div class="swiper-wrapper">
                                @foreach ($testimonials as $testimonial)
                                    @php
                                        $comment = strip_tags($testimonial->comment ?? '');
                                        $title =
                                            mb_strlen($comment, 'UTF-8') > 80
                                                ? mb_substr($comment, 0, 80, 'UTF-8') . '...'
                                                : $comment;
                                        $bgIndex = $loop->odd ? '1' : '2';
                                        $imageUrl = !empty($testimonial->image)
                                            ? asset('assets/front/img/testimonials/' . $testimonial->image)
                                            : asset('assets/front/images/placeholder.png');
                                    @endphp
                                    <div class="swiper-slide">
                                        <div class="testimonial-card bg-img bg-cover"
                                            data-bg-image="{{ asset('assets/front/images') }}/testimonial/testimonial-bg-{{ $bgIndex }}.png">
                                            <h4 class="title mb-20">&ldquo;{{ $title }}&rdquo;</h4>
                                            <p class="desc fs-4 mb-30 lc-4">{{ $comment }}</p>
                                            <div class="testimonial-card-footer">
                                                <div class="author-info">
                                                    <div class="author-image">
                                                        <a href="#">
                                                            <img class="blur-up lazyload"
                                                                src="{{ asset('assets/front/images/placeholder.png') }}"
                                                                data-src="{{ $imageUrl }}" alt="author-image">
                                                        </a>
                                                    </div>
                                                    <div class="details">
                                                        <h6 class="fw-semibold author-name small mb-0">
                                                            <a href="#">{{ $testimonial->name }}</a>
                                                        </h6>
                                                        <p class="mb-0 designation small">{{ $testimonial->rank }}</p>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-pagination" id="default-slider-testimonial-pagination"></div>
                        </div>
                    @else
                        <div class="bg-light text-center py-5 d-block w-100">
                            <h3>{{ __('No Testimonial Found!') }}</h3>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif
    @if (count($after_testimonial) > 0)
        @foreach ($after_testimonial as $cusHero)
            @if (isset($additional_section_status[$cusHero->id]))
                @if ($additional_section_status[$cusHero->id] == 1)
                    @php
                        $cusHeroContent = App\Models\AdditionalSectionContent::where([
                            ['language_id', $language->id],
                            ['addition_section_id', $cusHero->id],
                        ])->first();

                    @endphp
                    @includeIf('front.additional-section', [
                        'data' => $cusHeroContent,
                        'possition' => $cusHero->possition,
                    ])
                @endif
            @endif
        @endforeach
    @endif

@endsection
