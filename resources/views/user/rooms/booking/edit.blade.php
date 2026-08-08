@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Booking Details') }}</h4>
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
                <a href="#">{{ __('Booking Details') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-success alert-with-icon">
                <span class="bg-success text-white mr-2 p-1">
                    <i class="fas fa-link"></i>
                </span>
                <div>
                    {{ __('Customer Booking Link') . ': ' }}
                    <a href="{{ route('payment.redirect', ['id' => $details->id]) }}" target="_blank">
                        {{ route('payment.redirect', ['id' => $details->id]) }}
                    </a>
                </div>
            </div>


            @if ($details->booking_status != 2)
                @if ($details->source === 'whatsapp_bot')
                    @if ($details->send_payment_link == 1)
                        @php
                            $statusClasses = [
                                0 => ['alert' => 'alert-warning', 'bg' => 'bg-warning','text'=>'text-warning'],
                                1 => ['alert' => 'alert-success', 'bg' => 'bg-success','text'=>'text-success'],
                                2 => ['alert' => 'alert-primary', 'bg' => 'bg-primary','text'=>'text-primary'],
                                3 => ['alert' => 'alert-danger', 'bg' => 'bg-danger','text'=>'text-danger'],
                                4 => ['alert' => 'alert-secondary', 'bg' => 'bg-secondary','text'=>'text-secondary'],
                            ];

                            $status = $statusClasses[$details->payment_status] ?? $statusClasses[0];
                        @endphp
                        <div class="alert {{ $status['alert'] }} alert-with-icon">

                            <span class="{{ $status['bg'] }} text-white mr-2 p-1">
                                <i class="fas fa-clock"></i>
                            </span>

                            {{ __('Payment link has been sent to the customer and payment status is') . ' ' }}


                                {{ $details->formatted_payment_status }}


                        </div>
                    @else
                        <div class="alert alert-danger alert-with-icon">
                            <span class="bg-danger text-white mr-2 p-1">
                                <i class="fas fa-times"></i>
                            </span>
                            {{ __('Payment link has not been sent to the customer via WhatsApp.') }}
                        </div>
                    @endif
                @endif
            @else
                <div class="alert alert-danger alert-with-icon">
                    <span class="bg-danger text-white mr-2 p-1">
                        <i class="fas fa-times"></i>
                    </span>
                    {{ __('This booking has been rejected.') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">{{ __('Edit Booking Details') }}</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('tenant.room_bookings.all_bookings') }}">
                        <span class="btn-label">
                            <i class="fas fa-backward"></i>
                        </span>
                        {{ __('Back') }}
                    </a>
                </div>

                <div class="card-body">

                    <form id="roomBookingForm" action="{{ route('tenant.room_bookings.update_booking') }}" method="POST">
                        @csrf
                        <input type="hidden" name="rooms_json" id="rooms_json" value="">
                        <div class="row">
                            <div class="col-lg-6">

                                <input type="hidden" name="booking_id" value="{{ $details->id }}">
                                <input type="hidden" name="room_category_id" value="{{ $details->room_category_id }}">

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Booking Number') }}</label>
                                            <input type="text" class="form-control"
                                                value="{{ '#' . $details->booking_number }}" readonly>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Booking Date') }} <span class="text-danger">**</span></label>
                                            <input type="text" class="form-control"
                                                value="{{ date_format($details->created_at, 'F d, Y') }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Check In / Out Date') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('Select Dates') }}" id="date-range" name="dates"
                                                value="{{ $details->arrival_date . ' - ' . $details->departure_date }}"
                                                readonly onchange="sendRoomData()" />
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_dates"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Total Room') }} <span class="text-danger">**</span></label>
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('Enter Total Room') }}" name="total_rooms"
                                                value="{{ $details->total_rooms }}" onchange="sendRoomData()" />
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_booking_total_rooms"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Number of Nights') }} <span class="text-danger">**</span></label>
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('Number of Nights') }}" id="night" name="nights"
                                                value="{{ $interval2->days }}" readonly>
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
                                                value="{{ $details->adult }}">
                                            @error('adult')
                                                <p class="mt-1 mb-0 ml-1 text-danger">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Number of Child') }} <span class="text-danger">**</span></label>
                                            <input type="number" class="form-control"
                                                placeholder="{{ __('Enter Number of Child') }}" name="child"
                                                value="{{ $details->child }}">
                                            @error('child')
                                                <p class="mt-1 mb-0 ml-1 text-danger">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Total Rent') . ' (' . $details->currency_text . ')' }}</label>
                                            <input type="text" class="form-control" name="total"
                                                value="{{ $details->total_rent }}" readonly id="total">
                                        </div>
                                    </div>


                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Discount') . ' (' . $details->currency_text . ')' }}</label>
                                            <input type="text" class="form-control" name="discount"
                                                value="{{ $details->discount }}" id="discount"
                                                placeholder="Enter Discount Amount" onchange="sendRoomData()" />
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
                                                value="{{ $details->customer_name }}">
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_customer_name"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Customer Email') }} <span class="text-danger">**</span></label>
                                            <input type="email" class="form-control"
                                                placeholder="{{ __('Enter Customer Email') }}" name="customer_email"
                                                value="{{ $details->customer_email }}">
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_customer_email"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Customer Phone Number') }} <span
                                                    class="text-danger">**</span></label>
                                            <input type="text" class="form-control"
                                                placeholder="{{ __('Enter Phone Number') }}" name="customer_phone"
                                                value="{{ $details->customer_phone }}">
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_customer_phone"></p>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Room Name') }}</label>
                                            <input type="text" class="form-control" value="{{ $roomTitle }}"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Payment Method') }} <span class="text-danger">**</span></label>
                                            <select name="payment_method" class="form-control">
                                                <option disabled>{{ __('Select a Method') }}</option>

                                                @if (count($onlineGateways) > 0)
                                                    @foreach ($onlineGateways as $onlineGateway)
                                                        <option
                                                            {{ $details->payment_method == $onlineGateway->name ? 'selected' : '' }}
                                                            value="{{ $onlineGateway->name }}">
                                                            {{ $onlineGateway->name }}
                                                        </option>
                                                    @endforeach
                                                @endif

                                                @if (count($offlineGateways) > 0)
                                                    @foreach ($offlineGateways as $offlineGateway)
                                                        <option
                                                            {{ $details->payment_method == $offlineGateway->name ? 'selected' : '' }}
                                                            value="{{ $offlineGateway->name }}">
                                                            {{ $offlineGateway->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @error('payment_method')
                                                <p class="mt-1 mb-0 ml-1 text-danger">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Payment Status') }} <span class="text-danger">**</span></label>
                                            <select name="payment_status" class="form-control" id="payment_status"
                                                {{ !empty($bookingAdjustment) && $bookingAdjustment->type == 'extra_payment' ? 'disabled' : '' }}>
                                                <option selected disabled>{{ __('Select Payment Status') }}</option>
                                                <option @selected($details->payment_status == 0) value="0">
                                                    {{ __('Unpaid') }}
                                                </option>
                                                <option @selected($details->payment_status == 1) value="1">
                                                    {{ __('Full Paid') }}
                                                </option>
                                                <option @selected($details->payment_status == 2) value="2">
                                                    {{ __('Partial Paid') }}
                                                </option>
                                                <option @selected($details->payment_status == 3) value="3">
                                                    {{ __('Cancelled') }}
                                                </option>
                                            </select>
                                            @if (!empty($bookingAdjustment) && $bookingAdjustment->type == 'extra_payment')
                                                <input type="hidden" name="payment_status"
                                                    value="{{ $details->payment_status }}">
                                            @endif
                                            @error('payment_status')
                                                <p class="mt-1 mb-0 ml-1 text-danger">{{ $message }}</p>
                                            @enderror
                                            @if (!empty($bookingAdjustment) && $bookingAdjustment->type == 'extra_payment')
                                                <p class="mt-2 mb-2">
                                                    <span class="text-warning">
                                                        {{ __('Click') }}
                                                    </span>
                                                    <a href="{{ route('tenant.room_bookings.update_extra_payment', ['booking_id' => $details->id]) }}"
                                                        id="extra-payment-accept"
                                                        data-url="{{ route('tenant.room_bookings.update_extra_payment', ['booking_id' => $details->id]) }}"
                                                        class="text-primary fw-bold">
                                                        {{ __('Confirm Full Payment') }}
                                                    </a>
                                                    <span class="text-warning">
                                                        {{ __('to complete payment and update due amount.') }}
                                                    </span>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    @php
                                       $payingDivStyle =  (empty($bookingAdjustment) && $bookingAdjustment->type != 'extra_payment') && $details->payment_status == 2;
                                    @endphp
                                    <div class="col-lg-6" id="paying_amount"
                                        style="{{ $payingDivStyle ? '' : 'display: none;' }}">
                                        <div class="form-group">
                                            <label>{{ __('Partial Amount') }} <span class="text-danger">**</span></label>
                                            <input type="number" class="form-control" step="0.01"
                                                name="paying_amount" value="{{ $details->partial_amount }}"
                                                {{ !empty($bookingAdjustment) && $bookingAdjustment->type == 'extra_payment' ? 'readonly' : '' }}>
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_paying_amount"></p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __('Booking Status') }} <span class="text-danger">**</span></label>
                                            <select name="booking_status" class="form-control">
                                                <option selected disabled>{{ __('Select Booking Status') }}</option>
                                                <option {{ $details->booking_status == 0 ? 'selected' : '' }}
                                                    value="0">
                                                    {{ __('Pending') }}
                                                </option>

                                                <option {{ $details->booking_status == 1 ? 'selected' : '' }}
                                                    value="1">
                                                    {{ __('Confirmed') }}
                                                </option>

                                                <option {{ $details->booking_status == 2 ? 'selected' : '' }}
                                                    value="2">
                                                    {{ __('Rejected') }}
                                                </option>
                                            </select>
                                            <p class="mt-1 mb-0 ml-1 em text-danger" id="er_booking_status"></p>
                                        </div>
                                    </div>





                                    @if ($details->tax >= 0 || $userBs->room_tax_status == 1)
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>{{ __('Tax') . ' (' . $details->currency_text . ')' }}</label>
                                                <input type="text" class="form-control" name="tax"
                                                    value="{{ $details->tax_amount }}" id="tax"
                                                    placeholder=" {{ __('Enter Tax Amount') }}" oninput="applyTaxFee()">

                                            </div>
                                        </div>
                                    @endif

                                    @if ($details->fee >= 0 || $userBs->room_fee_status == 1)
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>{{ __('Fee') . ' (' . $details->currency_text . ')' }}</label>
                                                <input type="text" class="form-control" name="fee"
                                                    value="{{ $details->fee }}" id="fee"
                                                    placeholder=" {{ __('Enter Fee Amount') }}" oninput="applyTaxFee()">
                                            </div>
                                        </div>
                                    @endif
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
                                                    @include('user.rooms.booking.booking-engine.edit-room-assignment')

                                                </div>
                                            </div>
                                            <div class="col-xl-12">
                                                <div class="card">
                                                    <div
                                                        class="card-header d-flex justify-content-between align-items-center">
                                                        <div class="card-title mb-0">
                                                            <h3 class="mb-0">{{ __('Booked Rooms') }}</h3>
                                                        </div>
                                                        @if (is_null($details->reserved_dates_info) || $details->reserved_dates_info == '[]')
                                                            <div
                                                                class="d-flex flex-wrap align-items-center small text-primary">
                                                                <span class="mr-3">
                                                                    {{ __('Received so far') }}:
                                                                    <strong>{{ userPriceFormat($userId, $details->paid_amount) }}</strong>
                                                                </span>
                                                                <span>
                                                                    {{ __('Estimated total before room assignment') }}:
                                                                    <strong>{{ userPriceFormat($userId, $details->due) }}</strong>
                                                                </span>
                                                            </div>
                                                        @endif
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
                                                                $totalRent = $totalRent * (int) $details->total_rooms;
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
                                                                            <span
                                                                                class="taxChafrge">{{ $currencySymbol }}
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
                                                                class="d-flex justify-content-between align-items-center border-top p-2 px-3 bg-light bg-light">
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

                                                            @if ($bookingAdjustment)
                                                                @if ($details->due == 0)
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-center p-2 px-3 bg-primary text-white">
                                                                        <span>{{ __('Paid Amount') }}</span>
                                                                        <span>
                                                                            @if ($symbolPosition === 'left')
                                                                                {{ $currencySymbol }}
                                                                                {{ number_format($bookingAdjustment->grand_total, 2) }}
                                                                            @else
                                                                                {{ number_format($bookingAdjustment->grand_total, 2) }}
                                                                                {{ $currencySymbol }}
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                                @if ($bookingAdjustment->type == 'refund')
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-center p-2 px-3 bg-danger text-white">
                                                                        <span>
                                                                            {{ __('Refund Amount') }}
                                                                            <br>
                                                                            <a href="{{ route('tenant.room_bookings.all_bookings', ['booking_no' => $details->booking_number]) }}"
                                                                                class="text-white fw-bold">
                                                                                <u>{{ __('Click here to refund') }}</u>
                                                                            </a>
                                                                        </span>
                                                                        <span>
                                                                            @if ($symbolPosition === 'left')
                                                                                {{ $currencySymbol }}
                                                                                {{ number_format($bookingAdjustment->amount, 2) }}
                                                                            @else
                                                                                {{ number_format($bookingAdjustment->amount, 2) }}
                                                                                {{ $currencySymbol }}
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                                @if ($bookingAdjustment->type == 'extra_payment')
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-center p-2 px-3 bg-danger text-white">
                                                                        <span>{{ __('Due Amount') }}</span>
                                                                        <span>
                                                                            @if ($symbolPosition === 'left')
                                                                                {{ $currencySymbol }}
                                                                                {{ number_format($bookingAdjustment->amount, 2) }}
                                                                            @else
                                                                                {{ number_format($bookingAdjustment->amount, 2) }}
                                                                                {{ $currencySymbol }}
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            @endif
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
                                {{ __('Save Changes') }}
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
        // assign php value to js variable
        var currency = "{{ $details->currency_text }}";
        var room_fee = "{{ $userBs->room_fee_status == 1 ? $userBs->room_fee : 0 }}";
        var room_tax = "{{ $userBs->room_tax_status == 1 ? $userBs->room_tax : 0 }}";
        var currency = "{{ $currencyInfo->base_currency_text }}";
        var roomUpdateUrl = "{{ route('tenant.rooms_management.bookings.total_rooms') }}";
    </script>

    <script type="text/javascript" src="{{ asset('assets/tenant/js/booking.js') }}"></script>
@endsection
