@extends('front.layout')

@section('pagename')
    - {{ __('Payment Cancelled') }}
@endsection

@section('breadcrumb-title')
    {{ __('Payment Cancelled') }}
@endsection

@section('breadcrumb-link')
    {{ __('Payment Cancelled') }}
@endsection

@section('content')

<div class="container ptb-120">
    <div class="row align-items-center gx-xl-5">
        <div class="col-md-6 mx-auto">
            <div class="payment-img mb-30">
                <img src="{{ asset('assets/front/img/cancel.png') }}" alt="Payment Cancelled Illustration">
            </div>
        </div>
        <div class="col-md-6 mx-auto" id="mt">
            <div class="payment mb-30">
                <div class="content">
                    @auth
                        <h2 class="mb-4">{{ __('Payment Cancelled') }}</h2>
                        <p class="paragraph-text mb-4">
                            {{ __('Your payment was cancelled or failed. Please try again.') }}
                        </p>
                        <a href="{{ route('user-dashboard') }}" class="btn primary-btn">
                            {{ __('Go to Dashboard') }}
                        </a>
                    @endauth

                    @guest
                        <h2 class="mb-4">{{ __('Payment Cancelled') }}</h2>
                        <p class="paragraph-text mb-4">
                            {{ __('Your payment could not be completed. You may try again later.') }}
                        </p>
                        <a href="{{ route('front.index') }}" class="btn primary-btn anim-btn">
                            {{ __('Go to Home') }}
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
