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
        <h4 class="page-title">{{ __('Contact Page') }}</h4>
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
                <a href="#">{{ __('Contact Page') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form id="ajaxForm" enctype="multipart/form-data" action="{{ route('admin.contact.update', $lang_id) }}"
                    method="POST">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-10 mb-3 mb-lg-0">
                                <div class="card-title">{{ __('Contact Page') }}</div>
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
                                    <label>{{ __('Form Title') }} <span class="text-danger">**</span></label>
                                    <input class="form-control" name="contact_form_title"
                                        value="{{ $abs->contact_form_title }}" placeholder="{{ __('Enter form Title') }}">
                                    <p class="mb-0 text-danger em" id="err_contact_form_title"></p>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Information Title') }} <span class="text-danger">**</span></label>
                                    <input class="form-control" name="contact_info_title"
                                        value="{{ $abs->contact_info_title }}"
                                        placeholder="{{ __('Enter Information Title') }}">
                                    <p class="mb-0 text-danger em" id="err_contact_info_title"></p>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Address') }} <span class="text-danger">**</span></label>
                                    <textarea class="form-control" name="contact_addresses" rows="4" placeholder="{{ __('Enter Address') }}">{{ $abe->contact_addresses }}</textarea>
                                    <div class="text-warning">{{ __('Use newline to seperate multiple addresses.') }}</div>
                                    <p class="mb-0 text-danger em" id="err_contact_addresses"></p>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Contact Information Text') }} <span class="text-danger">**</span></label>
                                    <input class="form-control" name="contact_text" value="{{ $abs->contact_text }}"
                                        placeholder="{{ __('Enter Information text') }}">
                                    <p class="mb-0 text-danger em" id="err_contact_text"></p>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Phone') }} <span class="text-danger">**</span></label>
                                    <input class="form-control" data-role="tagsinput" name="contact_numbers"
                                        value="{{ $abe->contact_numbers }}" placeholder="{{ __('Enter Phone Number') }}">
                                    <div class="text-warning">{{ __('Use comma (,) to add multiple Phone Numbers') }}</div>
                                    <p class="mb-0 text-danger em" id="err_contact_numbers"></p>

                                </div>

                                <div class="form-group">
                                    <label>{{ __('Email') }} <span class="text-danger">**</span></label>
                                    <input class="form-control ltr" data-role="tagsinput" name="contact_mails"
                                        value="{{ $abe->contact_mails }}" placeholder="{{ __('Enter Email Addresses') }}">
                                    <div class="text-warning">{{ __('Use comma (,) to add multiple Email Addresses') }}
                                    </div>
                                    <p class="mb-0 text-danger em" id="err_contact_mails"></p>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('Latitude') }}</label>
                                            <input class="form-control ltr" type="number" step="any" name="latitude"
                                                value="{{ $abs->latitude }}" placeholder="{{ __('e.g. 23.8103') }}">
                                            <p class="mb-0 text-danger em" id="err_latitude"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('Longitude') }}</label>
                                            <input class="form-control ltr" type="number" step="any"
                                                name="longitude" value="{{ $abs->longitude }}"
                                                placeholder="{{ __('e.g. 90.4125') }}">
                                            <p class="mb-0 text-danger em" id="err_longitude"></p>

                                        </div>
                                    </div>
                                </div>
                                <p class="text-info"><i
                                        class="fas fa-info-circle mr-1"></i>{{ __('Latitude & Longitude will be used to show your exact location on the map.') }}
                                </p>
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
