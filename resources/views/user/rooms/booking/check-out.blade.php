@extends('user.layout')

@section('content')
  <div class="page-header">
    @if (request()->routeIs('tenant.room_bookings.check_outs.delayed'))
      <h4 class="page-title">{{ __('Delayed') }}</h4>
    @elseif (request()->routeIs('tenant.room_bookings.check_outs.upcoming'))
      <h4 class="page-title">{{ __('UpComing') }}</h4>
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
        <a href="#">{{ __('Check-Outs') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        @if (request()->routeIs('tenant.room_bookings.check_outs.delayed'))
          <a href="#">{{ __('Delayed') }}</a>
        @elseif (request()->routeIs('tenant.room_bookings.check_outs.upcoming'))
          <a href="#">{{ __('Upcoming') }}</a>
        @endif
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-lg-3">
              <div class="card-title mb-3">
                @if (request()->routeIs('tenant.room_bookings.check_outs.delayed'))
                  {{ __('Delayed Check-Outs') }}
                @elseif (request()->routeIs('tenant.room_bookings.check_outs.upcoming'))
                  {{ __('Upcoming Check-Outs') }}
                @endif
              </div>
            </div>

            <div class="col-lg-12">

              <form
                @if (request()->routeIs('tenant.room_bookings.check_outs.delayed')) action="{{ route('tenant.room_bookings.check_outs.delayed') }}"
                        @elseif (request()->routeIs('tenant.room_bookings.check_outs.upcoming')) action="{{ route('tenant.room_bookings.check_outs.upcoming') }}" @endif
                method="GET">
                <div class="form-input-row">
                  {{-- Booking No --}}
                  <input name="booking_no" type="text" class="form-control" placeholder=" {{ __('Search By Booking No') }}"
                    value="{{ request('booking_no', '') }}">

                  <input name="keyword" id="keyword" type="text" class="form-control"
                    placeholder="{{ __('Search By name/phone/email') }}" value="{{ request()->input('keyword') ?? '' }}">

                  {{-- Date Option --}}
                  <select id="date_option" name="date_option" class="form-control" onchange="handleDateOptionChange()">
                    @if (request()->routeIs('tenant.room_bookings.check_outs.upcoming'))
                      <option value="today" {{ request('date_option', 'today') == 'today' ? 'selected' : '' }}>
                        {{ __('Today') }}</option>
                      <option value="tomorrow" {{ request('date_option') == 'tomorrow' ? 'selected' : '' }}>
                        {{ __('Tomorrow') }}</option>
                      <option value="custom" {{ request('date_option') == 'custom' ? 'selected' : '' }}>
                        {{ __('Custom') }}
                      </option>
                    @else
                      <option value="today" {{ request('date_option', 'today') == 'today' ? 'selected' : '' }}>
                        {{ __('Today') }}</option>
                      <option value="yesterday" {{ request('date_option') == 'yesterday' ? 'selected' : '' }}>
                        {{ __('Yesterday') }}</option>
                      <option value="custom" {{ request('date_option') == 'custom' ? 'selected' : '' }}>
                        {{ __('Custom') }}
                      </option>
                    @endif
                  </select>

                  <input name="date" type="date" id="single_date" class="form-control"
                    value="{{ request('date', \Carbon\Carbon::now()->format('Y-m-d')) }}">

                  {{-- Custom range --}}
                  <input name="start_date" type="date" id="start_date" class="form-control"
                    value="{{ request('start_date', \Carbon\Carbon::now()->format('Y-m-d')) }}">

                  <input name="end_date" type="date" id="end_date" class="form-control"
                    value="{{ request('end_date', \Carbon\Carbon::now()->addWeek()->format('Y-m-d')) }}">

                  <div class="input-group-append">
                    <button class="btn btn-primary serch-btn" type="submit">
                      <i class="fas fa-search"></i> {{ __('Search') }}
                    </button>
                  </div>
                  <button class="btn btn-danger  float-right d-none delete-btn bulk-delete"
                  data-href="{{ route('tenant.room_bookings.bulk_delete_booking') }}">
                  <i class="flaticon-interface-5"></i> {{ __('Delete') }}
                </button>
                </div>
              </form>
            </div>
          </div>
        </div>


        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (request()->routeIs('tenant.room_bookings.check_outs.delayed'))
                <div class="col-lg-6 offset-lg-3">
                  <div class="alert alert-danger text-center" role="alert">
                    @php
                      $opt = request('date_option', 'today');
                    @endphp
                    @if ($opt === 'today')
                      {{ __('Shows all bookings scheduled to check out today, where the check-out time has already passed but the guest has not checked out yet.') }}
                    @elseif ($opt === 'yesterday')
                      {{ __('Shows all bookings scheduled to check out yesterday, where the check-out time had already passed but the guest did not check out.') }}
                    @elseif ($opt === 'custom')
                      {{ __('Shows all bookings scheduled to check out in the selected date range, where the check-out time has already passed but the guest has not checked out yet.') }}
                    @else
                      {{ __('Shows all bookings scheduled to check out, where the check-out time has already passed but the guest has not checked out yet.') }}
                    @endif
                  </div>
                </div>
              @elseif (request()->routeIs('tenant.room_bookings.check_outs.upcoming'))
                <div class="col-lg-6 offset-lg-3">
                  <div class="alert alert-danger text-center" role="alert">
                    @php
                      $opt = request('date_option', 'today');
                    @endphp
                    @if ($opt === 'today')
                      {{ __('Shows all bookings scheduled to check out today, where the check-out time has not yet started.') }}
                    @elseif ($opt === 'tomorrow')
                      {{ __('Shows all bookings scheduled to check out tomorrow, where the check-out time has not yet started.') }}
                    @elseif ($opt === 'custom')
                      {{ __('Shows all bookings scheduled to check out in the selected date range, where the check-out time has not yet started.') }}
                    @else
                      {{ __('Shows all upcoming bookings scheduled to check out, where the check-out time has not yet started.') }}
                    @endif
                  </div>
                </div>
              @endif
              <hr>

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
                        <th scope="col">{{ __('Check In - Check Out') }}</th>
                        <th scope="col">{{ __('Guest') }}</th>
                        <th scope="col">{{ __('Total Amount') }}</th>
                        <th scope="col">{{ __('Stay Status') }}</th>
                        <th scope="col">{{ __('Booking Status') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($bookings as $booking)
                        <tr>
                          <td>
                            <input type="checkbox" class="bulk-check" data-val="{{ $booking->id }}">
                          </td>
                          <td>
                            <a href="{{ route('admin.room_bookings.booking_details_and_edit', $booking->id) }}">
                              <div>
                                {{ '#' . $booking->booking_number }}
                              </div>
                            </a>
                          </td>
                          <td>
                            <div>
                              {{ \Carbon\Carbon::parse($booking->arrival_date)->format('d M, Y') }}
                            </div>
                            <div>
                              {{ \Carbon\Carbon::parse($booking->departure_date)->format('d M, Y') }}
                            </div>
                          </td>
                          <td>
                            <div>
                              {{ $booking->customer_name }}
                            </div>
                            <div>
                              <a href="#">
                                {{ $booking->customer_phone }}
                              </a>
                            </div>
                            <div>
                              <a href="#" class=" mailBtn mailBtn-underline" data-target="#mailModal"
                                data-toggle="modal" data-customer_email="{{ $booking->customer_email }}">
                                {{ $booking->customer_email }}
                              </a>
                            </div>
                          </td>

                          <td>
                            {{ $booking->currency_text_position == 'left' ? $booking->currency_text : '' }}
                            {{ $booking->grand_total }}
                            {{ $booking->currency_text_position == 'right' ? $booking->currency_text : '' }}
                          </td>
                          <td>
                            @if ($booking->booking_status == 2)
                              <span class="badge badge-danger">{{ __('Canceled') }}</span>
                            @else
                              @if ($booking->stay_status == 'checked-out')
                                <span class="badge badge-danger">{{ __('Checked-Out') }}</span>
                              @else
                                <form id="stayStatusForm{{ $booking->id }}" class="d-inline-block"
                                  action="{{ route('tenant.room_bookings.update_stay_status') }}" method="post">
                                  @csrf
                                  <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                                  <select
                                    class="form-control form-control-sm {{ $booking->stay_status == 'checked-out' ? 'bg-danger' : 'bg-success' }}"
                                    name="stay_status"
                                    onchange="document.getElementById('stayStatusForm{{ $booking->id }}').submit();">
                                    <option value="checked-in"
                                      {{ $booking->stay_status == 'checked-in' ? 'selected' : '' }}>
                                      {{ __('Checked In') }}
                                    </option>
                                    <option value="checked-out"
                                      {{ $booking->stay_status == 'checked-out' ? 'selected' : '' }}>
                                      {{ __('Checked Out') }}
                                    </option>
                                  </select>
                                </form>
                              @endif
                            @endif
                          </td>
                          <td>
                            @if ($booking->booking_status === 0)
                              <form id="bookingStatusForm{{ $booking->id }}" class="d-inline-block"
                                action="{{ route('admin.room_bookings.update_booking_status') }}" method="post">
                                @csrf
                                <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                                <select class="form-control form-control-sm bg-warning" name="booking_status"
                                  onchange="handleBookingStatusChange(this, {{ $booking->id }})">
                                  <option value="0" {{ $booking->booking_status === 0 ? 'selected' : '' }}>
                                    {{ __('Pending') }}
                                  </option>
                                  <option value="1" {{ $booking->booking_status === 1 ? 'selected' : '' }}>
                                    {{ __('Approved') }}
                                  </option>
                                  <option value="2" {{ $booking->booking_status === 2 ? 'selected' : '' }}>
                                    {{ __('Canceled') }}
                                  </option>
                                </select>
                              </form>
                              @includeIf('user.rooms.booking.make-refund')
                            @else
                              @if ($booking->booking_status == 1)
                                <h2 class="d-inline-block"><span
                                    class="badge badge-success">{{ __('Approved') }}</span>
                                </h2>
                              @else
                                <h2 class="d-inline-block"><span class="badge badge-danger">{{ __('Canceled') }}</span>
                                </h2>
                              @endif
                            @endif
                          </td>

                          <td>
                            <div class="dropdown">
                              <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                {{ __('Select') }}
                              </button>

                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                @if (!empty($booking->attachment))
                                  <a class="btn btn-sm btn-info" href="#" data-toggle="modal"
                                    data-target="#attachmentModal{{ $booking->id }}">
                                    {{ __('Attachment') }}
                                  </a>
                                @endif

                                @if ($booking->invoice)
                                  <a href="{{ asset('assets/tenant/invoices/rooms/' . $booking->invoice) }}"
                                    class="dropdown-item" target="_blank">
                                    {{ __('Invoice') }}
                                  </a>
                                @endif

                                <a href="#" class="dropdown-item mailBtn" data-target="#mailModal"
                                  data-toggle="modal" data-customer_email="{{ $booking->customer_email }}">
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
                        @includeIf('user.rooms.booking.show-attachment')
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

  @includeIf('user.rooms.booking.send-mail')
@endsection

@section('script')
  <script>
    // Set data attribute for booking type detection in external JS
    document.body.setAttribute('data-is-upcoming', '{{ request()->routeIs('tenant.room_bookings.check_outs.upcoming') ? 'true' : 'false' }}');
  </script>
  <script type="text/javascript" src="{{ asset('assets/tenant/js/booking-checkout.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/tenant/js/admin-room.js') }}"></script>
@endsection
