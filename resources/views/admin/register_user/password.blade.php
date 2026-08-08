@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Password') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="#">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Users Management') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Registered Users') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Password') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form id="ajaxEditForm" action="{{ route('register.user.updatePassword') }}" method="post">
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    @csrf
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <div class="card-title">{{ __('Update Password') }} ({{ $user->username }})</div>
                            </div>
                            <div class="col-6 text-right">
                                <a href="{{ route('admin.register.user') }}"
                                    class="btn btn-sm btn-primary float-right">
                                    <i class="fas fa-backward"></i>
                                    {{ __('Back') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 mx-auto">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="">{{ __('New Password') }} <span
                                                        class="text-danger">**</span></label>
                                                <input type="password" class="form-control"
                                                    placeholder="{{ __('New Password') }}" name="new_password">
                                                <p class="text-danger mb-0" id="editErr_new_password"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="">{{ __('Confirm Password') }} <span
                                                        class="text-danger">**</span></label>
                                                <input type="password" class="form-control"
                                                    placeholder="{{ __('Confirm Password') }}" name="confirm_password">
                                                <p class="text-danger mb-0" id="editErr_confirm_password"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" id="updateBtn" class="btn btn-success">{{ __('Submit') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
