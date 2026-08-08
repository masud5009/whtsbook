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
        <h4 class="page-title">{{ __('Image & Text') }}</h4>
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
                <a href="#">{{ __('Footer') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Image & Text') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10 mb-3 mb-lg-0">
                            <div class="card-title">{{ __('Update Image & Text') }}</div>
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
                        <div class="col-lg-6 mx-auto">
                            <form id="ajaxForm" action="{{ route('admin.footer.update', $lang_id) }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="mb-2 pl-0">
                                                <label for="image"><strong>{{ __('Logo') }}</strong></label>
                                            </div>
                                            <div class="showImage mb-3 pl-0">
                                                <img src="{{ !empty($abs->footer_logo) ? asset('assets/front/img/' . $abs->footer_logo) : asset('assets/admin/img/noimage.jpg') }}"
                                                    alt="..." class="img-thumbnail">
                                            </div>

                                            <br>
                                            <div role="button" class="btn btn-primary btn-sm upload-btn" id="image">
                                                {{ __('Choose Image') }}
                                                <input type="file" class="img-input" name="file">
                                            </div>

                                            <p id="err_file" class="mb-0 text-danger em"></p>
                                            <p class="text-warning mb-0">{{ __('Upload 185 X 50 image for best quality') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Footer Text') }}</label>
                                    <input type="text" class="form-control" name="footer_text"
                                        value="{{ $abs->footer_text }}">
                                    <p id="err_footer_text" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Useful Links Title') }}</label>
                                    <input type="text" class="form-control" name="useful_links_title"
                                        value="{{ $abs->useful_links_title }}">
                                    <p id="err_useful_links_title" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Newsletter Title') }}</label>
                                    <input type="text" class="form-control" name="newsletter_title"
                                        value="{{ $abs->newsletter_title }}">
                                    <p id="err_newsletter_title" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Newsletter Subtitle') }}</label>
                                    <input type="text" class="form-control" name="newsletter_subtitle"
                                        value="{{ $abs->newsletter_subtitle }}">
                                    <p id="err_newsletter_subtitle" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Copyright Text') }}</label>
                                    <textarea id="copyright_text" name="copyright_text" class="form-control summernote" data-height="100">{{ replaceBaseUrl($abs->copyright_text) }}</textarea>
                                    <p id="err_copyright_text" class="em text-danger mb-0"></p>
                                </div>
                            </form>

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
            </div>
        </div>
    </div>

@endsection
