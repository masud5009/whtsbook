@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Maintenance Mode') }}</h4>
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
                <a href="#">{{ __('Settings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Maintenance Mode') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card-title">{{ __('Update Maintenance Mode') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 mx-auto">
                            <form id="ajaxForm" action="{{ route('admin.maintainance.update') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <div class="col-12 mb-2  pl-0">
                                                <label for="image"><strong>{{ __('Maintainance Image') }} <span
                                                            class="text-danger">**</span></strong></label>
                                            </div>
                                            <div class="col-md-12 showImage mb-3 pl-0">
                                                <img src="{{ $bs->maintenance_img ? asset('assets/front/img/' . $bs->maintenance_img) : asset('assets/admin/img/noimage.jpg') }}"
                                                    alt="..." class="img-thumbnail">
                                            </div>
                                            <br>
                                            <div class="col-12 mb-2  pl-0">
                                                <div role="button" class="btn btn-primary btn-sm upload-btn"
                                                    id="image">
                                                    <i class="fas fa-images"></i>
                                                    {{ __('Choose Image') }}
                                                    <input type="file" class="img-input" name="file">
                                                </div>
                                            </div>

                                            <p class="mb-0 text-danger em" id="err_image"></p>

                                            <p class="text-warning mb-0">
                                                {{ __('Upload 770 X 720 image for best quality') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Maintenance Status') }} <span class="text-danger">**</span></label>
                                    <div class="selectgroup w-100">
                                        <label class="selectgroup-item">
                                            <input type="radio" name="maintenance_status" value="1"
                                                class="selectgroup-input"
                                                {{ $data->maintenance_status == 1 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Active') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="radio" name="maintenance_status" value="0"
                                                class="selectgroup-input"
                                                {{ $data->maintenance_status == 0 ? 'checked' : '' }}>
                                            <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                        </label>
                                    </div>
                                    <p class="mb-0 text-danger em" id="err_maintenance_status"></p>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Maintenance Message') }} <span class="text-danger">**</span></label>
                                    <textarea class="form-control" name="maintainance_text" rows="3" cols="80">{{ $data->maintainance_text }}</textarea>
                                    <p class="mb-0 text-danger em" id="err_maintainance_text"></p>
                                </div>

                                <div class="form-group">
                                    <label> {{ __('Secret Path') }}</label>
                                    <input name="secret_path" type="text" class="form-control"
                                        value="{{ $data->secret_path }}">
                                    <p class="text-warning">
                                        {{ __('After activating maintenance mode, You can access the website via') }}
                                        <strong class="text-danger">{{ url('{secret_path}') }}</strong>
                                    </p>
                                    <p class="text-warning">{{ __('Try to avoid using special characters') }}</p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" id="submitBtn" class="btn btn-success">
                                {{ __('Update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
