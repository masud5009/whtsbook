@extends('admin.layout')

@if (!empty($abe->language) && $abe->language->rtl == 1)
    @section('styles')
        <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
    @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Cookie Alert') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Settings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Cookie Alert') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form id="ajaxForm" action="{{ route('admin.cookie.update', $lang_id) }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-10 mb-3 mb-lg-0">
                                <div class="card-title">{{ __('Update Cookie Alert') }}</div>
                            </div>
                            <div class="col-lg-2">
                                @if (!empty($langs))
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><i class="fas fa-language"
                                                    style="color: #1572E8 ; font-size:20px "></i></div>
                                        </div>
                                        <select name="language" class="form-control"
                                            onchange="window.location='{{ url()->current() . '?language=' }}'+this.value">
                                            <option value="" selected disabled>{{ __('Select a Language') }}</option>
                                            @foreach ($langs as $lang)
                                                <option value="{{ $lang->code }}"
                                                    {{ $lang->code == request()->input('language') ? 'selected' : '' }}>
                                                    {{ $lang->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-6 mx-auto">
                                @csrf
                                <div class="form-group">
                                    <label>{{ __('Cookie Alert Status') }} <span class="text-danger">**</span></label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="cookie_alert_status" value="1"
                                                class="selectgroup-input"
                                                {{ $abe->cookie_alert_status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="cookie_alert_status" value="0"
                                                class="selectgroup-input"
                                                {{ $abe->cookie_alert_status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    <p id="err_cookie_alert_status" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Cookie Alert Button Text') }} <span class="text-danger">**</span></label>
                                    <input class="form-control" name="cookie_alert_button_text"
                                        value="{{ $abe->cookie_alert_button_text }}">
                                    <p id="err_cookie_alert_button_text" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Cookie Alert Text') }} <span
                                            class="text-danger">**</span></label>
                                    <textarea class="form-control summernote" id="summernote" name="cookie_alert_text" rows="3"
                                        placeholder="{{ __('Enter Cookie Alert Text') }}" data-height="100">{{ replaceBaseUrl($abe->cookie_alert_text) }}</textarea>
                                    <p id="err_cookie_alert_text" class="mb-0 text-danger em"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" id="submitBtn"
                                        class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
