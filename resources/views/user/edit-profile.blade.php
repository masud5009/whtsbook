@extends('user.layout')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/cropper.css') }}">
@endsection

{{-- this style will be applied when the direction of language is right-to-left --}}
@includeIf('user.partials.rtl-style')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Edit Profile') }}</h4>
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
                <a href="#">{{ __('Settings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Edit Profile') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Update Profile') }}</div>
                </div>
                <div class="card-body pb-5">
                    <div class="row">
                        <div class="col-lg-10 offset-lg-1">
                            <form id="ajaxForm" class="" action="{{ route('user-profile-update') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div class="">
                                                <label for="image"><strong> {{ __('Profile Image') }}
                                                        <span class="text-danger">**</span></strong></label>
                                            </div>
                                            <div class="showImage mb-3">
                                                <img src="{{ \App\Http\Helpers\Uploader::getImageUrl(Constant::WEBSITE_TENANT_IMAGE, $user->photo, $userBs, Constant::WEBSITE_NO_IMAGE) }}"alt="..." class="cropped-thumbnail-image img-thumbnail">
                                            </div>

                                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                                data-target="#thumbnail-image-modal">{{ __('Choose Image') }}</button>

                                            <p id="err_photo" class="mb-0 text-danger em"></p>
                                            <p class="text-warning"> {{ __('Only image size will be 100X100 pixel.') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('First Name') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="first_name"
                                                value="{{ $user->first_name }}">
                                            <p id="err_first_name" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Last Name') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="last_name"
                                                value="{{ $user->last_name }}">
                                            <p id="err_last_name" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Company name') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="company_name"
                                                value="{{ $user->company_name }}">
                                            <p id="err_company_name" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Username') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="username"
                                                value="{{ $user->username }}">
                                            <p id="err_username" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Phone') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="phone"
                                                value="{{ $user->phone }}">
                                            <p id="err_phone" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('City') }}</label>
                                            <input type="text" class="form-control" name="city" rows="5"
                                                value="{{ $user->city }}">
                                            <p id="err_city" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('State') }}</label>
                                            <input type="text" class="form-control" name="state" rows="5"
                                                value="{{ $user->state }}">
                                            <p id="err_state" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Country') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="country" rows="5"
                                                value="{{ $user->country }}">
                                            <p id="err_country" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="">{{ __('Address') }}</label>
                                            <textarea type="text" class="form-control" name="address" rows="5">{{ $user->address }}</textarea>
                                            <p id="err_address" class="mb-0 text-danger em"></p>
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
    @includeIf('user.modal-cropper')
@endsection
@section('script')
    <script src="{{ asset('assets/admin/js/cropper-plugin.js') }}"></script>
    <script src="{{ asset('assets/tenant/js/profile-cropper.js') }}"></script>
@endsection
