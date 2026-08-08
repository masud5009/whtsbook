@extends('admin.layout')

@if (!empty($abs->language) && $abs->language->rtl == 1)
  @section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
  @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Intro Section') }}</h4>
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
                <a href="#">{{ __('Intro Section') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10 mb-3 mb-lg-0">
                            <div class="card-title">{{ __('Update Intro Section') }}</div>
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
                    </div>
                </div>
                <div class="card-body pt-5 pb-4">
                    <div class="row">
                        <div class="col-lg-6 offset-lg-3">
                            <form id="ajaxForm" action="{{ route('admin.introsection.update', $lang_id) }}"
                                method="post">
                                @csrf

                                <div class="form-group">
                                    <label for="">{{ __('Title') }} </label>
                                    <input type="text" class="form-control" name="intro_title"
                                        value="{{ $abs->intro_title }}">
                                    <p id="err_intro_title" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Subtitle') }}</label>
                                    <input type="text" class="form-control" name="intro_subtitle"
                                        value="{{ $abs->intro_subtitle }}">
                                    <p id="err_intro_subtitle" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Text') }} </label>
                                    <textarea name="intro_text" class="form-control" rows="4">{{ $abs->intro_text }}</textarea>
                                    <p id="err_intro_text" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Button Text') }} </label>
                                    <input type="text" class="form-control" name="intro_section_button_text"
                                        value="{{ $abs->intro_section_button_text }}">
                                    <p id="err_intro_section_button_text" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Button URL') }} </label>
                                    <input type="text" class="form-control ltr" name="intro_section_button_url"
                                        value="{{ $abs->intro_section_button_url }}">
                                    <p id="err_intro_section_button_url" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Video URL') }} </label>
                                    <input type="text" class="form-control ltr" name="intro_section_video_url"
                                        value="{{ $abs->intro_section_video_url }}">
                                    <p id="err_intro_section_video_url" class="em text-danger mb-0"></p>
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
