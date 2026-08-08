@extends('admin.layout')

@if (!empty($abs->language) && $abs->language->rtl == 1)
  @section('styles')

    <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
  @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('SEO Informations') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('user-dashboard') }}">
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
                <a href="#">{{ __('SEO Informations') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form action="{{ route('admin.seo.update') }}" method="post">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-10">
                                <div class="card-title">{{ __('Update SEO Informations') }}</div>
                            </div>
                            <div class="col-lg-2">
                                @if (!empty($langs))
                                    <select name="language" class="form-control float-right"
                                        onchange="window.location='{{ url()->current() . '?language=' }}'+this.value">
                                        <option value="" selected disabled>{{ __('Select a Language') }}</option>
                                        @foreach ($langs as $lang)
                                            <option value="{{ $lang->code }}"
                                                {{ $lang->code == request()->input('language') ? 'selected' : '' }}>
                                                {{ $lang->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Home Page') }}</label>
                                    <input class="form-control" name="home_meta_keywords"
                                        value="{{ $data->home_meta_keywords }}"
                                        placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Home Page') }}</label>
                                    <textarea class="form-control" name="home_meta_description" rows="5"
                                        placeholder="{{ __('Enter Meta Description') }}">{{ $data->home_meta_description }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Blog Page') }}</label>
                                    <input class="form-control" name="blogs_meta_keywords"
                                        value="{{ $data->blogs_meta_keywords }}"
                                        placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Blog Page') }}</label>
                                    <textarea class="form-control" name="blogs_meta_description" rows="5"
                                        placeholder="{{ __('Enter Meta Description') }}">{{ $data->blogs_meta_description }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Profiles Page') }}</label>
                                    <input class="form-control" name="profiles_meta_keywords"
                                        value="{{ $data->profiles_meta_keywords }}"
                                        placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Profiles Page') }}</label>
                                    <textarea class="form-control" name="profiles_meta_description" rows="5"
                                        placeholder="{{ __('Enter Meta Description') }}">{{ $data->profiles_meta_description }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Pricing Page') }}</label>
                                    <input class="form-control" name="pricing_meta_keywords"
                                        value="{{ $data->pricing_meta_keywords }}"
                                        placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Pricing Page') }}</label>
                                    <textarea class="form-control" name="pricing_meta_description" rows="5"
                                        placeholder="{{ __('Enter Meta Description') }}">{{ $data->pricing_meta_description }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For FAQs Page') }}</label>
                                    <input class="form-control" name="faqs_meta_keywords"
                                        value="{{ $data->faqs_meta_keywords }}"
                                        placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For FAQs Page') }}</label>
                                    <textarea class="form-control" name="faqs_meta_description" rows="5"
                                        placeholder="{{ __('Enter Meta Description') }}">{{ $data->faqs_meta_description }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Contact Page') }}</label>
                                    <input class="form-control" name="contact_meta_keywords"
                                        value="{{ $data->contact_meta_keywords }}"
                                        placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Contact Page') }}</label>
                                    <textarea class="form-control" name="contact_meta_description" rows="5"
                                        placeholder="{{ __('Enter Meta Description') }}">{{ $data->contact_meta_description }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Login Page') }}</label>
                                    <input class="form-control" name="login_meta_keywords"
                                        value="{{ $data->login_meta_keywords }}"
                                        placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Login Page') }}</label>
                                    <textarea class="form-control" name="login_meta_description" rows="5"
                                        placeholder="{{ __('Enter Meta Description') }}">{{ $data->login_meta_description }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Forget Password Page') }}</label>
                                    <input class="form-control" name="forget_password_meta_keywords"
                                        value="{{ $data->forget_password_meta_keywords }}"
                                        placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Forget Password Page') }}</label>
                                    <textarea class="form-control" name="forget_password_meta_description" rows="5"
                                        placeholder="{{ __('Enter Meta Description') }}">{{ $data->forget_password_meta_description }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords For Checkout Page') }}</label>
                                    <input class="form-control" name="checkout_meta_keywords"
                                        value="{{ $data->checkout_meta_keywords }}"
                                        placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Meta Description For Checkout Page') }}</label>
                                    <textarea class="form-control" name="checkout_meta_description" rows="5"
                                        placeholder="{{ __('Enter Meta Description') }}">{{ $data->checkout_meta_description }}</textarea>
                                </div>
                            </div>

                            @if (count($pages) > 0)
                                @foreach ($pages as $page)
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Meta Keywords For') . ' ' . $page->title . ' ' . __('Page') }}
                                            </label>
                                            <input class="form-control"
                                                name="custome_page_meta_keyword[{{ $page->id }}]"
                                                value="{{ isset($decodedKeywords[$page->id]) ? $decodedKeywords[$page->id] : '' }}"
                                                placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                                        </div>

                                        <div class="form-group">
                                            <label>{{ __('Meta Description For') . ' ' . $page->title . ' ' . __('Page') }}</label>
                                            <textarea class="form-control" name="custome_page_meta_description[{{ $page->id }}]" rows="5"
                                                placeholder="{{ __('Enter Meta Description') }}">{{ isset($decodedDescriptions[$page->id]) ? $decodedDescriptions[$page->id] : '' }}</textarea>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="card-footer">
                            <div class="form">
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <button type="submit"
                                            class="btn btn-success {{ $data == null ? 'd-none' : '' }}">{{ __('Update') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
@endsection
