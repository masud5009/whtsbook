@extends('user.layout')

@section('content')
    <div class="page-header">
        @if (request()->routeIs('tenant.rooms_management.report'))
            <h4 class="page-title">{{ __('Room Report') }}</h4>
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
                @if (request()->routeIs('tenant.rooms_management.report'))
                    <a href="#">{{ __('Room Report') }}</a>
                @endif
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-10">
                            <form action="{{ route('tenant.rooms_management.report') }}" method="GET">
                                <div class="row no-gutters room-report-filter-row">
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>{{ __('From') }}</label>
                                            <input name="from" type="text" class="form-control datepicker-2"
                                                placeholder="{{ __('Select Start Date') }}"
                                                value="{{ !empty(request()->input('from')) ? request()->input('from') : '' }}"
                                                readonly autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>{{ __('To') }}</label>
                                            <input name="to" type="text" class="form-control datepicker-2"
                                                placeholder="{{ __('Select To Date') }}"
                                                value="{{ !empty(request()->input('to')) ? request()->input('to') : '' }}"
                                                readonly autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>{{ __('Booking No.') }}</label>
                                            <input name="booking_no" type="text" class="form-control"
                                                placeholder="{{ __('Search booking no') }}"
                                                value="{{ request()->input('booking_no') }}" autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>{{ __('Payment Gateways') }}</label>
                                            <select class="form-control h-42" name="payment_gateway">
                                                <option value="">{{ __('All') }}</option>
                                                @if (count($onlineGateways) > 0)
                                                    @foreach ($onlineGateways as $onlineGateway)
                                                        <option value="{{ $onlineGateway->keyword }}"
                                                            {{ request()->input('payment_gateway') == $onlineGateway->keyword ? 'selected' : '' }}>
                                                            {{ $onlineGateway->name }}
                                                        </option>
                                                    @endforeach
                                                @endif

                                                @if (count($offlineGateways) > 0)
                                                    @foreach ($offlineGateways as $offlineGateway)
                                                        <option value="{{ $offlineGateway->name }}"
                                                            {{ request()->input('payment_gateway') == $offlineGateway->name ? 'selected' : '' }}>
                                                            {{ $offlineGateway->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>{{ __('Payment Status') }}</label>
                                            <select class="form-control h-42" name="payment_status">
                                                <option value="">{{ __('All') }}</option>
                                                <option value="completed"
                                                    {{ request()->query('payment_status') == "completed" ? 'selected' : '' }}>
                                                    {{ __('Completed') }}
                                                </option>
                                                <option value="incompleted" {{ !empty(request()->query('payment_status')) && request()->query('payment_status') == 'incompleted' ? 'selected' : '' }}>
                                                    {{ __('Incompleted') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <button type="submit"
                                            class="btn btn-primary btn-sm ml-lg-3 card-header-button room-report-action-btn">
                                            {{ __('Submit') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-2">
                            <a href="{{ route('tenant.rooms_management.export_report', [
                                'from' => request()->input('from'),
                                'to' => request()->input('to'),
                                'booking_no' => request()->input('booking_no'),
                                'payment_gateway' => request()->input('payment_gateway'),
                                'payment_status' => request()->input('payment_status'),
                            ]) }}"
                                class="btn btn-success btn-sm float-right card-header-button room-report-action-btn">
                                <i class="fas fa-file-export"></i> {{ __('Export') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (empty($isFilterApplied))
                                <h3 class="text-center mt-2">{{ __('Please use the filters and click submit to view report data.') }}</h3>
                            @elseif (count($bookings) == 0)
                                <h3 class="text-center mt-2">{{ __('NO ROOM BOOKING FOUND!') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('Booking No.') }}</th>
                                                <th scope="col">{{ __('Title') }}</th>
                                                <th scope="col">{{ __('Customer Name') }}</th>
                                                <th scope="col">{{ __('Rent') }}</th>
                                                <th scope="col">{{ __('Paid via') }}</th>
                                                <th scope="col">{{ __('Payment Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($bookings as $booking)
                                                <tr>
                                                    <td>{{ '#' . $booking->booking_number }}</td>
                                                    <td>{{ $booking->roomTitle }}</td>
                                                    <td>{{ $booking->customer_name }}</td>
                                                    <td>
                                                        {{ $booking->currency_text_position == 'left' ? $booking->currency_text : '' }}
                                                        {{ $booking->grand_total }}
                                                        {{ $booking->currency_text_position == 'right' ? $booking->currency_text : '' }}
                                                    </td>
                                                    <td>{{ $booking->payment_method }}</td>
                                                    <td>
                                                        @if ((int) $booking->payment_status === 1)
                                                            <h2 class="d-inline-block mb-0"><span
                                                                    class="badge badge-success">{{ __('Completed') }}</span>
                                                            </h2>
                                                        @elseif ((int) $booking->payment_status === 3)
                                                            <h2 class="d-inline-block mb-0"><span
                                                                    class="badge badge-warning">{{ __('Partial') }}</span>
                                                            </h2>
                                                        @else
                                                            <h2 class="d-inline-block mb-0"><span
                                                                    class="badge badge-danger">{{ __('Incomplete') }}</span>
                                                            </h2>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="mt-3 text-center">
                        <div class="d-inline-block mx-auto">
                            @if (count($bookings) > 0)
                                {{ $bookings->appends([
                                        'from' => request()->input('from'),
                                        'to' => request()->input('to'),
                                        'booking_no' => request()->input('booking_no'),
                                        'payment_gateway' => request()->input('payment_gateway'),
                                        'payment_status' => request()->input('payment_status'),
                                    ])->links() }}
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript" src="{{ asset('assets/tenant/js/admin-room.js') }}"></script>
@endsection
