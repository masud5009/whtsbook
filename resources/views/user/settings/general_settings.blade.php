@extends('user.layout')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/cropper.css') }}">
@endsection

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('General Settings') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('user-dashboard') }}">
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
                <a href="#">{{ __('General Settings') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form id="ajaxForm" action="{{ route('user.site_settings.update_general_settings') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-10">
                                <div class="card-title">{{ __('Update General Settings') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body py-5">
                        <div class="row">
                            <div class="col-lg-10 mx-auto">
                                <h3 class="text-warning">{{ __('Information') }}</h3>
                                <hr class="divider">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">{{ __('Website Title') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="website_title"
                                                value="{{ $data->website_title }}">
                                            <p id="err_website_title" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="col-12 mb-2 pl-0">
                                                <label for="image"><strong>{{ __('Favicon') }} <span
                                                            class="text-danger">**</span></strong></label>
                                            </div>
                                            <div class="col-md-12 showImage2 mb-3 pl-0 pr-0">
                                                <img src="{{ $data->favicon ? \App\Http\Helpers\Uploader::getImageUrl(Constant::WEBSITE_FAVICON, $data->favicon, $userBs) : asset('/assets/tenant/img/default.jpg') }}"
                                                    alt="..." class="img-thumbnail">
                                            </div>
                                            <div role="button" class="btn btn-primary btn-sm upload-btn" id="image2">
                                                {{ __('Choose Image') }}
                                                <input type="file" class="img-input" name="favicon">
                                            </div>
                                            <p id="err_favicon" class="mb-0 text-danger em"></p>
                                            <p class="text-warning">{{ __('Only JPG, JPEG, PNG images are allowed') }}</p>
                                            <p class="text-warning">
                                                {{ __('Recommended image size:Upload 40X40 pixel size image or squre size') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="col-12 mb-2 pl-0">
                                                <label for="image"><strong>{{ __('Logo') }} <span
                                                            class="text-danger">**</span></strong></label>
                                            </div>
                                            <div class="col-md-12 showImage mb-3 pl-0 pr-0">
                                                <img src="{{ $data->logo ? asset(Constant::WEBSITE_LOGO . '/' . $data->logo) : asset('/assets/tenant/img/default.jpg') }}"
                                                    alt="..." class="img-thumbnail cropped-thumbnail-image">
                                            </div>

                                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                                data-target="#thumbnail-image-modal">{{ __('Choose Image') }}</button>
                                            <p class="text-warning mb-0">
                                                {{ __('Only JPG, JPEG, PNG images are allowed') }}
                                            </p>
                                            <p id="err_thumbnail_image" class="mb-0 text-danger em"></p>
                                            <p class="text-warning mb-0">
                                                {{ __('Only image size will be 322X115 pixel.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="col-12 mb-2 pl-0">
                                                <label for="image"><strong>{{ __('Preloader') }} <span
                                                            class="text-danger">**</span></strong></label>
                                            </div>
                                            <div class="col-md-12 showImage3 mb-3 pl-0 pr-0">
                                                <img src="{{ isset($data->preloader) ? asset(Constant::WEBSITE_PRELOADER . '/' . $data->preloader) : asset('assets/admin/img/noimage.jpg') }}"
                                                    alt="..." class="img-thumbnail">
                                            </div>
                                            <div role="button" class="btn btn-primary btn-sm upload-btn" id="image3">
                                                {{ __('Choose Image') }}
                                                <input type="file" class="img-input" name="preloader">
                                            </div>

                                            <p class="text-warning">
                                                {{ __('Only JPG, JPEG, PNG, GIF images are allowed') }}
                                            </p>
                                            <p id="err_preloader" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>{{ __('Preloader Status') }} <span class="text-danger">**</span></label>
                                            <div class="selectgroup w-100">
                                                <label class="selectgroup-item">
                                                    <input type="radio" name="preloader_status" value="1"
                                                        class="selectgroup-input"
                                                        {{ $data->preloader_status == 1 ? 'checked' : '' }}>
                                                    <span class="selectgroup-button">{{ __('Active') }}</span>
                                                </label>
                                                <label class="selectgroup-item">
                                                    <input type="radio" name="preloader_status" value="0"
                                                        class="selectgroup-input"
                                                        {{ $data->preloader_status == 0 ? 'checked' : '' }}>
                                                    <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                                </label>
                                            </div>
                                            <p id="err_preloader_status" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-10 mx-auto">
                                <h3 class="text-warning">{{ __('Set Timezone') }}</h3>
                                <hr class="divider">
                                <div class="form-group">
                                    <label>{{ __('Timezone') }}
                                        <span class="text-danger">**</span></label>
                                    <select name="timezone" class="form-control select2">
                                        @foreach ($timezones as $timezone)
                                            <option value="{{ $timezone->timezone }}" @selected($timezone->timezone == $data->timezone)>
                                                {{ $timezone->timezone }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p id="err_timezone" class="em text-danger mb-0"></p>
                                </div>
                            </div>

                            <div class="col-lg-10 mx-auto">
                                <h3 class="text-warning">{{ __('Currency Settings') }}</h3>
                                <hr class="divider">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Base Currency Symbol') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control ltr" name="base_currency_symbol"
                                                value="{{ $data->base_currency_symbol }}">
                                            <p id="err_base_currency_symbol" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Base Currency Symbol Position') }} <span
                                                    class="text-danger">**</span></label>
                                            <select name="base_currency_symbol_position" class="form-control ltr">
                                                <option selected disabled>{{ __('Select') }}</option>
                                                <option value="left"
                                                    {{ $data->base_currency_symbol_position == 'left' ? 'selected' : '' }}>
                                                    {{ __('Left') }}</option>
                                                <option value="right"
                                                    {{ $data->base_currency_symbol_position == 'right' ? 'selected' : '' }}>
                                                    {{ __('Right') }}</option>
                                            </select>
                                            <p id="err_base_currency_symbol" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Base Currency Text') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control ltr" name="base_currency_text"
                                                value="{{ $data->base_currency_text }}">
                                            <p id="err_base_currency_text" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Base Currency Text Position') }} <span
                                                    class="text-danger">**</span></label>
                                            <select name="base_currency_text_position" class="form-control ltr">
                                                <option selected disabled>{{ __('Select') }}</option>
                                                <option value="left"
                                                    {{ $data->base_currency_text_position == 'left' ? 'selected' : '' }}>
                                                    {{ __('Left') }}</option>
                                                <option value="right"
                                                    {{ $data->base_currency_text_position == 'right' ? 'selected' : '' }}>
                                                    {{ __('Right') }}</option>
                                            </select>
                                            <p id="err_base_currency_text_position" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Base Currency Rate') }} <span
                                                    class="text-danger">**</span></label>
                                            <div class="input-group mb-2">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">{{ __('1 USD =') }}</span>
                                                </div>
                                                <input type="text" name="base_currency_rate" class="form-control ltr"
                                                    value="{{ $data->base_currency_rate }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">{{ $data->base_currency_text }}</span>
                                                </div>
                                            </div>
                                            <p id="err_base_currency_rate" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-10 mx-auto">
                                <h3 class="text-warning">{{ __('Website Appearance') }}</h3>
                                <hr class="divider">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Primary Color') }} <span class="text-danger">**</span></label>
                                            <input class="jscolor form-control ltr" name="primary_color"
                                                value="{{ $data->primary_color }}">
                                            <p id="err_primary_color" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Secondary Color') }} <span class="text-danger">**</span></label>
                                            <input class="jscolor form-control ltr" name="secondary_color"
                                                value="{{ $data->secondary_color }}">
                                            <p id="err_secondary_color" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12 text-center">
                                <button type="submit" id="submitBtn" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    @includeIf('user.settings.logo_model')
@endsection
@section('script')
    <script>
        "use strict"
        var aspectRatio = 2.8;
        var minCropBoxWidth = 322;
        var minCropBoxHeight = 115;
    </script>
    <script src="{{ asset('assets/admin/js/cropper-plugin.js') }}"></script>
    <script src="{{ asset('assets/tenant/js/logo-cropper.js') }}"></script>
@endsection
