@extends('front.layout')

@section('pagename')
    - {{ __('FAQ') }}
@endsection

@section('meta-description', !empty($seo) ? $seo->faqs_meta_description : '')
@section('meta-keywords', !empty($seo) ? $seo->faqs_meta_keywords : '')

@section('breadcrumb-title', !empty($heading) ? $heading->faq_title : __('FAQ'))
@section('breadcrumb-link', !empty($heading) ? $heading->faq_title : __('FAQ'))

@section('content')
    <div class="faq-area pt-lg-120 pt-90 pb-lg-100 pb-60 bg-img bg-cover"
        data-bg-image="{{ asset('assets/front/images/inner-pages/inner-bg.png') }}">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="section-title text-center mb-60">
                        <h2 class="title">{{ !empty($heading) ? $heading->faq_title : __('Frequently Asked Questions') }}
                        </h2>
                    </div>
                </div>

                <div class="col-lg-7" data-aos="fade-up" data-aos-delay="100">
                    <div class="accordion accordion-v3 faqAccordion mb-40" id="faqAccordion">
                        @forelse ($faqs as $key => $faq)
                            @php
                                $isFirst = $loop->first;
                                $headingId = 'faqHeading' . $key;
                                $collapseId = 'faqCollapse' . $key;
                            @endphp

                            <div class="accordion-item mb-20 {{ $isFirst ? 'active' : '' }}">
                                <h2 class="accordion-header" id="{{ $headingId }}">
                                    <button class="accordion-button fw-medium {{ $isFirst ? '' : 'collapsed' }}"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                        aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                                        aria-controls="{{ $collapseId }}">
                                        <span><i class="fa-regular fa-circle-question"></i></span>{{ $faq->question }}
                                    </button>
                                </h2>
                                <div id="{{ $collapseId }}"
                                    class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}"
                                    aria-labelledby="{{ $headingId }}" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body pt-0">
                                        <p class="mb-0">{{ $faq->answer }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-light text-center py-5 d-block w-100">
                                <h3>{{ __('NO FAQ FOUND!') }}</h3>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
