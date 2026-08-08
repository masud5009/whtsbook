@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Package Features') }}</h4>
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
                <a href="#">{{ __('Package Management') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Package Features') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Package Features') }}</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 m-auto">
                            <form id="permissionsForm" class="" action="{{ route('admin.package.features.update') }}"
                                method="post">
                                {{ csrf_field() }}
                                <div class="alert alert-warning text-dark">
                                    {{ __('Only these selected features will be visible in frontend Pricing Section') }}
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('Package Features') }}</label>
                                    <br/>
                                    <div class="selectgroup selectgroup-pills">
                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="features[]" value="QR Builder"
                                                class="selectgroup-input" @if (is_array($features) && in_array('QR Builder', $features)) checked @endif>
                                            <span class="selectgroup-button">{{ __('QR Builder') }}</span>
                                        </label>
                                        <label class="selectgroup-item">
                                            <input type="checkbox" name="features[]" value="Support Ticket"
                                                class="selectgroup-input" @if (is_array($features) && in_array('Support Ticket', $features)) checked @endif>
                                            <span class="selectgroup-button">{{ __('Support Ticket') }}</span>
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
