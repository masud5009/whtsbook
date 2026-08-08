@extends('front.layout')
@section('pagename', __('Reset Password'))
@section('meta-description', !empty($seo) ? $seo->forget_password_meta_description : '')
@section('meta-keywords', !empty($seo) ? $seo->forget_password_meta_keywords : '')

@section('breadcrumb-title', !empty($heading) ? $heading->forget_password_title : '')
@section('breadcrumb-link', !empty($heading) ? $heading->forget_password_title : '')

@section('content')
    <div class="login-area pt-lg-100 pt-90 pb-lg-100 pb-80">
        <div class="container">
            <div class="account-wrap bg-img bg-cover"
                data-bg-image="{{ asset('assets/front/images/inner-pages/login-bg-2.png') }}">
                <div class="row gx-0 align-items-center">
                    <div class="col-lg-6">
                        <div class="account-box">
                            <div class="account-box-header">
                                <h4 class="mb-10">{{ __('Forget Password') }}</h4>
                                <p>{{ __('Enter your email and we will send you a password reset link.') }}</p>
                                <div class="underline position-relative"><span>{{ __('Recover Account') }}</span></div>
                            </div>

                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form class="login-form" action="{{ route('user.forgot.password.submit') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="mb-20">
                                    <label for="forgot_email" class="form-label required">{{ __('Email Address') }} <span
                                            class="text-danger">*</span></label>
                                    <input id="forgot_email" type="email" name="email" placeholder="{{ __('Email') }}"
                                        class="form-control radius-sm" value="{{ old('email') }}">
                                    @error('email')
                                        <p class="text-danger mb-2 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>

                                @if ($bs->is_recaptcha == 1)
                                    <div class="mb-20">
                                        <div class="d-block mb-4">
                                            {!! NoCaptcha::renderJs() !!}
                                            {!! NoCaptcha::display() !!}
                                            @if ($errors->has('g-recaptcha-response'))
                                                @php
                                                    $errmsg = $errors->first('g-recaptcha-response');
                                                @endphp
                                                <p class="text-danger mb-0 mt-2">{{ __("$errmsg") }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-20 text-center">
                                    <button class="btn btn-primary w-100 radius-30">{{ __('Send Password Reset Link') }}</button>
                                </div>

                                <p class="text-center mb-0">
                                    {{ __('Remember your password?') }}
                                    <strong><a class="text-primary"
                                            href="{{ route('user.login') }}">{{ __('Login Here') }}</a></strong>
                                </p>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-6 login-banner-box-wrap">
                        <div class="login-banner-box">
                            <h2 class="text-center mb-lg-40 mb-30">
                                {{ __('Welcome To') }} {{ __($bs->website_title) ?? config('app.name') }}
                            </h2>

                            <div class="signin-banner-img-box">
                                <div class="signin-banner-img banner-img-1">
                                    <img class="blur-up lazyload"
                                        src="{{ asset('assets/front/images/inner-pages/signin-banner-img-1.png') }}"
                                        alt="signin-banner-1">
                                </div>
                                <div class="signin-banner-img banner-img-2">
                                    <img class="blur-up lazyload"
                                        src="{{ asset('assets/front/images/inner-pages/signin-banner-img-2.png') }}"
                                        alt="signin-banner-2">
                                </div>
                                <div class="signin-banner-img banner-img-3">
                                    <img class="blur-up lazyload"
                                        src="{{ asset('assets/front/images/inner-pages/signin-banner-img-3.png') }}"
                                        alt="signin-banner-3">
                                </div>
                                <div class="signin-banner-img banner-img-4">
                                    <img class="blur-up lazyload"
                                        src="{{ asset('assets/front/images/inner-pages/signin-banner-img-4.png') }}"
                                        alt="signin-banner-4">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="banner-img-5">
                    <img class="blur-up lazyload" src="{{ asset('assets/front/images/inner-pages/signin-banner-img-5.png') }}"
                        alt="signin-banner-5">
                </div>
            </div>
        </div>
    </div>
@endsection
