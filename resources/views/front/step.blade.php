@extends('front.layout')

@section('pagename')
    - {{$package->title}}
@endsection

@section('meta-description', !empty($package) ? $package->meta_keywords : '')
@section('meta-keywords', !empty($package) ? $package->meta_description : '')

@section('breadcrumb-title')
    {{$package->title}}
@endsection
@section('breadcrumb-link')
    {{$package->title}}
@endsection

@section('content')
    <div class="login-area pt-lg-100 pt-90 pb-lg-100 pb-80">
        <div class="container">
            <div class="account-wrap bg-img bg-cover"
                data-bg-image="{{ asset('assets/front/images/inner-pages/login-bg-2.png') }}">
                <div class="row gx-0 align-items-center">
                    <div class="col-lg-6">
                        <div class="account-box">
                            <div class="account-box-header">
                                <h4 class="mb-20">{{ __('Signup Now') }}</h4>
                            </div>
                            <form id="authForm" action="{{ route('front.checkout.view') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="mb-20">
                                    <label for="signup_username" class="form-label required">{{ __('Username') }} <span
                                            class="text-danger">*</span></label>
                                    <input id="signup_username" type="text" class="form-control radius-sm"
                                        name="username" placeholder="{{ __('Username') }}" value="{{ old('username') }}"
                                        required>
                                    @if ($hasSubdomain)
                                        <p class="mb-0 mt-2">
                                            {{ __('Your subdomain based website URL will be') }}:
                                            <strong class="text-primary"><span
                                                    id="username">{username}</span>.{{ env('WEBSITE_HOST') }}</strong>
                                        </p>
                                    @endif
                                    <p class="text-danger mb-0" id="usernameAvailable"></p>
                                    @error('username')
                                        <p class="text-danger mb-2 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mb-20">
                                    <label for="signup_email" class="form-label required">{{ __('Email Address') }} <span
                                            class="text-danger">*</span></label>
                                    <input id="signup_email" class="form-control radius-sm" type="email" name="email"
                                        value="{{ old('email') }}" placeholder="{{ __('Email Address') }}" required>
                                    @error('email')
                                        <p class="text-danger mb-2 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mb-20">
                                    <label for="signup_password" class="form-label required">{{ __('Password') }} <span
                                            class="text-danger">*</span></label>
                                    <input id="signup_password" class="form-control radius-sm" type="password"
                                        name="password" value="{{ old('password') }}" placeholder="{{ __('Password') }}"
                                        required>
                                    @error('password')
                                        <p class="text-danger mb-2 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mb-30">
                                    <label for="password-confirm" class="form-label required">{{ __('Confirm Password') }}
                                        <span class="text-danger">*</span></label>
                                    <input class="form-control radius-sm" id="password-confirm" type="password"
                                        placeholder="{{ __('Confirm Password') }}" name="password_confirmation" required
                                        autocomplete="new-password">
                                    @error('password')
                                        <p class="text-danger mb-2 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    <input type="hidden" name="id" value="{{ $id }}">
                                </div>
                                <div class="mb-20 text-center">
                                    <button type="submit" class="btn btn-primary w-100 radius-30">
                                        {{ __('Continue') }}
                                    </button>
                                </div>
                                <p class="text-center mb-0">
                                    {{ __('Already have an account?') }}
                                    <strong><a class="text-primary" href="{{ route('user.login') }}">{{ __('Login Here') }}</a></strong>
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



@section('scripts')
    @if ($hasSubdomain)
        <script>
            "use strict";
            $(document).ready(function() {
                $("input[name='username']").on('input', function() {
                    let username = $(this).val();
                    if (username.length > 0) {
                        $("#username").text(username);
                    } else {
                        $("#username").text("{username}");
                    }
                });
            });
        </script>
    @endif
    <script>
		"use strict";
        $(document).ready(function() {
            $("input[name='username']").on('change', function() {
                let username = $(this).val();
                if (username.length > 0) {
                    $.get("{{url('/')}}/check/" + username + '/username', function(data) {
                        if (data == true) {
                            $("#usernameAvailable").text('This username is already taken.');
                        } else {
                            $("#usernameAvailable").text('');
                        }
                    });
                } else {
                    $("#usernameAvailable").text('');
                }
            });
        });
    </script>
@endsection
