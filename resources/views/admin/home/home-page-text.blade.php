@extends('admin.layout')

@if (!empty($abs->language) && $abs->language->rtl == 1)
    @section('styles')
        <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
    @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Section Titles') }}</h4>
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
                <a href="#">{{ __('Section Titles') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10 mb-3 mb-lg-0">
                            <div class="card-title">{{ __('Update Section Titles') }}</div>
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
                        <div class="col-lg-8 mx-auto">

                            <form id="ajaxForm" action="{{ route('admin.home.page.text.update', $lang_id) }}"
                                method="post" enctype="multipart/form-data">
                                @csrf

                                <div class="row">

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Work Process Section Title') }}</label>
                                            <input type="hidden" name="types[]" value="work_process_title">
                                            <input name="work_process_title" class="form-control"
                                                value="{{ $abs->work_process_title }}">
                                            <p id="err_work_process_title" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Work Process Section Subtitle') }}</label>
                                            <input type="hidden" name="types[]" value="work_process_subtitle">
                                            <input name="work_process_subtitle" class="form-control"
                                                value="{{ $abs->work_process_subtitle }}">
                                            <p id="err_work_process_subtitle" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Preview Templates Section Title') }}</label>
                                            <input type="hidden" name="types[]" value="preview_templates_title">
                                            <input name="preview_templates_title" class="form-control"
                                                value="{{ $abs->preview_templates_title }}">
                                            <p id="err_preview_templates_title" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Preview Templates Section Subtitle') }}</label>
                                            <input type="hidden" name="types[]" value="preview_templates_subtitle">
                                            <input name="preview_templates_subtitle" class="form-control"
                                                value="{{ $abs->preview_templates_subtitle }}">
                                            <p id="err_preview_templates_subtitle" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Featured Users Section Title') }}</label>
                                            <input type="hidden" name="types[]" value="featured_users_title">
                                            <input name="featured_users_title" class="form-control"
                                                value="{{ $abs->featured_users_title }}">
                                            <p id="err_featured_users_title" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Featured Users Section Subtitle') }}</label>
                                            <input type="hidden" name="types[]" value="featured_users_subtitle">
                                            <input name="featured_users_subtitle" class="form-control"
                                                value="{{ $abs->featured_users_subtitle }}">
                                            <p id="err_featured_users_subtitle" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Partner Section Title') }} </label>
                                            <input type="hidden" name="types[]" value="partner_title">
                                            <input name="partner_title" class="form-control"
                                                value="{{ $abs->partner_title }}">
                                            <p id="err_partner_title" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Partner Section Subtitle') }} </label>
                                            <input type="hidden" name="types[]" value="partner_subtitle">
                                            <input name="partner_subtitle" class="form-control"
                                                value="{{ $abs->partner_subtitle }}">
                                            <p id="err_partner_subtitle" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Pricing Section Title') }} </label>
                                            <input type="hidden" name="types[]" value="pricing_title">
                                            <input name="pricing_title" class="form-control"
                                                value="{{ $abs->pricing_title }}">
                                            <p id="err_pricing_title" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Testimonial Section Title') }} </label>
                                            <input type="hidden" name="types[]" value="testimonial_title">
                                            <input name="testimonial_title" class="form-control"
                                                value="{{ $abs->testimonial_title }}">
                                            <p id="err_testimonial_title" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Blog Section Title') }} </label>
                                            <input type="hidden" name="types[]" value="blog_title">
                                            <input name="blog_title" class="form-control"
                                                value="{{ $abs->blog_title }}">
                                            <p id="err_blog_title" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Blog Section Subtitle') }} </label>
                                            <input type="hidden" name="types[]" value="blog_subtitle">
                                            <input name="blog_subtitle" class="form-control"
                                                value="{{ $abs->blog_subtitle }}">
                                            <p id="err_blog_subtitle" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
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
