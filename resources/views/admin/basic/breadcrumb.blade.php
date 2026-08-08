@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Image') }}</h4>
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
                <a href="#">{{ __('Breadcrumbs') }}</a>
            </li>

            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Image') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ __('Update Image') }}</div>
                </div>
                <div class="card-body pt-5 pb-4">
                    <div class="row">
                        <div class="col-lg-6 mx-auto">
                            <form enctype="multipart/form-data" action="{{ route('admin.breadcrumb.update') }}"
                                method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="col-12 mb-2">
                                                <label for="image"><strong>{{ __('Breadcrumb') }} <span
                                                            class="text-danger">**</span></strong></label>
                                            </div>
                                            <div class="col-md-12 showImage mb-3">
                                                <img src="{{ $bs->breadcrumb ? asset('assets/front/img/' . $bs->breadcrumb) : asset('assets/admin/img/noimage.jpg') }}"
                                                    alt="..." class="img-thumbnail">
                                            </div>

                                            <br>
                                            <div class="col-12 mb-2">
                                            <div role="button" class="btn btn-primary btn-sm upload-btn" id="image">
                                            <i class="fas fa-images"></i>
                                                {{ __('Choose Image') }}
                                                <input type="file" class="img-input" name="file">
                                            </div>
                                            </div>

                                            <p class="text-warning mb-0">
                                                {{ __('Upload 1920 X 600 image for best quality') }}</p>

                                            @error('file')
                                                <p id="err_file" class="mb-0 text-danger em"> {{  $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <div class="form">
                                        <div class="form-group from-show-notify row">
                                            <div class="col-12 text-center">
                                                <button type="submit" class="btn btn-success">{{ __('Update') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
