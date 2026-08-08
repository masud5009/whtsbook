@extends('front.layout')

@section('meta-description', !empty($seo) ? $seo->login_meta_description : '')
@section('meta-keywords', !empty($seo) ? $seo->login_meta_keywords : '')

@section('pagename')
    - {{ __('Staff Login') }}
@endsection
@section('breadcrumb-title', __('Staff Login'))
@section('breadcrumb-link', __('Staff Login'))

@section('content')
    <div class="login-area pt-lg-100 pt-90 pb-lg-100 pb-80">
        <div class="container">
            <div class="account-wrap bg-img bg-cover"
                data-bg-image="{{ asset('assets/front/images/inner-pages/login-bg-2.png') }}">
                <div class="row gx-0 align-items-center">
                    <div class="col-lg-6">
                        <div class="account-box">
                            <div class="account-box-header">
                                <h4 class="mb-20">{{ __('Staff Login') }}</h4>
                            </div>
                            <form method="post" action="{{ route('staff.login.submit') }}">
                                @csrf
                                <div class="mb-20">
                                    <label for="login" class="form-label required">{{ __('Username or Email') }}
                                        <span class="text-danger">*</span></label>
                                    <input type="text" name="login" class="form-control radius-sm" id="login"
                                        placeholder="{{ __('Enter Username or Email') }}"
                                        value="{{ old('login') }}">
                                    @if (Session::has('err'))
                                        <p class="text-danger mb-2 mt-2">{{ Session::get('err') }}</p>
                                    @endif
                                    @error('login')
                                        <p class="text-danger mb-2 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-20">
                                    <label for="password" class="form-label required">{{ __('Password') }} <span
                                            class="text-danger">*</span></label>
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control radius-sm"
                                            id="password" placeholder="{{ __('Password') }}">
                                        <span class="show-password-field">
                                            <i class="show-icon"></i>
                                        </span>
                                    </div>
                                    @error('password')
                                        <p class="text-danger mb-2 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>

                                @if ($bs->is_recaptcha == 1)
                                    <div class="mb-20">
                                        {!! NoCaptcha::renderJs() !!}
                                        {!! NoCaptcha::display() !!}
                                        @if ($errors->has('g-recaptcha-response'))
                                            @php
                                                $errmsg = $errors->first('g-recaptcha-response');
                                            @endphp
                                            <p class="text-danger mb-0 mt-2">{{ __($errmsg) }}</p>
                                        @endif
                                    </div>
                                @endif

                                <div class="mb-30 text-center">
                                    <button type="submit"
                                        class="btn btn-primary w-100 radius-30">{{ __('Log In') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 login-banner-box-wrap">
                        <div class="login-banner-box">
                            <h2 class="text-center mb-lg-40 mb-30">{{ __('Welcome To') }}
                                {{ __($bs->website_title) ?? config('app.name') }}</h2>

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
                    <img class="blur-up lazyload"
                        src="{{ asset('assets/front/images/inner-pages/signin-banner-img-5.png') }}"
                        alt="signin-banner-5">
                </div>
            </div>
        </div>
    </div>
@endsection
