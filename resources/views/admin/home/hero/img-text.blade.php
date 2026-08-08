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
                <a href="#">{{ __('Home Page') }}</a>
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
                    <form id="ajaxForm" action="{{ route('admin.herosection.update', $lang_id) }}" method="post">
                        @csrf

                        {{-- Partner Section --}}
                        <div class="row">
                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <h3 class="text-warning">{{ __('Partner Section') }}</h3>
                                    <hr class="divider m-0"><br>
                                </div>
                            </div>

                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <label for="">{{ __('Title') }}</label>
                                    <input type="text" class="form-control" name="partner_title"
                                        value="{{ $abs->partner_title }}">
                                    <p id="err_partner_title" class="em text-danger mb-0"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Workprocess Section --}}
                        <div class="row">
                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <h3 class="text-warning">{{ __('Workprocess Section') }}</h3>
                                    <hr class="divider m-0"><br>
                                </div>
                            </div>

                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <label for="">{{ __('Title') }}</label>
                                    <input type="text" class="form-control" name="work_process_title"
                                        value="{{ $abs->work_process_title }}">
                                    <p id="err_work_process_title" class="em text-danger mb-0"></p>
                                </div>
                            </div>
                        </div>


                        {{-- Features Section --}}
                        <div class="row">
                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <h3 class="text-warning">{{ __('Features Section') }}</h3>
                                    <hr class="divider m-0"><br>
                                </div>
                            </div>

                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <div class="col-12 mb-2  pl-0">
                                        <label for="image">{{ __('Image') }} </label>
                                    </div>
                                    <div class="col-md-12 showImage mb-3 pl-0">
                                        <img src="{{ !empty($abs->features_image) ? asset('assets/front/img/' . $abs->features_image) : asset('assets/admin/img/noimage.jpg') }}"
                                            alt="..." class="img-thumbnail">
                                    </div>

                                    <br>
                                    <div role="button" class="btn btn-primary btn-sm upload-btn" id="image">
                                        {{ __('Choose Image') }}
                                        <input type="file" class="img-input" name="image">
                                    </div>

                                    <p id="err_image" class="mb-0 text-danger em"></p>
                                    <p class="text-warning mb-0">{{ __('Upload 723 X 634 image for best quality') }}</p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Title') }} </label>
                                    <input type="text" class="form-control" name="features_title"
                                        value="{{ $abs->features_title }}">
                                    <p id="err_features_title" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Subtitle') }} </label>
                                    <input type="text" class="form-control" name="features_subtitle"
                                        value="{{ $abs->features_subtitle }}">
                                    <p id="err_features_subtitle" class="em text-danger mb-0"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Platform Modules Section --}}
                        <div class="row">
                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <h3 class="text-warning">{{ __('Platform Modules Section') }}</h3>
                                    <hr class="divider m-0"><br>
                                </div>
                            </div>

                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <label for="">{{ __('Title') }}</label>
                                    <input type="text" class="form-control" name="platform_modules_section_title"
                                        value="{{ $abs->platform_modules_section_title }}">
                                    <p id="err_platform_modules_section_title" class="em text-danger mb-0"></p>
                                </div>

                                <div class="form-group">
                                    <div class="col-12 mb-2 pl-0">
                                        <label for="image3">{{ __('Background Image') }}</label>
                                    </div>
                                    <div class="col-md-12 showImage3 mb-3 pl-0">
                                        <img src="{{ !empty($abs->platform_modules_section_bg_image) ? asset('assets/front/img/' . $abs->platform_modules_section_bg_image) : asset('assets/admin/img/noimage.jpg') }}"
                                            alt="..." class="img-thumbnail">
                                    </div>
                                    <br>
                                    <div role="button" class="btn btn-primary btn-sm upload-btn" id="image3">
                                        {{ __('Choose Image') }}
                                        <input type="file" class="img-input" name="platform_modules_section_bg_image">
                                    </div>
                                    <p id="err_platform_modules_section_bg_image" class="mb-0 text-danger em"></p>
                                    <p class="text-warning mb-0">
                                        {{ __('Upload a wide image for best quality (example: 1320 X 680)') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Pricing Section --}}
                        <div class="row">
                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <h3 class="text-warning">{{ __('Pricing Section') }}</h3>
                                    <hr class="divider m-0"><br>
                                </div>
                            </div>

                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <label for="">{{ __('Title') }}</label>
                                    <input type="text" class="form-control" name="pricing_title"
                                        value="{{ $abs->pricing_title }}">
                                    <p id="err_pricing_title" class="em text-danger mb-0"></p>
                                </div>
                            </div>
                        </div>

                        {{-- FAQ Section --}}
                        <div class="row">
                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <h3 class="text-warning">{{ __('FAQ Section') }}</h3>
                                    <hr class="divider m-0"><br>
                                </div>
                            </div>

                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <label for="">{{ __('Title') }}</label>
                                    <input type="text" class="form-control" name="faq_title"
                                        value="{{ $abs->faq_title }}">
                                    <p id="err_faq_title" class="em text-danger mb-0"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Testimonial Section --}}
                        <div class="row">
                            <div class="col-12 col-lg-8 mx-auto">
                                <div class="form-group">
                                    <h3 class="text-warning">{{ __('Testimonial Section') }}</h3>
                                    <hr class="divider m-0"><br>
                                </div>
                            </div>
                            <div class="col-12 col-lg-8 mx-auto">



                                <div class="row">

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Title') }} </label>

                                            <input name="testimonial_title" class="form-control"
                                                value="{{ $abs->testimonial_title }}">
                                            <p id="err_testimonial_title" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
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
