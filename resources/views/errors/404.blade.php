@extends('front.layout')

@section('pagename')
    - {{ __('404') }}
@endsection

@section('meta-description', !empty(optional($be)->page_not_found_subtitle) ? optional($be)->page_not_found_subtitle :
    '')
@section('meta-keywords', '')

@section('breadcrumb-title', !empty(optional($be)->page_not_found_title) ? optional($be)->page_not_found_title :
    __('404'))
@section('breadcrumb-link', !empty(optional($be)->page_not_found_title) ? optional($be)->page_not_found_title :
    __('404'))

@section('content')
    @php
        $notFoundTitle = !empty(optional($be)->page_not_found_title)
            ? optional($be)->page_not_found_title
            : __('404 not found');
        $notFoundSubtitle = !empty(optional($be)->page_not_found_subtitle)
            ? optional($be)->page_not_found_subtitle
            : __('The page you are looking for might have been moved, renamed, or might never existed.');
        $notFoundImage = !empty(optional($be)->page_not_found_image)
            ? asset('assets/front/img/' . optional($be)->page_not_found_image)
            : asset('assets/admin/img/noimage.jpg');
    @endphp

    <section class="error-area pt-lg-100 pt-90 pb-lg-100 pb-60 bg-img bg-cover"
        data-bg-image="{{ asset('assets/front/images') }}/inner-pages/inner-bg.png">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="not-found text-center mb-40">
                        <img class="blur-up lazyload" src="{{ $notFoundImage }}" alt="404">
                    </div>
                    <div class="error-txt text-center">
                        <h2>{{ $notFoundTitle }}</h2>
                        <p class="mx-auto mb-40">
                            {{ $notFoundSubtitle }}
                        </p>
                        <a href="{{ route('front.index') }}" class="btn anim-btn radius-30"> {{ __('Back to Home') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
