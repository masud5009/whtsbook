@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Preferences') }}</h4>
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
                <a href="#">{{ __('Rooms Management') }}</a>
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
                <a href="#">{{ __('Preferences') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title">{{ __('Preferences') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-5 pb-5">
                    <div class="row">
                        <div class="col-lg-10 offset-lg-1">
                            <form id="ajaxForm" action="{{ route('tenant.rooms_management.update_settings') }}"
                                method="post">
                                @csrf
                                <div class="row">

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Checkin Time') }} <span class="text-danger">**</span></label>
                                            <input type="time" name="checkin_time" class="form-control"
                                                value="{{ old('checkin_time', $data->checkin_time) }}">
                                            <p id="err_checkin_time" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Checkout Time') }} <span class="text-danger">**</span></label>
                                            <input type="time" name="checkout_time" class="form-control"
                                                value="{{ old('checkout_time', $data->checkout_time) }}">
                                            <p id="err_checkout_time" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Room Booking Cancellation') }} <span
                                                    class="text-danger">**</span></label>
                                            <div class="selectgroup w-100">
                                                <label class="selectgroup-item">
                                                    <input type="radio" name="room_booking_cancellation" value="active"
                                                        class="selectgroup-input"
                                                        {{ $data->room_booking_cancellation == 'active' ? 'checked' : '' }}>
                                                    <span class="selectgroup-button">{{ __('Active') }}</span>
                                                </label>

                                                <label class="selectgroup-item">
                                                    <input type="radio" name="room_booking_cancellation" value="deactive"
                                                        class="selectgroup-input"
                                                        {{ $data->room_booking_cancellation == 'deactive' ? 'checked' : '' }}>
                                                    <span class="selectgroup-button">{{ __('Deactive') }}</span>
                                                </label>
                                            </div>
                                            <p id="err_room_booking_cancellation" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="cancellation_time_limit_hours">
                                                {{ __('Cancellation Time Limit (in hours)') }}
                                            </label>
                                            <input type="number" name="cancellation_time_limit_hours"
                                                id="cancellation_time_limit_hours" class="form-control"
                                                value="{{ $data->cancellation_time_limit_hours }}">
                                            <p id="err_cancellation_time_limit_hours" class="mb-0 text-danger em"></p>
                                            <p class="text-warning">{{ __('Enter how many hours before check-in a guest can cancel the booking.') }}</p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="cancellation_refund_percentage">
                                                {{ __('Refund Percentage (%)') }}
                                            </label>
                                            <input type="number" name="cancellation_refund_percentage"
                                                id="cancellation_refund_percentage" class="form-control"
                                                value="{{ $data->cancellation_refund_percentage }}" min="0"
                                                max="100">
                                            <p id="err_cancellation_refund_percentage" class="mb-0 text-danger em"></p>
                                        </div>
                                    </div>

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
