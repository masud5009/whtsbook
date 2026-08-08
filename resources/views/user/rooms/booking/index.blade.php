@extends('user.layout')

@section('content')
    @php
        use App\Http\Helpers\StaffAuthHelper;
        $canBookingEdit = StaffAuthHelper::hasPermission('Room Bookings Edit');
    @endphp
    <div class="page-header">
        <h4 class="page-title">{{ $pageTitle }}</h4>

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
                <a href="#">{{ $pageTitle }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            @if (session()->has('not-assigned-booking-id') && $canBookingEdit)
                <div class="alert alert-danger" role="alert" id="roomAssignmentAlert">
                    {{ __('Room not assigned yet. Please assign room first to update the booking status.') }}
                    <a
                        href="{{ route('tenant.room_bookings.booking_edit', ['id' => session()->get('not-assigned-booking-id')]) }}">
                        {{ __('Assign From Here') }}
                    </a>
                </div>
            @endif

            @if ($isAnyUnassignedRoomAvailable && request()->input('unassigned') != 1 && $canBookingEdit)
                <div class="alert alert-info alert-with-icon">
                    <span class="bg-info text-white mr-2 p-1">
                        <i class="fas fa-tasks"></i>
                    </span>
                    <strong>
                        {{ __('It looks like you may have missed assigning rooms to some bookings. Please check which bookings are still unassigned and assign them') }}
                        <a href="{{ url()->current() . '?unassigned=1' }}">{{ __('click here to view') }}</a>
                    </strong>
                </div>
            @endif
            <div class="card callback-panel mb-4">
                <button type="button" class="callback-panel__header callback-panel__header--compact" data-toggle="collapse"
                    data-target="#callbackPanelCollapse" aria-expanded="true" aria-controls="callbackPanelCollapse">
                    <span class="callback-panel__header-copy callback-panel__header-copy--title-only">
                        <span class="callback-panel__icon mr-3">
                            <i class="fas fa-question-circle"></i>
                        </span>
                        <span>
                            <span class="callback-panel__title">{{ __('Booking Process Guide') }}</span>
                        </span>
                    </span>
                    <span class="callback-panel__panel-icon">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </button>

                <div id="callbackPanelCollapse" class="collapse">
                    <div class="card-body px-4 pb-4 pt-0">
                        <div class="callback-panel__accordion" id="callbackUrlAccordion">

                            <div class="callback-panel__verify-box">
                                <div class="callback-panel__verify-title">
                                    <i class="fab fa-whatsapp"></i>
                                    <span>{{ __('Step 1: Booking Request') }}</span>
                                </div>

                                <p class="callback-panel__verify-text">
                                    {{ __('A booking request will come from WhatsApp only(taken by Al assistant). If needed, the admin can also add a booking manually by clicking the “Add Booking” button below.') }}
                                </p>
                                <p class="callback-panel__verify-text">
                                    <strong
                                        class="text-info">{{ __('Note') . ': ' }}</strong>{{ __('A notification email will be sent to the admin/staff when a booking request is confirmed from WhatsApp by AI assistant') }}
                                </p>
                            </div>

                            <div class="callback-panel__verify-box">
                                <div class="callback-panel__verify-title">
                                    <i class="fas fa-link"></i>
                                    <span>{{ __('Step 2: Send Payment Link') }}</span>
                                </div>

                                <p class="callback-panel__verify-text">
                                    {{ __('You or any staff member can click the ‘Send Payment Link’ button to send the payment link to the customer on WhatsApp so they can complete the payment') }}
                                </p>
                            </div>

                            <div class="callback-panel__verify-box">
                                <div class="callback-panel__verify-title">
                                    <i class="fas fa-bed"></i>
                                    <span>{{ __('Step 3: Assign Room') }}</span>
                                </div>

                                <p class="callback-panel__verify-text">
                                    {{ __('Once the payment is completed, click the ‘Edit’ button on the booking to assign rooms and update it') }}
                                </p>
                            </div>

                            <div class="callback-panel__verify-box">
                                <div class="callback-panel__verify-title">
                                    <i class="fas fa-info-circle"></i>
                                    <span>{{ __('Refund Note') }}</span>
                                </div>

                                <p class="callback-panel__verify-text">
                                <ul>
                                    <li> {{ __('If the booking is rejected after the payment has been completed') }}</li>
                                    <li>
                                        {{ __('If the booking is updated in a way that reduces the total amount, then the remaining balance will be refunded. For example, if the customer reduces the number of rooms later') }}
                                    </li>
                                </ul>
                                </p>
                            </div>

                            <div class="callback-panel__verify-box">
                                <div class="callback-panel__verify-title">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>{{ __('Extra Room & Additional Payment') }}</span>
                                </div>

                                <p class="callback-panel__verify-text">
                                    {{ __('If more rooms are added after payment, the total price will increase. After the booking is updated, a new payment link will be sent to the customer to pay the extra amount') }}
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">

                        <div class="col-lg-9">
                            <form action="{{ url()->current() }}" method="GET" id="searchForm">
                                <div class="form-row">

                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <input name="booking_no" type="text" class="form-control search-input"
                                            placeholder="{{ __('Search By Booking No.') }}"
                                            value="{{ request('booking_no') }}">
                                    </div>

                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <input name="keyword" type="text" class="form-control search-input"
                                            placeholder="{{ __('Search By name/phone/email') }}"
                                            value="{{ request('keyword') }}">
                                    </div>

                                    <div class="col-md-3 mb-2 mb-md-0">
                                        @if ($canBookingEdit && request()->routeIs('tenant.room_bookings.all_bookings'))
                                            <select name="unassigned" class="form-control" onchange="this.form.submit()">
                                                <option disabled @selected(!request('unassigned'))>
                                                    {{ __('Search By Room Assignment') }}
                                                </option>
                                                <option value="" @selected(request('unassigned') == '')>
                                                    {{ __('All') }}
                                                </option>
                                                <option value="1" @selected(request('unassigned') == '1')>
                                                    {{ __('Unassigned') }}
                                                </option>
                                                <option value="0" @selected(request('unassigned') == '0')>
                                                    {{ __('Assigned') }}
                                                </option>
                                            </select>
                                        @endif
                                    </div>

                                    <div class="col-md-3 mb-2 mb-md-0">
                                        @if (request()->routeIs('tenant.room_bookings.all_bookings'))
                                            <select name="status" class="form-control" onchange="this.form.submit()">

                                                <option disabled @selected(!request('status'))>
                                                    {{ __('Search By Booking Status') }}
                                                </option>

                                                <option value="" @selected(request('status') == '')>
                                                    {{ __('All') }}
                                                </option>

                                                <option value="approved" @selected(request('status') == 'approved')>
                                                    {{ __('Approved') }}
                                                </option>

                                                <option value="pending" @selected(request('status') == 'pending')>
                                                    {{ __('Pending') }}
                                                </option>

                                                <option value="canceled" @selected(request('status') == 'canceled')>
                                                    {{ __('Canceled') }}
                                                </option>

                                            </select>
                                        @endif
                                    </div>

                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <button type="submit" class="btn btn-primary w-100 search-btn d-none"
                                            id="searchBtn">
                                            <i class="fas fa-search mr-2"></i>{{ __('Search') }}
                                        </button>
                                    </div>

                                </div>
                            </form>
                        </div>

                        <div class="col-lg-3 text-right mt-2 mt-lg-0">
                            <div class="btn-group">
                                @if ($canBookingEdit)
                                    <a href="#" data-toggle="modal" data-target="#roomModal"
                                        class="btn btn-primary">
                                        <i class="fas fa-plus mr-2"></i> {{ __('Add Booking') }}
                                    </a>
                                @endif

                                @if (StaffAuthHelper::hasPermission('Room Bookings Delete'))
                                    <button class="btn btn-danger d-none bulk-delete"
                                        data-href="{{ route('tenant.room_bookings.bulk_delete_booking') }}">
                                        <i class="flaticon-interface-5"></i> {{ __('Delete') }}
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>



                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (request()->routeIs('tenant.room_bookings.active_bookings'))
                                <div class="col-lg-6 offset-lg-3">
                                    <div class="alert alert-danger text-center" role="alert">
                                        {{ __('Shows all active bookings where the guest has already checked in but has not yet checked out.') }}
                                    </div>
                                </div>
                                <hr>
                            @endif
                            @if (count($bookings) == 0)
                                <h3 class="text-center mt-2">{{ __('NO ROOM BOOKING FOUND!') }}</h3>
                            @else
                                @includeIf('user.rooms.booking.partials.booking-table')
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

    @includeIf('user.rooms.booking.send-mail')
    @includeIf('user.rooms.booking.all-room-categories')
    @includeIf('user.rooms.booking.refund-modal')
@endsection

@section('script')
    <script>
        let getLangwiseRoomCategoryUrl = "{{ route('tenant.rooms_management.get_langwise_room_category') }}";
    </script>
    <script src="{{ asset('assets/tenant/js/rooms/room.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/tenant/js/admin-room.js') }}"></script>
@endsection
