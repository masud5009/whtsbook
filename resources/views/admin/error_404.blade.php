@extends('admin.layout')

@php
    $selLang = \App\Models\Language::where('code', request()->input('language'))->first();
@endphp
@if (!empty($selLang) && $selLang->rtl == 1)
    @section('styles')
        <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
    @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('404 Page') }}</h4>
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
                <a href="#">{{ __('Pages') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('404 Page') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form id="ajaxForm" enctype="multipart/form-data"
                    action="{{ route('admin.update_error_404', $language->code) }}" method="POST">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-10 mb-3 mb-lg-0">
                                <div class="card-title">{{ __('404 Page') }}</div>
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
                                    <div class="col-12 mb-2 pl-0">
                                        <label for="image"><strong>{{ __('Page Not Found Image') }} <span
                                                    class="text-danger">**</span></strong></label>
                                    </div>
                                    <div class="col-md-12 showImage mb-3 pl-0">
                                        <img src="{{ $data != null ? asset('assets/front/img/' . $data->page_not_found_image) : asset('assets/admin/img/noimage.jpg') }}"
                                            alt="..." class="img-thumbnail">
                                    </div>

                                    <br>
                                    <div role="button" class="btn btn-primary btn-sm upload-btn" id="image">
                                        {{ __('Choose Image') }}
                                        <input type="file" class="img-input" name="page_not_found_image">
                                    </div>

                                    <p class="text-warning mb-0">
                                        {{ __('Upload 1920 X 600 image for best quality') }}</p>

                                    <p class="mb-0 text-danger em" id="err_page_not_found_image"></p>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Page Not Found Title') }} <span class="text-danger">**</span></label>
                                    <input class="form-control" name="page_not_found_title"
                                        value="{{ @$data->page_not_found_title }}"
                                        placeholder="{{ __('Enter Page Not Found Title') }}">
                                    <p class="mb-0 text-danger em" id="err_page_not_found_title"></p>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Page Not Found Subtitle') }} <span class="text-danger">**</span></label>
                                    <input class="form-control" name="page_not_found_subtitle"
                                        value="{{ @$data->page_not_found_subtitle }}"
                                        placeholder="{{ __('Enter Page Not Found Title') }}">
                                    <p class="mb-0 text-danger em" id="err_page_not_found_subtitle"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer pt-3">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button id="submitBtn" class="btn btn-success">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
