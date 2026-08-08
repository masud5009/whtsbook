@extends('admin.layout')
@if (!empty($abs->language) && $abs->language->rtl == 1)
    <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Images & Texts') }}</h4>
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
                <a href="#">{{ __('About Us') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Images & Texts') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10 mb-3 mb-lg-0">
                            <div class="card-title">{{ __('Update Images & Texts') }}</div>
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

                <div class="card-body pt-5 pb-4">
                    <form id="ajaxForm" action="{{ route('admin.aboutpage.update', $lang_id) }}" method="post">
                        @csrf
                        <div class="row">

                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <label for="">{{ __('Title') }}</label>
                                    <input name="about_features_section_title" class="form-control"
                                        value="{{ $abe->about_features_section_title }}"
                                        placeholder="{{ __('Enter Title') }}">
                                    <p id="err_about_features_section_title" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Subtitle') }}</label>
                                    <input name="about_features_section_subtitle" class="form-control"
                                        value="{{ $abe->about_features_section_subtitle }}"
                                        placeholder="{{ __('Enter subtitle') }}">
                                    <p id="err_about_features_section_subtitle" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Text') }}</label>
                                    <textarea name="about_features_section_text" cols="30" rows="5"
                                        placeholder="{{ __('Enter Text') }}" class="form-control">{{ $abe->about_features_section_text }}</textarea>
                                    <p id="err_about_features_section_text" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="aboutGalleryDropzone">{{ __('Gallery') }}</label>
                                    <div id="aboutGalleryDropzone" class="dropzone mt-2 mb-3"
                                        data-upload-url="{{ route('admin.aboutpage.gallery.upload', $lang_id) }}"
                                        data-delete-route="{{ route('admin.aboutpage.gallery.delete', '__id__') }}"
                                        data-delete-text="{{ __('Delete') }}"
                                        data-error-text="{{ __('Something went wrong!') }}"
                                        data-success-title="{{ __('Success') }}">
                                        <div class="dz-message">
                                            <i class="fas fa-cloud-upload-alt"></i><br>
                                            {{ __('Drag and drop images here to upload') }}
                                        </div>
                                        <div class="fallback"></div>
                                    </div>
                                    <p class="text-warning mb-0">{{ __('Only JPG, JPEG, PNG image is allowed') }}</p>
                                    <p id="err_about_gallery_images" class="em text-danger mb-0 mt-1"></p>
                                </div>

                                <div class="row mt-3" id="aboutGalleryList">
                                    @foreach ($aboutGalleryImages as $galleryImage)
                                        <div class="col-md-4 mb-3" id="about-gallery-item-{{ $galleryImage->id }}">
                                            <div class="card">
                                                <div class="card-body p-2">
                                                    <img src="{{ asset('assets/front/img/about-gallery/' . $galleryImage->image) }}"
                                                        alt="gallery-image" class="img-thumbnail w-100 mb-2">
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm btn-block delete-about-gallery"
                                                        data-url="{{ route('admin.aboutpage.gallery.delete', $galleryImage->id) }}"
                                                        data-id="{{ $galleryImage->id }}">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
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

@section('scripts')
    <script src="{{ asset('assets/admin/js/about-img-text.js') }}"></script>
@endsection
