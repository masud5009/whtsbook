@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Permissions Management') }}</h4>
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
                <a href="#">{{ __('Admins Management') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Role & Permissions') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ $role->name }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Permissions Management') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Permissions Management') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block" href="{{ route('admin.role.index') }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 offset-lg-2">
                            <form id="permissionsForm" class="" action="{{ route('admin.role.permissions.update') }}"
                                method="post">
                                {{ csrf_field() }}
                                <input type="hidden" name="role_id" value="{{ Request::route('id') }}">
                                @php
                                    $permissions = $role->permissions;
                                    if (!empty($role->permissions)) {
                                        $permissions = json_decode($permissions, true);
                                    }
                                @endphp

                                <div class="form-group">
                                    <label for="" class="mx-1">{{ __('Permissions') }} : </label>
                                    <div class="selectgroup selectgroup-pills mt-2">
                                        <label class="selectgroup-item">
                                            <input type="hidden" name="permissions[]" value="Dashboard"
                                                class="selectgroup-input">
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="Packages"
                                                class="selectgroup-input" @if (is_array($permissions) && in_array('Packages', $permissions)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Package Management') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="AI Credits"
                                                class="selectgroup-input" @if (is_array($permissions) && in_array('AI Credits', $permissions)) checked @endif>
                                            <span class="selectgroup-button">{{ __('AI Credits') }}</span>
                                        </label>

                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="Payment Log"
                                                class="selectgroup-input" @if (is_array($permissions) && in_array('Payment Log', $permissions)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Payment Log') }}</span>
                                        </label>

                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="Users Management"
                                                class="selectgroup-input" @if (is_array($permissions) && in_array('Users Management', $permissions)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Users Management') }}</span>
                                        </label>


                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="Pages"
                                                class="selectgroup-input" @if (is_array($permissions) && in_array('Pages', $permissions)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Pages') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="Support Tickets"
                                                class="selectgroup-input" @if (is_array($permissions) && in_array('Support Tickets', $permissions)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Support Tickets') }}</span>
                                        </label>

                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="Announcement Popup"
                                                class="selectgroup-input" @if (is_array($permissions) && in_array('Announcement Popup', $permissions)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Announcement Popup') }}</span>
                                        </label>

                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="Settings"
                                                class="selectgroup-input" @if (is_array($permissions) && in_array('Settings', $permissions)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Settings') }}</span>
                                        </label>

                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="permissions[]" value="Admins Management"
                                                class="selectgroup-input" @if (is_array($permissions) && in_array('Admins Management', $permissions)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Admins Management') }}</span>
                                        </label>

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
                                <button type="submit" id="permissionBtn"
                                    class="btn btn-success">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
