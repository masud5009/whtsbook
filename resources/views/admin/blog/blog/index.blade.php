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
        <h4 class="page-title">{{ __('Posts') }}</h4>
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
                <a href="#">{{ __('Posts') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4 mb-3 mb-lg-0">
                            <div class="card-title d-inline-block">{{ __('Posts') }}</div>
                        </div>
                        <div class="col-lg-3">
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
                        <div class="col-lg-4 offset-lg-1 mt-2 mt-lg-0">
                            <a href="#" class="btn btn-primary float-right btn-sm" data-toggle="modal"
                                data-target="#createModal"><i class="fas fa-plus"></i> {{ __('Add Post') }}</a>
                            <button class="btn btn-danger float-right btn-sm mr-2 d-none bulk-delete"
                                data-href="{{ route('admin.blog.bulk.delete') }}"><i class="flaticon-interface-5"></i>
                                {{ __('Delete') }}</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($blogs) == 0)
                                <h3 class="text-center">{{ __('NO POST FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">{{ __('Image') }}</th>
                                                <th scope="col">{{ __('Category') }}</th>
                                                <th scope="col">{{ __('Title') }}</th>
                                                <th scope="col">{{ __('Publish Date') }}</th>
                                                <th scope="col">{{ __('Serial Number') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($blogs as $key => $blog)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $blog->id }}">
                                                    </td>
                                                    <td><img src="{{ asset('assets/front/img/blogs/' . $blog->main_image) }}"
                                                            alt="" width="80"></td>
                                                    <td>
                                                        {{ $blog->categoryName }}
                                                    </td>
                                                    <td width="20%">
                                                        {{ $blog->title }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $date = \Carbon\Carbon::parse($blog->created_at);
                                                        @endphp
                                                        {{ $date->translatedFormat('jS F, Y') }}
                                                    </td>
                                                    <td>{{ $blog->serial_number }}</td>
                                                    <td>
                                                        <a class="btn btn-secondary btn-sm  mb-1"
                                                            href="{{ route('admin.blog.edit', $blog->id) . '?language=' . request()->input('language') }}">
                                                            <span class="btn-label">
                                                                <i class="fas fa-edit"></i>
                                                            </span>
                                                        </a>
                                                        <form class="deleteform d-inline-block"
                                                            action="{{ route('admin.blog.delete') }}" method="post">
                                                            @csrf
                                                            <input type="hidden" name="blog_id"
                                                                value="{{ $blog->id }}">
                                                            <button type="submit"
                                                                class="btn btn-danger btn-sm deletebtn mb-1">
                                                                <span class="btn-label">
                                                                    <i class="fas fa-trash"></i>
                                                                </span>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Create Blog Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Post') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="ajaxForm" enctype="multipart/form-data" class="modal-form"
                        action="{{ route('admin.blog.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div class="col-12 mb-2">
                                        <label for="image"><strong>{{ __('Image') }}</strong></label>
                                    </div>
                                    <div class="col-md-12 showImage mb-3">
                                        <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="..."
                                            class="img-thumbnail">
                                    </div>
                                    <input type="file" name="image" id="image" class="form-control">
                                    <p id="err_image" class="mb-0 text-danger em"></p>
                                    <p class="text-warning mb-0">{{ __('Upload 900 X 570 image for best quality') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Language') }} <span class="text-danger">**</span></label>
                            <select id="language" name="language_id" class="form-control">
                                <option value="" selected disabled>{{ __('Select a language') }}</option>
                                @foreach ($langs as $lang)
                                    <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                                @endforeach
                            </select>
                            <p id="err_language_id" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Title') }} <span class="text-danger">**</span></label>
                            <input type="text" class="form-control" name="title"
                                placeholder="{{ __('Enter Title') }}" value="">
                            <p id="err_title" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Category') }} <span class="text-danger">**</span></label>
                            <select id="bcategory" class="form-control" name="category" disabled>
                                <option value="" selected disabled>{{ __('Select a category') }}</option>
                            </select>
                            <p id="err_category" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Content') }} <span class="text-danger">**</span></label>
                            <textarea class="form-control summernote" id="summernote" name="content" rows="8" cols="80"
                                placeholder="{{ __('Enter Content') }}"></textarea>
                            <p id="err_content" class="mb-0 text-danger em"></p>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Serial Number') }} <span class="text-danger">**</span></label>
                            <input type="number" class="form-control ltr" name="serial_number" value=""
                                placeholder="{{ __('Enter Serial Number') }}">
                            <p id="err_serial_number" class="mb-0 text-danger em"></p>
                            <p class="text-warning mb-0">
                                <small>{{ __('The higher the serial number is, the later the post will be shown.') }}</small>
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="">{{ __('Meta Keywords') }}</label>
                            <input type="text" class="form-control" name="meta_keywords" value=""
                                data-role="tagsinput">
                        </div>
                        <div class="form-group">
                            <label for="">{{ __('Meta Description') }}</label>
                            <textarea type="text" class="form-control" name="meta_description" rows="5"></textarea>
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
