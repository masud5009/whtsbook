@extends('user.layout')

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
                        <div class="col-lg-8 offset-lg-2">
                            <form id="ajaxForm" action="{{ route('user-profile-update') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Name') }} <span class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="name"
                                                value="{{ $staff->name }}" required>
                                            <p id="err_name" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Username') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="username"
                                                value="{{ $staff->username }}" required>
                                            <p id="err_username" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="">{{ __('Email') }} <span class="text-danger">**</span></label>
                                            <input type="email" class="form-control" name="email"
                                                value="{{ $staff->email }}" required>
                                            <p id="err_email" class="mb-0 text-danger em"></p>
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
