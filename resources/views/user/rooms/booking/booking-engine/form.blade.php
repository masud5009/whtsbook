@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('New Booking') }}</h4>
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
                <a href="#">{{ __('Room Bookings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('New Booking') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Make New Booking') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('tenant.room_bookings.all_bookings') }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>

                <div class="card-body">
                    <form id="roomBookingForm" action="{{ route('tenant.room_bookings.make_booking') }}" method="POST"
                        autocomplete="off">
                        @csrf
                        <input type="hidden" name="rooms_json" id="rooms_json" value="">
                        <input type="hidden" name="language_id" value="{{ optional($language)->id }}">

                        <div class="row">
                            <div class="col-lg-6">

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <input type="hidden" name="room_category_id"
                                    value="{{ request()->input('room_category_id') }}">

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Check In / Out Date') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('Select Dates') }}" id="date-range" name="dates"
                                                value="{{ $datesC }}" readonly onchange="sendRoomData()">
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_dates"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Total Room') }} <span class="text-danger">**</span></label>
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('Enter Total Room') }}" name="total_rooms"
                                                value="1" />
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_total_rooms"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Number of Nights') }} <span class="text-danger">**</span></label>
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('Number of Nights') }}" id="night" name="nights"
                                                value="{{ old('nights', $interval ?? '') }}" readonly autocomplete="off">
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_nights"></p>
                                            <p class="text-warning mt-1 mb-0 ml-1">
                                                {{ __('Number of nights will be calculated based on checkin & checkout date.') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Number of Adult') }} <span class="text-danger">**</span></label>
                                            <input type="number" class="form-control"
                                                placeholder="{{ __('Enter Number of Adult') }}" name="adult"
                                                value="{{ old('adult') }}">
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_adult"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Number of Child') }} <span class="text-danger">**</span></label>
                                            <input type="number" class="form-control"
                                                placeholder="{{ __('Enter Number of Child') }}" name="child"
                                                value="{{ old('child') }}">
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_child"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Total Rent') . ' (' . $userBs->base_currency_text . ')' }}</label>
                                            <input type="text" class="form-control" name="total"
                                                value="{{ $totalRent }}" readonly id="total" autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Discount') . ' (' . $userBs->base_currency_text . ')' }}</label>
                                            <input type="text" class="form-control" name="discount" value="0.00"
                                                id="discount" placeholder="Enter Discount Amount"
                                                onchange="sendRoomData()" />
                                            <p class="text-warning mt-1 mb-0 ml-1">
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Customer Full Name') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('Enter Full Name') }}" name="customer_name"
                                                value="{{ old('customer_name') }}" autocomplete="off">
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_customer_name"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Customer Phone Number') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('Enter Phone Number') }}" name="customer_phone"
                                                value="{{ old('customer_phone') }}" autocomplete="off">
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_customer_phone"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Customer Email') }} <span class="text-danger">**</span></label>
                                            <input type="email" class="form-control"
                                                placeholder="{{ __('Enter Customer Email') }}" name="customer_email"
                                                value="{{ old('customer_email') }}" autocomplete="off">
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_customer_email"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Payment Method') }} <span class="text-danger">**</span></label>
                                            <select name="payment_method" class="form-control" autocomplete="off">
                                                <option selected disabled>{{ __('Select a Method') }}</option>

                                                @if (count($onlineGateways) > 0)
                                                    @foreach ($onlineGateways as $onlineGateway)
                                                        <option
                                                            {{ old('payment_method') == $onlineGateway->name ? 'selected' : '' }}
                                                            value="{{ $onlineGateway->name }}">
                                                            {{ $onlineGateway->name }}
                                                        </option>
                                                    @endforeach
                                                @endif

                                                @if (count($offlineGateways) > 0)
                                                    @foreach ($offlineGateways as $offlineGateway)
                                                        <option
                                                            {{ old('payment_method') == $offlineGateway->name ? 'selected' : '' }}
                                                            value="{{ $offlineGateway->name }}">
                                                            {{ $offlineGateway->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_payment_method"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Payment Status') }} <span class="text-danger">**</span> </label>
                                            <select name="payment_status" class="form-control" id="payment_status">
                                                <option selected disabled>{{ __('Select Payment Status') }}</option>
                                                <option @selected(old('payment_status') == 0) value="0">
                                                    {{ __('Unpaid') }}
                                                </option>
                                                <option @selected(old('payment_status') == 1) value="1">
                                                    {{ __('Full Paid') }}
                                                </option>
                                                <option @selected(old('payment_status') == 2) value="2">
                                                    {{ __('Partial Paid') }}
                                                </option>
                                                <option @selected(old('payment_status') == 3) value="3">
                                                    {{ __('Cancelled') }}
                                                </option>
                                            </select>
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_payment_status"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Booking Status') }} <span class="text-danger">**</span></label>
                                            <select name="booking_status" class="form-control">
                                                <option selected disabled>{{ __('Select Booking Status') }}</option>
                                                <option {{ old('booking_status') == '1' ? 'selected' : '' }}
                                                    value="1">
                                                    {{ __('Confirmed') }}
                                                </option>
                                                <option {{ old('booking_status') == '0' ? 'selected' : '' }}
                                                    value="0">
                                                    {{ __('Pending') }}
                                                </option>
                                            </select>
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_booking_status"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6" id="paying_amount" style="display: none;">
                                        <div class="form-group">
                                            <label>{{ __('Paying Amount') }} <span class="text-danger">**</span></label>
                                            <input type="number" class="form-control" step="0.01"
                                                name="paying_amount" value="0.00">
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_paying_amount"></p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-lg-6">
                                <div class="search-container">
                                    @if ($insufficientDate)
                                        <div class="row booking-wrapper">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <h3 class="text-primary"> {{ __('We have only') }}
                                                        {{ $availableCount }}
                                                        {{ __('room avaiable for') }}
                                                        {{ $dateStr }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mt-1 mb-0 ml-1 em text-danger" id="er_rooms"></p>
                                    @else
                                        <div class="row booking-wrapper">
                                            <div class="col-xl-12">
                                                <div class="card">
                                                    <!-- card-header -->
                                                    <div
                                                        class="card-header d-flex gap-2 flex-wrap justify-content-between">
                                                        <div
                                                            class="card-title d-flex justify-content-between booking-info-title mb-0">
                                                            <h3 class="mb-0">{{ __('Room Assignment') }}</h3>
                                                        </div>
                                                        <div>
                                                            <span class="fas fa-circle text-danger"></span>
                                                            <span class="">{{ __('Booked') }}</span>
                                                            <span class="fas fa-circle text-success"></span>
                                                            <span class="">{{ __('Selected') }}</span>
                                                            <span class="fas fa-circle text-primary"></span>
                                                            <span>{{ __('Available') }}</span>
                                                        </div>
                                                    </div>
                                                    <!-- card-Body -->
                                                    @include('user.rooms.booking.booking-engine.room-assignment')

                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <div class="card-title mb-0">
                                                            <h3 class="mb-0">{{ __('Booked Rooms') }}</h3>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="orderList">
                                                            <!-- list-group-flush -->
                                                            <ul class="list-group list-group-flush orderItem">
                                                                <li class="list-group-item">
                                                                    <h5 class="mb-0">{{ __('Room') }}</h5>
                                                                    <h5 class="mb-0">{{ __('Days') }}</h5>
                                                                    <h5 class="mb-0">{{ __('Rent') }}</h5>
                                                                </li>

                                                                @foreach ($roomList as $room)
                                                                    <li class="list-group-item">
                                                                        <span>
                                                                            <strong>{{ $room['room_number'] }}</strong>
                                                                        </span>

                                                                        <span class="totalDays">
                                                                            <strong>{{ (int) $room['days'] }}</strong>
                                                                        </span>

                                                                        <span class="unitRent">
                                                                            @if (is_array($room['rent']))
                                                                                <div>{{ __('Regular') }}:
                                                                                    {{ userPriceFormat($userId, $room['rent']['regular_price'] ?? 0) }}
                                                                                </div>
                                                                                <div>{{ __('Weekend') }}:
                                                                                    {{ userPriceFormat($userId, $room['rent']['weekend_price'] ?? 0) }}
                                                                                </div>
                                                                                <div>{{ __('Seasonal') }}:
                                                                                    {{ userPriceFormat($userId, $room['rent']['seasonal_price'] ?? 0) }}
                                                                                </div>
                                                                            @endif
                                                                        </span>
                                                                    </li>
                                                                @endforeach
                                                            </ul>


                                                            @php

                                                                $taxRate =
                                                                    $userBs->room_tax_status == 1
                                                                        ? $userBs->room_tax
                                                                        : 0;
                                                                $roomFee =
                                                                    $userBs->room_fee_status == 1
                                                                        ? $userBs->room_fee
                                                                        : 0;
                                                                $taxAmount =
                                                                    (($totalRent - $discount) * $taxRate) / 100;
                                                                $finalTotal =
                                                                    $totalRent - $discount + $taxAmount + $roomFee;
                                                                $currencySymbol = $userBs->base_currency_text;
                                                                $symbolPosition =
                                                                    $userBs->base_currency_symbol_position;
                                                            @endphp

                                                            <!-- Grand Total -->
                                                            <div
                                                                class="d-flex justify-content-between align-items-center p-2 px-3 bg-light">
                                                                <span>{{ __('Total Rent') }}</span>
                                                                <span class="totalRent"
                                                                    data-amount="{{ $totalRent }}">
                                                                    @if ($symbolPosition === 'left')
                                                                        {{ $currencySymbol }}
                                                                        {{ number_format($totalRent, 2) }}
                                                                    @else
                                                                        {{ number_format($totalRent, 2) }}{{ $currencySymbol }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div
                                                                class="d-flex justify-content-between align-items-center border-top p-2 px-3 bg-light">
                                                                <span>{{ __('Discount') }}</span>
                                                                <span class="totalDiscount"
                                                                    data-amount="{{ $discount }}">

                                                                    @if ($symbolPosition === 'left')
                                                                        {{ $currencySymbol }}
                                                                        {{ number_format($discount, 2) }}
                                                                    @else
                                                                        {{ number_format($discount, 2) }}{{ $currencySymbol }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            @if ($userBs->room_tax_status == 1)
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center border-top p-2 px-3 bg-light">
                                                                    <span>{{ __('Tax') }}
                                                                        <small>({{ $taxRate }}%)</small></span>
                                                                    <span>
                                                                        @if ($symbolPosition === 'left')
                                                                            <span class="taxCharge">{{ $currencySymbol }}
                                                                                {{ number_format($taxAmount, 2) }}</span>
                                                                        @else
                                                                            <span
                                                                                class="taxCharge">{{ number_format($taxAmount, 2) }}
                                                                                {{ $currencySymbol }}</span>
                                                                        @endif
                                                                    </span>
                                                                    <input name="tax_charge" type="hidden"
                                                                        value="{{ number_format($taxAmount, 2) }}">
                                                                </div>
                                                            @endif
                                                            @if ($userBs->room_fee_status == 1)
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center border-top p-2 px-3 bg-light">
                                                                    <span>{{ __('Fee') }} <small></small></span>
                                                                    <span>
                                                                        @if ($symbolPosition === 'left')
                                                                            <span class="taxChafrge">{{ $currencySymbol }}
                                                                                {{ number_format($roomFee, 2) }}</span>
                                                                        @else
                                                                            <span
                                                                                class="taxfCharge">{{ number_format($roomFee, 2) }}
                                                                                {{ $currencySymbol }}</span>
                                                                        @endif
                                                                    </span>
                                                                    <input name="fee_charge" type="hidden"
                                                                        value="{{ number_format($roomFee, 2) }}">
                                                                </div>
                                                            @endif

                                                            <div
                                                                class="d-flex justify-content-between align-items-center border-top p-2 px-3 bg-light">
                                                                <span>{{ __('Grand Total') }}</span>
                                                                <span class="grandTotalRent">
                                                                    @if ($symbolPosition === 'left')
                                                                        {{ $currencySymbol }}
                                                                        {{ number_format($finalTotal, 2) }}
                                                                    @else
                                                                        {{ number_format($finalTotal, 2) }}
                                                                        {{ $currencySymbol }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" form="roomBookingForm" class="btn btn-success">
                                {{ __('Update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        'use strict';
        let bookedDates = {!! json_encode($dates) !!};
        var currency = "{{ $userBs->base_currency_text }}";
        var roomUpdateUrl = "{{ route('tenant.rooms_management.bookings.total_rooms') }}";
        var room_fee = "{{ $userBs->room_fee_status == 1 ? $userBs->room_fee : 0 }}";
        var room_tax = "{{ $userBs->room_tax_status == 1 ? $userBs->room_tax : 0 }}";
    </script>
    <script type="text/javascript" src="{{ asset('assets/tenant/js/booking.js') }}"></script>
@endsection
