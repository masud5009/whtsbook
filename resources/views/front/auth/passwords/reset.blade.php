@extends('front.layout')

@section('pagename', __('Reset Password'))
@section('breadcrumb-title', !empty($heading) ? $heading->reset_password_title : '')
@section('breadcrumb-link',!empty($heading) ? $heading->reset_password_title : '')


@section('content')
    <!--====== End Breadcrumbs section ======-->
    <section class="login-section pb-120 pt-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="user-form">
                        <form class="login-form" action="{{route('user.reset.password.submit')}}" method="post"
                              enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="pass_token" value="{{ request('pass_token') }}">
                            <div class="form_group">
                                <span>{{__('Password')}}*</span>
                                <input type="password" class="form-control" placeholder="{{__('Password')}}"
                                       name="password" value="{{old('password')}}" >
                                @error('password')
                                <p class="text-danger mb-2 mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="form_group">
                                <button class="btn primary-btn anim-btn">{{ __('Reset Password') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
