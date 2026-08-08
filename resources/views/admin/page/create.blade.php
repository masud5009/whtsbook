@extends('admin.layout')
@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Add Page') }}</h4>
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
                <a href="#">{{ __('Additional Pages') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Add Page') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ __('Create Page') }}</div>
                </div>
                <div class="card-body pt-3 pb-4">
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <form id="ajaxForm" action="{{ route('admin.page.store') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Language') }} <span
                                                    class="text-danger">**</span></label>
                                            <select id="language" name="language_id" class="form-control">
                                                <option value="" selected disabled>{{ __('Select a language') }}
                                                </option>
                                                @foreach ($langs as $lang)
                                                    <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                                @endforeach
                                            </select>
                                            <p id="err_language_id" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Name') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" name="name" class="form-control"
                                                placeholder="{{ __('Enter Name') }}" value="">
                                            <p id="err_name" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Title') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="title"
                                                placeholder="{{ __('Enter Title') }}" value="">
                                            <p id="err_title" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Status') }} <span
                                                    class="text-danger">**</span></label>
                                            <select class="form-control ltr" name="status">
                                                <option value="1">{{ __('Active') }}</option>
                                                <option value="0">{{ __('Deactive') }}</option>
                                            </select>
                                            <p id="err_status" class="em text-danger mb-0"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Body') }} <span class="text-danger">**</span></label>
                                    <textarea id="body" class="form-control summernote" name="body" data-height="500"></textarea>
                                    <p id="err_body" class="em text-danger mb-0"></p>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Meta Keywords') }}</label>
                                    <input class="form-control" name="meta_keywords" value=""
                                        placeholder="{{ __('Enter meta keywords') }}" data-role="tagsinput">
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Meta Description') }}</label>
                                    <textarea class="form-control" name="meta_description" rows="5" placeholder="{{ __('Enter meta description') }}"></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" id="submitBtn" class="btn btn-success">{{ __('Submit') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
