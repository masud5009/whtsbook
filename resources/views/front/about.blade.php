@extends('front.layout')

@section('meta-keywords', !empty($seo) ? $seo->about_meta_keywords : '')
@section('meta-description', !empty($seo) ? $seo->about_meta_description : '')
@php
    $additional_section_status = json_decode($bs->about_additional_section_status, true);
@endphp
@section('pagename')
    - {{ __('About') }}
@endsection

@section('breadcrumb-title', __('About'))
@section('breadcrumb-link', __('About'))

@section('content')

    <!-- Start about-banner area -->
    @if ($bs->about_features_section_status == 1)
    <div class="about-banner-area pt-lg-100 pt-70 pb-lg-80 pb-60">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="about-banner-content mb-40" data-aos="fade-up" data-aos-delay="100">
                        @if (!empty($be->about_features_section_subtitle))
                            <h6 class="subtitle mb-20">{{ $be->about_features_section_subtitle }}</h6>
                        @endif
                        @if (!empty($be->about_features_section_title))
                            <h2 class="mb-20 title">{{ $be->about_features_section_title }}</h2>
                        @endif
                        @if (!empty($be->about_features_section_text))
                            <p class="desc">{!! nl2br($be->about_features_section_text) !!}</p>
                        @endif
                    </div>
                </div>
            </div>

            @if (!empty($aboutGalleryImages) && count($aboutGalleryImages) > 0)
                <div class="row about-gallery-row">
                    @foreach ($aboutGalleryImages as $galleryImage)
                        <div class="col-xl-4 col-lg-4 col-md-6 gallery-col">
                            <div class="dashbord-image mb-20" data-aos="zoom-out"
                                data-aos-delay="{{ ($loop->index + 1) * 100 }}">
                                <img class="blur-up lazyload" src="{{ asset('assets/front/img/placeholder.png') }}"
                                    data-src="{{ asset('assets/front/img/about-gallery/' . $galleryImage->image) }}"
                                    alt="about-gallery">
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 d-block w-100">
                    <h3>{{ __('No About Gallery Found!') }}</h3>
                </div>
            @endif

        </div>
    </div>
    @endif
    <!-- End about-banner Area -->

    <!-- ======= START brand section ========= -->
    @if ($bs->about_partner_section_status == 1)
        <section class="brand-area">
            <div class="container">
                <div class="brand-slider-area" data-aos="fade-up" data-aos-delay="200">
                    <h4 class="title">{{ !empty($bs->partner_title) ? $bs->partner_title : __('Our Partners') }}</h4>

                    @if (count($partners) > 0)
                        <div class="swiper brand-slider">
                            <div class="swiper-wrapper">
                                @foreach ($partners as $partner)
                                    <div class="swiper-slide">
                                        <div class="item">
                                            @if (!empty($partner->url))
                                                <a href="{{ $partner->url }}" target="_blank" rel="noopener noreferrer">
                                                    <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}"
                                                        alt="company-logo">
                                                </a>
                                            @else
                                                <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}"
                                                    alt="company-logo">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-light text-center py-5 d-block w-100">
                            <h3>{{ __('No Partner Found!') }}</h3>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif
    <!-- ======= End brand section ========= -->

    <!-- Start Blog area -->
    @if($bs->about_blog_section_status == 1)
    <div class="blog-area pt-70 pb-lg-100 pb-70">
        <div class="container">
            <div class="row justify-content-center">
                @forelse ($blogs as $blog)
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ ($loop->index + 1) * 100 }}">
                        <article class="blog-card mb-30">
                            <figure class="blog-image">
                                <a href="{{ route('front.blogdetails', ['id' => $blog->id, 'slug' => $blog->slug]) }}"
                                    class="lazy-container ratio">
                                    <img src="{{ asset('assets/front/img/placeholder.png') }}"
                                        data-src="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}"
                                        alt="{{ $blog->title }}">
                                </a>
                                <span
                                    class="date">{{ \Carbon\Carbon::parse($blog->created_at)->format('d F, Y') }}</span>
                            </figure>

                            <div class="blog-content">
                                <ul class="list-inline reset-ul">
                                    <li class="list-inline-item small">
                                        <a href="javascript:void(0)" class="fw-semibold">
                                            <i class="fa-regular fa-circle-user"></i>
                                            {{ __('By Admin') }}
                                        </a>
                                    </li>
                                    @if (!empty($blog->bcategory))
                                        <li class="list-inline-item small">
                                            <a href="{{ route('front.blogs', ['category' => $blog->bcategory->slug]) }}">
                                                <i class="fa-regular fa-folder"></i>
                                                {{ $blog->bcategory->name }}
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                                <h4 class="title lc-2 mb-10">
                                    <a href="{{ route('front.blogdetails', ['id' => $blog->id, 'slug' => $blog->slug]) }}">
                                        {{ \Illuminate\Support\Str::limit($blog->title, 70) }}
                                    </a>
                                </h4>
                                <p class="card-text lc-2 mb-30">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($blog->content), 150) }}
                                </p>
                                <a href="{{ route('front.blogdetails', ['id' => $blog->id, 'slug' => $blog->slug]) }}"
                                    class="btn anim-btn radius-30">{{ __('Read More') }}
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5 d-block w-100">
                            <h3>{{ __('No Blog Found!') }}</h3>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

@endsection
