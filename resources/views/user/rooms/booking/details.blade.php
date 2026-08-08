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
        <a href="{{ url()->previous() }}" class="btn btn-primary ml-auto">
            <span class="btn-label">
                <i class="fas fa-backward"></i>
            </span>
            {{ __('Back') }}
        </a>
    </div>
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
            <div class="alert alert-warning alert-with-icon">
                <span class="bg-warning text-white mr-2 p-1">
                    <i class="fas fa-clock"></i>
                </span>

                {{ __('Payment link has not been sent yet.') }}
            </div>
        @endif

        @if (empty($details->reserved_dates_info))
            <div class="alert alert-secondary alert-with-icon">
                <span class="bg-info text-white mr-2 p-1">
                    <i class="fas fa-paper-plane"></i>
                </span>
                {{ __('Room assignment is still pending and booking amount has been calculated based on the booked dates. Review the booking and send the room assignment details to the customer from the edit page.') }}
                <a href="{{ route('tenant.room_bookings.booking_edit', ['id' => $details->id]) }}">
                    {{ __('Review Booking') }}
                </a>
            </div>
        @endif

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
    @endif

    <div class="row">

        <div class="col-md-12">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title d-inline-block">{{ __('Booking No') . ':' }}
                                #[{{ $details->booking_number }}] </div>
                        </div>
                        <div class="card-body">
                            <div class="container">

                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Booking Date') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">
                                        {{ \Carbon\Carbon::parse($details->created_at)->format('F d, Y') }}
                                    </div>
                                </div>
                                <hr>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Room Category') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">
                                        <a href="{{ route('front.room.details', ['userId' => $details->user_id, 'slug' => $roomContent->slug]) }}"
                                            target="_blank">
                                            {{ $roomContent->title }}
                                        </a>
                                    </div>
                                </div>


                                <hr>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Total Rent') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">
                                        {{ currencyTextPrice($details->total_rent, $details->currency_text, $details->currency_text_position) }}
                                    </div>
                                </div>
                                <hr>

                                @if ($details->discount > 0)
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <strong>{{ __('Discount') . ':' }}</strong>
                                        </div>
                                        <div class="col-lg-8">
                                            {{ currencyTextPrice($details->discount, $details->currency_text, $details->currency_text_position) }}
                                        </div>
                                    </div>
                                    <hr>
                                @endif

                                @if ($details->tax_amount > 0 || $userBs->room_tax_status == 1)
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <strong>{{ __('Tax') . ' (' . $details->tax_percentage . '%) :' }}</strong>
                                        </div>
                                        <div class="col-lg-8">
                                            {{ currencyTextPrice($details->tax_amount, $details->currency_text, $details->currency_text_position) }}
                                        </div>
                                    </div>
                                    <hr>
                                @endif

                                @if ($details->fee > 0 || $userBs->room_fee_status == 1)
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <strong>{{ __('Fee') . ':' }}</strong>
                                        </div>
                                        <div class="col-lg-8">
                                            {{ currencyTextPrice($details->fee, $details->currency_text, $details->currency_text_position) }}
                                        </div>
                                    </div>
                                    <hr>
                                @endif

                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Grand Total') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">
                                        {{ currencyTextPrice($details->grand_total, $details->currency_text, $details->currency_text_position) }}
                                    </div>
                                </div>
                                <hr>
                                @if ($details->paid_amount > 0 && !is_null($details->paid_amount))
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <strong>{{ __('Paid Amount') . ':' }}</strong>
                                        </div>
                                        <div class="col-lg-8">
                                            {{ currencyTextPrice($details->paid_amount, $details->currency_text, $details->currency_text_position) }}
                                        </div>
                                    </div>
                                    <hr>
                                @endif

                                @if($details->due > 0 && !is_null($details->due))
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <strong>{{ __('Due') . ':' }}</strong>
                                        </div>
                                        <div class="col-lg-8">
                                            {{ currencyTextPrice($details->due, $details->currency_text, $details->currency_text_position) }}
                                        </div>
                                    </div>
                                    <hr>
                                @endif

                                @if ($bookingAdjustment->type == 'extra_payment')
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <strong>{{ __('Due') . ':' }}</strong>
                                        </div>
                                        <div class="col-lg-8">
                                            {{ currencyTextPrice($details->due, $details->currency_text, $details->currency_text_position) }}
                                        </div>
                                    </div>
                                    <hr>
                                @endif

                                @if (in_array(optional($bookingAdjustment)->type, ['extra_payment', 'refund'], true))
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <strong>{{ __('Paid Amount') . ':' }}</strong>
                                        </div>
                                        <div class="col-lg-8">
                                            {{ currencyTextPrice(optional($bookingAdjustment)->grand_total, $details->currency_text, $details->currency_text_position) }}
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            @if (optional($bookingAdjustment)->type == 'extra_payment')
                                                <strong>{{ __('Due') . ':' }}</strong>
                                            @else
                                                <strong>{{ __('Refund Amount') . ':' }}</strong>
                                            @endif
                                        </div>
                                        <div class="col-lg-8">
                                            @if (optional($bookingAdjustment)->type == 'extra_payment')
                                                {{ currencyTextPrice(optional($bookingAdjustment)->amount, $details->currency_text, $details->currency_text_position) }}
                                            @else
                                                {{ __('Complete') }}
                                                ({{ currencyTextPrice($refund->paying_amount, $details->currency_text, $details->currency_text_position) }})
                                                <br>
                                                {{ __('Pending') }}
                                                ({{ currencyTextPrice(optional($bookingAdjustment)->amount, $details->currency_text, $details->currency_text_position) }})
                                            @endif
                                        </div>
                                    </div>
                                    <hr>
                                @endif

                                @if ($details->payment_method)
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <strong>{{ __('Payment Method') . ':' }}</strong>
                                        </div>
                                        <div class="col-lg-8">
                                            {{ __($details->payment_method) }}

                                        </div>
                                    </div>
                                    <hr>
                                @endif

                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Payment Status') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">
                                        @if ($details->payment_status == 1)
                                            <span class="badge badge-success">{{ __('Success') }}</span>
                                        @elseif($details->payment_status == 2)
                                            <span class="badge badge-info text-dark">{{ __('Partial') }}</span>
                                        @elseif($details->payment_status == 3)
                                            <span class="badge badge-danger">{{ __('Rejected') }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ __('Pending') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Booking Status') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">
                                        <span
                                            class="badge
                                        @if ($details->booking_status == 0) bg-warning text-dark
                                        @elseif($details->booking_status == 1)
                                            bg-success text-white
                                        @else
                                            bg-danger text-white @endif
                                        ">
                                            {{ $details->formatted_booking_status }}
                                        </span>
                                    </div>
                                </div>
                                {{-- @endif --}}


                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title d-inline-block">{{ __('Booking Information') }}</div>
                        </div>
                        <div class="card-body">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Arrival Date') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">
                                        {{ \Carbon\Carbon::parse($details->arrival_date)->format('F d, Y') }}</div>
                                </div>
                                <hr>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Departure Date') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">
                                        {{ \Carbon\Carbon::parse($details->departure_date)->format('F d, Y') }}
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Total Room') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">
                                        {{ $details->total_rooms }}
                                    </div>
                                </div>
                                <hr>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Number Of Adult') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">{{ $details->adult }}</div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Number Of Child') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">{{ $details->child }}</div>
                                </div>
                                <hr>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Number Of Nights') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">{{ $interval->days }}</div>
                                </div>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title d-inline-block">{{ __('Billing Details') }}</div>
                        </div>
                        <div class="card-body">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Name') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">{{ $details->customer_name }}</div>
                                </div>
                                <hr>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Email') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">{{ $details->customer_email }}</div>
                                </div>
                                <hr>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <strong>{{ __('Contact Number') . ':' }}</strong>
                                    </div>
                                    <div class="col-lg-8">{{ $details->customer_phone }}</div>
                                </div>
                                <hr>
                                @if (!is_null($customBookingFields))
                                    @foreach ($customBookingFields as $key => $customField)
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <strong>{{ $key . ':' }}</strong>
                                            </div>
                                            <div class="col-lg-8">{{ $customField }}</div>
                                        </div>
                                        <hr>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            @if ($details->send_payment_link == 1 && ($details->can_assign_room == 1 || is_null($details->can_assign_room)))
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title d-inline-block">{{ __('Assigned Room Schedule') }}</div>

                            </div>
                            <div class="card-body">
                                @if (!empty($reserved_dates_info) && $reserved_dates_info->isNotEmpty())
                                    <table class="table table-striped mb-20 table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('SL') }}</th>
                                                <th scope="col">{{ __('Date') }}</th>
                                                <th scope="col">{{ __('Room Number') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($reserved_dates_info as $item)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $item['date'] }}</td>
                                                    <td>
                                                        {{ collect($item['rooms'])->pluck('room_number')->implode(', ') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted">{{ __('No reserved dates found.') }}</p>
                                @endif
                            </div>
                            <div class="card-footer">

                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
