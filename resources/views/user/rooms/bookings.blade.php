@extends('user.layout')

@section('content')
    <div class="page-header">
        @if (request()->routeIs('tenant.room_bookings.all_bookings'))
            <h4 class="page-title">{{ __('All Bookings') }}</h4>
        @elseif (request()->routeIs('tenant.room_bookings.paid_bookings'))
            <h4 class="page-title">{{ __('Paid Bookings') }}</h4>
        @elseif (request()->routeIs('tenant.room_bookings.unpaid_bookings'))
            <h4 class="page-title">{{ __('Unpaid Bookings') }}</h4>
        @endif

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
                <a href="#">{{ __('Rooms Bookings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                @if (request()->routeIs('tenant.room_bookings.all_bookings'))
                    <a href="#">{{ __('All Bookings') }}</a>
                @elseif (request()->routeIs('tenant.room_bookings.paid_bookings'))
                    <a href="#">{{ __('Paid Bookings') }}</a>
                @elseif (request()->routeIs('tenant.room_bookings.unpaid_bookings'))
                    <a href="#">{{ __('Unpaid Bookings') }}</a>
                @endif
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">

                        <div class="col-lg-4">
                            <div class="card-title">
                                @if (request()->routeIs('tenant.room_bookings.all_bookings'))
                                    {{ __('All Room Bookings') }}
                                @elseif (request()->routeIs('tenant.room_bookings.paid_bookings'))
                                    {{ __('Paid Room Bookings') }}
                                @elseif (request()->routeIs('tenant.room_bookings.unpaid_bookings'))
                                    {{ __('Unpaid Room Bookings') }}
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <form
                                @if (request()->routeIs('tenant.room_bookings.all_bookings')) action="{{ route('tenant.room_bookings.all_bookings') }}"
                @elseif (request()->routeIs('tenant.room_bookings.paid_bookings'))
                  action="{{ route('tenant.room_bookings.paid_bookings') }}"
                @elseif (request()->routeIs('tenant.room_bookings.unpaid_bookings'))
                  action="{{ route('tenant.room_bookings.unpaid_bookings') }}" @endif
                                method="GET">
                                <input name="booking_no" type="text" class="form-control"
                                    placeholder=" {{ __('Search By Booking No') }}"
                                    value="{{ !empty(request()->input('booking_no')) ? request()->input('booking_no') : '' }}">
                            </form>
                        </div>

                        <div class="col-lg-4 mt-2 mt-lg-0">

                            <a href="#" data-toggle="modal" data-target="#roomModal"
                                class="btn btn-primary btn-sm float-right mt-1 ml-2">
                                {{ __('Add Booking') }}
                            </a>

                            <button class="btn btn-danger btn-sm float-right d-none bulk-delete mt-1 ml-2"
                                data-href="{{ route('tenant.room_bookings.bulk_delete_booking') }}">
                                <i class="flaticon-interface-5"></i> {{ __('Delete') }}
                            </button>
                        </div>

                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($bookings) == 0)
                                <h3 class="text-center mt-2">{{ __('NO ROOM BOOKING FOUND!') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col">{{ __('Booking No.') }}</th>
                                                <th scope="col">{{ __('Room') }}</th>

                                                <th scope="col">{{ __('Rent') }}</th>

                                                <th scope="col">{{ __('Paid via') }}</th>
                                                <th scope="col">{{ __('Payment Status') }}</th>
                                                <th scope="col">{{ __('Attachment') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($bookings as $booking)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $booking->id }}">
                                                    </td>
                                                    <td>{{ '#' . $booking->booking_number }}</td>
                                                    <td>
                                                        @php
                                                            $title = $booking->hotelRoom->roomContent
                                                                ->where('language_id', $language->id)
                                                                ->where('user_id', Auth::guard('web')->user()->id)
                                                                ->first()->title;
                                                        @endphp
                                                        {{ $title }}
                                                    </td>
                                                    <td>
                                                        {{ $booking->currency_text_position == 'left' ? $booking->currency_text : '' }}
                                                        {{ $booking->grand_total }}
                                                        {{ $booking->currency_text_position == 'right' ? $booking->currency_text : '' }}
                                                    </td>

                                                    <td>{{ $booking->payment_method }}</td>
                                                    <td>
                                                        @if ($booking->gateway_type == 'online')
                                                            @if ($booking->payment_status == 1)
                                                                <h2 class="d-inline-block"><span
                                                                        class="badge badge-success">{{ __('Paid') }}</span>
                                                                </h2>
                                                            @else
                                                                <h2 class="d-inline-block"><span
                                                                        class="badge badge-danger">{{ __('Unpaid') }}</span>
                                                                </h2>
                                                            @endif
                                                        @else
                                                            @if ($booking->payment_offline_status === null)
                                                                <form id="paymentStatusForm{{ $booking->id }}"
                                                                    class="d-inline-block"
                                                                    action="{{ route('tenant.room_bookings.update_payment_status') }}"
                                                                    method="post">
                                                                    @csrf
                                                                    <input type="hidden" name="booking_id"
                                                                        value="{{ $booking->id }}">
                                                                    <select
                                                                        class="form-control form-control-sm {{ $booking->payment_status == 1 ? 'bg-success' : 'bg-danger' }}"
                                                                        name="payment_status"
                                                                        onchange="document.getElementById('paymentStatusForm{{ $booking->id }}').submit();">
                                                                        <option value="1"
                                                                            {{ $booking->payment_status == 1 ? 'selected' : '' }}>
                                                                            {{ __('Paid') }}
                                                                        </option>
                                                                        <option value="0"
                                                                            {{ $booking->payment_status == 0 ? 'selected' : '' }}>
                                                                            {{ __('Unpaid') }}
                                                                        </option>
                                                                    </select>
                                                                </form>
                                                            @endif
                                                            @if ($booking->payment_offline_status === 1)
                                                                <span
                                                                    class="badge badge-success">{{ __('Paid') }}</span>
                                                            @elseif($booking->payment_offline_status === 0)
                                                                <span class="badge badge-danger">{{ __('Unpaid') }}</span>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (!empty($booking->attachment))
                                                            <a class="btn btn-sm btn-info" href="#"
                                                                data-toggle="modal"
                                                                data-target="#attachmentModal{{ $booking->id }}">
                                                                {{ __('Show') }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-secondary btn-sm dropdown-toggle"
                                                                type="button" id="dropdownMenuButton"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                {{ __('Select') }}
                                                            </button>

                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                                                <a href="{{ route('tenant.room_bookings.booking_details', ['id' => $booking->id]) }}"
                                                                    class="dropdown-item">
                                                                    {{ __('Details') }}
                                                                </a>

                                                                <a href="{{ route('tenant.room_bookings.booking_edit', ['id' => $booking->id]) }}"
                                                                    class="dropdown-item">
                                                                    {{ __('Edit') }}
                                                                </a>
                                                                @if (!empty($booking->invoice))
                                                                    <a href="{{ asset('assets/tenant/invoices/rooms/' . $booking->invoice) }}"
                                                                        class="dropdown-item" target="_blank">
                                                                        {{ __('Invoice') }}
                                                                    </a>
                                                                @endif

                                                                <a href="#" class="dropdown-item mailBtn"
                                                                    data-target="#mailModal" data-toggle="modal"
                                                                    data-customer_email="{{ $booking->customer_email }}">
                                                                    {{ __('Send Mail') }}
                                                                </a>

                                                                <form class="deleteForm d-block"
                                                                    action="{{ route('tenant.room_bookings.delete_booking', ['id' => $booking->id]) }}"
                                                                    method="post">
                                                                    @csrf
                                                                    <button type="submit" class="deleteBtn">
                                                                        {{ __('Delete') }}
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>

                                                @includeIf('user.rooms.show_attachment')
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="d-inline-block mx-auto">
                            {{ $bookings->appends(['booking_no' => request()->input('booking_no')])->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @includeIf('user.rooms.send_mail')

    @includeIf('user.rooms.booking.all-room-categories')
@endsection

@section('script')
    <script>
        "use strict"
        var room_fee = 0;
        var room_tax = 0;
    </script>

    <script type="text/javascript" src="{{ asset('assets/tenant/js/admin-room.js') }}"></script>
@endsection
