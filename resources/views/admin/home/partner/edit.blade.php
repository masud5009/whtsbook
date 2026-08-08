@extends('admin.layout')

@if (!empty($partner->language) && $partner->language->rtl == 1)
  @section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
  @endsection
@endif


@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Edit Partner') }}</h4>
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
                <a href="#">{{ __('Home Page') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Edit Partner') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Edit Partner') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('admin.partner.index') . '?language=' . request()->input('language') }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 mx-auto">
                            <form id="ajaxForm" class="" action="{{ route('admin.partner.update') }}" method="post">
                                @csrf
                                <input type="hidden" name="partner_id" value="{{ $partner->id }}">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-12 mb-2  pl-0">
                                                <label for="image"><strong>{{ __('Image') }}**</strong></label>
                                            </div>
                                            <div class="col-md-12 showImage mb-3 pl-0">
                                                <img src="{{ $partner->image ? asset('assets/front/img/partners/' . $partner->image) : asset('assets/admin/img/noimage.jpg') }}"
                                                    alt="..." class="img-thumbnail">
                                            </div>
                                            <br>
                                            <div role="button" class="btn btn-primary btn-sm upload-btn" id="image">
                                                {{ __('Choose Image') }}
                                                <input type="file" class="img-input" name="image">
                                            </div>

                                            <p id="err_image" class="mb-0 text-danger em"></p>
                                            <p class="text-warning mb-0">{{ __('Upload 300 X 70 image for best quality') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('URL') }} <span class="text-danger">**</span></label>
                                    <input type="text" class="form-control ltr" name="url"
                                        value="{{ $partner->url }}" placeholder="{{ __('Enter URL') }}">
                                    <p id="err_url" class="text-danger mb-0 em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Serial Number') }} <span
                                            class="text-danger">**</span></label>
                                    <input type="number" class="form-control ltr" name="serial_number"
                                        value="{{ $partner->serial_number }}"
                                        placeholder="{{ __('Enter Serial Number') }}">
                                    <p id="err_serial_number" class="mb-0 text-danger em"></p>
                                    <p class="text-warning">
                                        <small>{{ __('The higher the serial number is, the later the partner will be shown.') }}</small>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" id="submitBtn" class="btn btn-success">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
