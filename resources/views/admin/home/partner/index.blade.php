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
        <h4 class="page-title">{{ __('Partners') }}</h4>
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
                <a href="#">{{ __('Partners') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-5 mb-3 mb-lg-0">
                            <div class="card-title d-inline-block">{{ __('Partners') }}</div>
                        </div>
                        <div class="col-lg-2">
                            @if (!empty($langs))
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="fas fa-language" style="color: #1572E8 ; font-size:20px "></i></div>
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
                        <div class="col-lg-5  mt-2 mt-lg-0">
                            <a href="#" class="btn btn-primary btn-sm float-right" data-toggle="modal"
                                data-target="#createModal"><i class="fas fa-plus"></i> {{ __('Add Partner') }}</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            @if (count($partners) == 0)
                                <h3 class="text-center">{{ __('NO PARTNER FOUND') }}</h3>
                            @else
                                <div class="row">
                                    @foreach ($partners as $key => $partner)
                                        <div class="col-md-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <img class="w-100"
                                                        src="{{ asset('assets/front/img/partners/' . $partner->image) }}"
                                                        alt="">
                                                </div>
                                                <div class="card-footer text-center">
                                                    <a class="btn btn-secondary btn-sm mr-2"
                                                        href="{{ route('admin.partner.edit', $partner->id) . '?language=' . request()->input('language') }}">
                                                        <span class="btn-label">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                        {{ __('Edit') }}
                                                    </a>
                                                    <form class="deleteform d-inline-block"
                                                        action="{{ route('admin.partner.delete') }}" method="post">
                                                        @csrf
                                                        <input type="hidden" name="partner_id"
                                                            value="{{ $partner->id }}">
                                                        <button type="submit" class="btn btn-danger btn-sm deletebtn">
                                                            <span class="btn-label">
                                                                <i class="fas fa-trash"></i>
                                                            </span>
                                                            {{ __('Delete') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Partner Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Partner') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="ajaxForm" class="modal-form" action="{{ route('admin.partner.store') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div class="col-12 mb-2  pl-0">
                                        <label for="image"><strong>{{ __('Image') }} <span class="text-danger">**</span></strong></label>
                                    </div>
                                    <div class="col-md-12 showImage mb-3 pl-0">
                                        <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                            class="img-thumbnail">
                                    </div>

                                    <br>
                                    <div role="button" class="btn btn-primary btn-sm upload-btn" id="image">
                                        {{ __('Choose Image') }}
                                        <input type="file" class="img-input" name="image">
                                    </div>

                                    <p id="err_image" class="mb-0 text-danger em"></p>
                                    <p class="text-warning mb-0">{{ __('Upload 300 X 70 image for best quality') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Language') }} <span class="text-danger">**</span></label>
                            <select name="language_id" class="form-control">
                                <option value="" selected disabled>{{ __('Select a language') }}</option>
                                @foreach ($langs as $lang)
                                    <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                @endforeach
                            </select>
                            <p id="err_language_id" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('URL') }} <span class="text-danger">**</span></label>
                            <input type="text" class="form-control ltr" name="url" value=""
                                placeholder="{{ __('Enter URL') }}">
                            <p id="err_url" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Serial Number') }} <span class="text-danger">**</span></label>
                            <input type="number" class="form-control ltr" name="serial_number" value=""
                                placeholder="{{ __('Enter Serial Number') }}">
                            <p id="err_serial_number" class="mb-0 text-danger em"></p>
                            <p class="text-warning">
                                <small>{{ __('The higher the serial number is, the later the partner will be shown.') }}</small>
                            </p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                    <button id="submitBtn" type="button" class="btn btn-primary">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
