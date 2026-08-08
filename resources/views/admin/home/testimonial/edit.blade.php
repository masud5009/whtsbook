@extends('admin.layout')

@if (!empty($testimonial->language) && $testimonial->language->rtl == 1)
  @section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
  @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Edit Testimonial') }}</h4>
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
                <a href="#">{{ __('Edit Testimonial') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Edit Testimonial') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('admin.testimonial.index') . '?language=' . request()->input('language') }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 mx-auto">
                            <form id="ajaxForm" class="" action="{{ route('admin.testimonial.update') }}"
                                method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-12 mb-2 pl-0">
                                                <label for="image"><strong> {{ __('Image') }} <span class="text-danger">**</span> </strong></label>
                                            </div>
                                            <div class="col-md-12 showImage mb-3 pl-0">
                                                <img src="{{ $testimonial->image ? asset('assets/front/img/testimonials/' . $testimonial->image) : asset('assets/admin/img/noimage.jpg') }}"
                                                    alt="..." class="img-thumbnail">
                                            </div>
                                            <br>
                                            <div role="button" class="btn btn-primary btn-sm upload-btn" id="image">
                                                {{ __('Choose Image') }}
                                                <input type="file" class="img-input" name="image">
                                            </div>
                                            <p id="err_image" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="testimonial_id" value="{{ $testimonial->id }}">
                                <div class="form-group">
                                    <label for="">{{ __('Comment') }} <span class="text-danger">**</span></label>
                                    <textarea class="form-control" name="comment" rows="3" cols="80" placeholder="{{ __('Enter comment') }}">{{ $testimonial->comment }}</textarea>
                                    <p id="err_comment" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Name') }} <span class="text-danger">**</span></label>
                                    <input type="text" class="form-control" name="name"
                                        value="{{ $testimonial->name }}" placeholder="{{ __('Enter Name') }}">
                                    <p id="err_name" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Rank') }} <span class="text-danger">**</span></label>
                                    <input type="text" class="form-control" name="rank"
                                        value="{{ $testimonial->rank }}" placeholder="{{ __('Enter Rank') }}">
                                    <p id="err_rank" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Serial Number') }} <span
                                            class="text-danger">**</span></label>
                                    <input type="number" class="form-control ltr" name="serial_number"
                                        value="{{ $testimonial->serial_number }}"
                                        placeholder="{{ __('Enter Serial Number') }}">
                                    <p id="err_serial_number" class="mb-0 text-danger em"></p>
                                    <p class="text-warning">
                                        <small>{{ __('The higher the serial number is, the later the testimonial will be shown') }}</small>
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
