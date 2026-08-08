@extends('admin.layout')

@if (!empty($blog->language) && $blog->language->rtl == 1)
  @section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
  @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Edit Post') }}</h4>
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
                <a href="#">{{ __('Blog') }}</a>
            </li>
                       <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.blog.index') . '?language=' . request()->input('language') }}">{{ __('Posts') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Edit Post') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Edit Post') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('admin.blog.index') . '?language=' . request()->input('language') }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 offset-lg-3">
                            <form id="ajaxForm" class="" action="{{ route('admin.blog.update') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="blog_id" value="{{ $blog->id }}">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <div class="col-12 mb-2">
                                                <label for="image"><strong>{{ __('Image') }} </strong></label>
                                            </div>
                                            <div class="col-md-12 showImage mb-3">
                                                <img src="{{ $blog->main_image ? asset('assets/front/img/blogs/' . $blog->main_image) : asset('assets/admin/img/noimage.jpg') }}"
                                                    alt="..." class="img-thumbnail">
                                            </div>
                                            <input type="file" name="image" id="image" class="form-control">
                                            <p id="err_image" class="mb-0 text-danger em"></p>
                                            <p class="text-warning mb-0">
                                                {{ __('Upload 900 X 570 image for best quality') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Title') }} <span class="text-danger">**</span></label>
                                    <input type="text" class="form-control" name="title" value="{{ $blog->title }}"
                                        placeholder="{{ __('Enter Title') }}">
                                    <p id="err_title" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Category') }} <span class="text-danger">**</span></label>
                                    <select class="form-control" name="category">
                                        <option value="" selected disabled>{{ __('Select a category') }}</option>
                                        @foreach ($bcats as $key => $bcat)
                                            <option value="{{ $bcat->indx }}"
                                                {{ $bcat->indx == $blog->category_index ? 'selected' : '' }}>
                                                {{ $bcat->name }}</option>
                                        @endforeach

                                    </select>
                                    <p id="err_category" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Content') }} <span class="text-danger">**</span></label>
                                    <textarea class="form-control summernote" id="summernote" name="content" data-height="300"
                                        placeholder="{{ __('Enter Content') }}">{{ replaceBaseUrl($blog->content) }}</textarea>
                                    <p id="err_content" class="mb-0 text-danger em"></p>
                                </div>

                                <div class="form-group">
                                    <label for="">{{ __('Serial Number') }} <span
                                            class="text-danger">**</span></label>
                                    <input type="number" class="form-control ltr" name="serial_number"
                                        value="{{ $blog->serial_number }}" placeholder="{{ __('Enter Serial Number') }}">
                                    <p id="err_serial_number" class="mb-0 text-danger em"></p>
                                    <p class="text-warning">
                                        <small>{{ __('The higher the serial number is, the later the post will be shown.') }}</small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Meta Keywords') }}</label>
                                    <input type="text" class="form-control" name="meta_keywords"
                                        value="{{ $blog->meta_keywords }}" data-role="tagsinput">
                                    <p id="err_meta_keywords" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <label for="">{{ __('Meta Description') }}</label>
                                    <textarea type="text" class="form-control" name="meta_description" rows="5">{{ $blog->meta_description }}</textarea>
                                    <p id="err_meta_description" class="mb-0 text-danger em"></p>
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
