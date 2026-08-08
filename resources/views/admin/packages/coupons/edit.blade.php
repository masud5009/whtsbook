@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Edit') }}</h4>
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
                <a href="#">{{ __('Coupons') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Edit') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Edit Coupon') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block" href="{{ route('admin.coupon.index') }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-6 mx-auto">

                            <form id="ajaxForm" class="modal-form" action="{{ route('admin.coupon.update') }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="coupon_id" value="{{ $coupon->id }}">
                                <div class="row no-gutters">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Name') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="name"
                                                value="{{ $coupon->name }}" placeholder="{{ __('Enter name') }}">
                                            <p id="err_name" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Code') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="code"
                                                value="{{ $coupon->code }}" placeholder="{{ __('Enter code') }}">
                                            <p id="err_code" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row no-gutters">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Type') }} <span
                                                    class="text-danger">**</span></label>
                                            <select name="type" id="" class="form-control">
                                                <option value="percentage" @selected($coupon->type == 'percentage')>
                                                    {{ __('Percentage') }}
                                                </option>
                                                <option value="fixed" @selected($coupon->type == 'fixed')>
                                                    {{ __('Fixed') }}
                                                </option>
                                            </select>
                                            <p id="err_type" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Value') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control" name="value"
                                                value="{{ $coupon->value }}" placeholder="{{ __('Enter value') }}"
                                                autocomplete="off">
                                            <p id="err_value" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row no-gutters">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Start Date') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control datepicker" name="start_date"
                                                value="{{ $coupon->start_date }}"
                                                placeholder="{{ __('Enter Start Date') }}" autocomplete="off">
                                            <p id="err_start_date" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('End Date') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control datepicker" name="end_date"
                                                value="{{ $coupon->end_date }}" placeholder="{{ __('Enter End Date') }}"
                                                autocomplete="off">
                                            <p id="err_end_date" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row no-gutters">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Packages') }}</label>
                                            <select class="select2" name="packages[]" multiple="multiple"
                                                placeholder="Select Packages">
                                                @foreach ($packages as $package)
                                                    <option value="{{ $package->id }}"
                                                        {{ is_array($selectedPackages) && in_array($package->id, $selectedPackages) ? 'selected' : '' }}>
                                                        {{ $package->title }} {{ ucfirst($package->term) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="mb-0 text-warning">
                                                {{ __('This coupon can be applied to these packages') }}</p>
                                            <p class="mb-0 text-warning">
                                                {{ __('Leave this field blank for all packages') }}</p>
                                            <p id="err_packages" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{ __('Maximum uses limit') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="number" value="{{ $coupon->maximum_uses_limit }}"
                                                class="form-control " name="maximum_uses_limit" value=""
                                                placeholder="{{ __('Enter Maximum uses limit') }}" autocomplete="off">
                                            <p id="err_maximum_uses_limit" class="mb-0 text-danger em"></p>
                                            <p class="mb-0 text-warning">{{ __('Enter 999999 to make it unlimited') }}</p>
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
