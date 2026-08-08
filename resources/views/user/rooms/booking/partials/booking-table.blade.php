@php
    use App\Http\Helpers\StaffAuthHelper;

    $canPaymentLink = StaffAuthHelper::hasPermission('Room Bookings Payment Link');
    $canStayStatus = StaffAuthHelper::hasPermission('Room Bookings Stay Status');
    $canPaymentStatus = StaffAuthHelper::hasPermission('Room Bookings Payment Status');
    $canRefundStatus = StaffAuthHelper::hasPermission('Room Bookings Refund Status');
    $canBookingStatus = StaffAuthHelper::hasPermission('Room Bookings Booking Status');
    $canBookingSource = StaffAuthHelper::hasPermission('Room Bookings Booking Source');
    $canBookingDetails = StaffAuthHelper::hasPermission('Room Bookings Details');
    $canBookingEdit = StaffAuthHelper::hasPermission('Room Bookings Edit');
    $canBookingSendMail = StaffAuthHelper::hasPermission('Room Bookings Send Mail');
    $canBookingWhatsapp = StaffAuthHelper::hasPermission('Room Bookings WhatsApp Message');
    $canBookingDelete = StaffAuthHelper::hasPermission('Room Bookings Delete');
    $canActionColumn = $canBookingDetails || $canBookingEdit || $canBookingSendMail || $canBookingWhatsapp || $canBookingDelete;
@endphp
<div class="table-responsive">
    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th scope="col">
                    <input type="checkbox" class="bulk-check" data-val="all">
                </th>
                <th scope="col">{{ __('Booking No.') }}</th>
                <th scope="col">{{ __('Check In - Check Out') }}</th>
                <th scope="col">{{ __('Payment Link') }}</th>
                <th scope="col">{{ __('Stay Status') }}</th>
                <th scope="col">{{ __('Payment Status') }}</th>
                <th scope="col">{{ __('Refund Status') }}</th>
                <th scope="col">{{ __('Booking Status') }}</th>
                <th scope="col">{{ __('Booking Source') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $booking)
                @php
                    $bookingAdjustment = App\Models\User\BookingAdjustment::where('booking_id', $booking->id)->first();

                    $cancellationEnabled = isset($cancellationPolicy) && $cancellationPolicy->room_booking_cancellation === 'active';
                    $policyRefundPercentage = $cancellationEnabled
                        ? (float) ($cancellationPolicy->cancellation_refund_percentage ?? 0)
                        : 0;
                    $timeLimitHours = max(0, (int) ($cancellationPolicy->cancellation_time_limit_hours ?? 0));

                    $bookingDateTime = \Carbon\Carbon::parse($booking->created_at, config('app.timezone'));
                    $hoursSinceBooking = $bookingDateTime->diffInHours(\Carbon\Carbon::now(config('app.timezone')));

                    $eligibleRefundPercentage = ($cancellationEnabled && $hoursSinceBooking <= $timeLimitHours)
                        ? max(0, min(100, $policyRefundPercentage))
                        : 0;

                    $refundableAmountOnReject = round((float) $booking->paid_amount, 2);
                    $autoRefundAmountOnReject = round(($refundableAmountOnReject * $eligibleRefundPercentage) / 100, 2);

                    $stayStatusLabel = match ($booking->stay_status) {
                        'Upcoming' => __('Upcoming'),
                        'checked-in' => __('Checked In'),
                        'checked-out' => __('Checked Out'),
                        default => ucfirst((string) $booking->stay_status),
                    };

                    $stayStatusBadge = match ($booking->stay_status) {
                        'Upcoming' => 'badge-info',
                        'checked-in' => 'badge-success',
                        'checked-out' => 'badge-danger',
                        default => 'badge-secondary',
                    };

                    $paymentStatusLabel = match ((int) $booking->payment_status) {
                        0 => __('Pending'),
                        1 => __('Success'),
                        2 => __('Partial'),
                        3 => __('Rejected'),
                        4 => __('Offline payment pending'),
                        default => __('Pending'),
                    };

                    $paymentStatusBadge = match ((int) $booking->payment_status) {
                        0 => 'badge-warning',
                        1 => 'badge-success',
                        2 => 'badge-primary',
                        3 => 'badge-danger',
                        4 => 'badge-warning',
                        default => 'badge-secondary',
                    };

                    $bookingStatusLabel = match ((int) $booking->booking_status) {
                        0 => __('Pending'),
                        1 => __('Confirmed'),
                        2 => __('Rejected'),
                        default => __('Pending'),
                    };

                    $bookingStatusBadge = match ((int) $booking->booking_status) {
                        0 => 'badge-warning',
                        1 => 'badge-success',
                        2 => 'badge-danger',
                        default => 'badge-secondary',
                    };
                @endphp
                <tr>
                    <td>
                        <input type="checkbox" class="bulk-check" data-val="{{ $booking->id }}">
                    </td>
                    <!-- booking number -->
                    <td>
                        <div>
                            {{ '#' . $booking->booking_number }}
                        </div>
                        <div>
                            {{ \Carbon\Carbon::parse($booking->created_at)->format('d M, Y h:i A') }}
                        </div>
                    </td>

                    <!-- check in - check out -->
                    <td>
                        <div>
                            {{ \Carbon\Carbon::parse($booking->arrival_date)->format('d M, Y') }}
                        </div>
                        <div>
                            {{ \Carbon\Carbon::parse($booking->departure_date)->format('d M, Y') }}
                        </div>
                    </td>

                    <!-- payment link -->
                    <td>
                        @if ($canPaymentLink)
                            @if ($booking->iyzico_payment_status == 1)
                                <span class="badge badge-warning">
                                    {{ __('Pending') }}
                                </span>
                            @else
                                @if ($booking->source === 'whatsapp_bot')
                                    @if (in_array($booking->payment_status, [0, 2, 3]))
                                        <button type="button" id="sendPaymentLinkBtn" data-id="{{ $booking->id }}"
                                            data-href="{{ route('tenant.room_bookings.send_payment_link') }}"
                                            class="btn btn-sm btn-primary">
                                            {{ $booking->send_payment_link == 1 ? __('Resend Payment Link') : __('Send Payment Link') }}
                                        </button>
                                        @if ($booking->send_payment_link == 1 && $booking->can_assign_room == 1)
                                            <p class="font-italic mb-0 badge badge-info text-white">
                                                {{ __('Room assigned, payment pending') }}
                                            </p>
                                        @endif
                                    @elseif($booking->payment_status == 4)
                                        <p class="font-italic mb-0 badge badge-warning text-white">
                                            {{ __('Offline payment pending') }}
                                        </p>
                                    @else
                                        <p class="font-italic mb-0 badge badge-success text-white">
                                            {{ __('Payment completed') }}
                                        </p>
                                    @endif
                                @else
                                    <p class="font-italic text-muted mb-0">
                                        {{ __('Booking via Dashboard') }}
                                    </p>
                                @endif
                            @endif
                        @else
                            @if ($booking->iyzico_payment_status == 1)
                                <span class="badge badge-warning">{{ __('Pending') }}</span>
                            @elseif ($booking->source === 'whatsapp_bot')
                                @if (in_array($booking->payment_status, [0, 2, 3]))
                                    <span class="badge badge-secondary">{{ __('No Access') }}</span>
                                @elseif($booking->payment_status == 4)
                                    <span class="badge badge-warning text-white">{{ __('Offline payment pending') }}</span>
                                @else
                                    <span class="badge badge-success">{{ __('Payment completed') }}</span>
                                @endif
                            @else
                                <span class="badge badge-secondary">{{ __('Booking via Dashboard') }}</span>
                            @endif
                        @endif
                    </td>

                    <!-- stay status -->
                    <td>
                        @if ($canStayStatus)
                            @if ($booking->booking_status == 2)
                                <span class="badge badge-danger">{{ __('Rejected') }}</span>
                            @else
                                @if ($booking->stay_status == 'checked-out')
                                    <span class="badge badge-danger">{{ __('Checked-Out') }}</span>
                                @else
                                    <form id="stayStatusForm{{ $booking->id }}" class="d-inline-block"
                                        action="{{ route('tenant.room_bookings.update_stay_status') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                        @php
                                            $statusClass = match ($booking->stay_status) {
                                                'Upcoming' => 'bg-info',
                                                'checked-out' => 'bg-danger',
                                                'checked-in' => 'bg-success',
                                                default => '',
                                            };
                                        @endphp

                                        <select class="form-control form-control-sm {{ $statusClass }}" name="stay_status"
                                            onchange="document.getElementById('stayStatusForm{{ $booking->id }}').submit();">
                                            @if ($booking->stay_status != 'checked-in')
                                                <option value="Upcoming"
                                                    {{ $booking->stay_status == 'Upcoming' ? 'selected' : '' }}>
                                                    {{ __('Upcoming') }}
                                                </option>
                                            @endif

                                            <option value="checked-in"
                                                {{ $booking->stay_status == 'checked-in' ? 'selected' : '' }}>
                                                {{ __('Checked In') }}
                                            </option>

                                            @if ($booking->stay_status != 'Upcoming')
                                                <option value="checked-out"
                                                    {{ $booking->stay_status == 'checked-out' ? 'selected' : '' }}>
                                                    {{ __('Checked Out') }}
                                                </option>
                                            @endif
                                        </select>
                                    </form>
                                @endif
                            @endif
                        @else
                            <span class="badge {{ $stayStatusBadge }}">{{ $stayStatusLabel }}</span>
                        @endif
                    </td>

                    <!-- payment status -->
                    <td>
                        @if ($canPaymentStatus)
                            @if ($booking->iyzico_payment_status == 1)
                                <span class="badge badge-warning">
                                    {{ __('Pending') }}
                                </span>
                            @else
                                @if ($booking->payment_status == 1 && $bookingAdjustment->type != 'extra_payment')
                                    <span class="badge badge-success">{{ __('Success') }}</span>
                                @elseif($booking->payment_status == 3)
                                    <span class="badge badge-danger">{{ __('Rejected') }}</span>
                                @else
                                    <form id="paymentStatusForm{{ $booking->id }}" class="d-inline-block"
                                        action="{{ route('tenant.room_bookings.update_payment_status') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                                        <select
                                            class="form-control form-control-sm
                                            @if ($booking->payment_status == 3) {{ 'bg-info' }}
                                             @elseif($booking->payment_status == 2)
                                            {{ 'bg-primary text-white' }}
                                            @else
                                            {{ 'bg-warning' }} @endif
                                            "
                                            name="payment_status"
                                            onchange="document.getElementById('paymentStatusForm{{ $booking->id }}').submit();">
                                            <option value="0" @selected($booking->payment_status == 0)>
                                                {{ __('Pending') }}
                                            </option>

                                            <option value="1" @selected($booking->payment_status == 1)>
                                                {{ __('Success') }}
                                            </option>
                                            <option value="2" @selected($booking->payment_status == 2)>
                                                {{ __('Partial') }}
                                            </option>

                                            <option value="3" @selected($booking->payment_status == 3)>
                                                {{ __('Rejected') }}
                                            </option>
                                        </select>
                                    </form>
                                @endif
                            @endif
                        @else
                            <span class="badge {{ $paymentStatusBadge }}">{{ $paymentStatusLabel }}</span>
                        @endif
                    </td>
                    <!-- refund status -->
                    <td>
                        @if ($canRefundStatus)
                            @if ($bookingAdjustment && $bookingAdjustment->type == 'refund')
                                <button class="btn btn-sm btn-danger mr-1 mb-1 editBtn"
                                    data-paying_amount="{{ $bookingAdjustment->amount }}"
                                    data-refund_context="adjustment_refund" data-booking_id="{{ $booking->id }}"
                                    data-toggle="modal" data-target="#editModal">
                                    <i class="fas fa-undo"></i> {{ __('Refund') }}
                                </button>
                            @else
                                {{ '-' }}
                            @endif
                        @else
                            <span class="badge badge-secondary">{{ __('No Access') }}</span>
                        @endif
                    </td>

                    <!-- booking status -->
                    <td>
                        @if ($canBookingStatus)
                            @php
                                $bookingStatus = $booking->booking_status;
                                $bookingStatusClass = match ($bookingStatus) {
                                    1 => 'bg-success',
                                    2 => 'bg-danger',
                                    default => 'bg-warning',
                                };
                            @endphp

                            <form id="bookingStatusForm{{ $booking->id }}" class="d-inline-block"
                                action="{{ route('tenant.room_bookings.update_booking_status') }}" method="post">
                                @csrf
                                <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                                <select class="form-control form-control-sm {{ $bookingStatusClass }}"
                                    name="booking_status"
                                    onchange="handleBookingStatusChange(this, {{ $booking->id }}, {{ number_format($refundableAmountOnReject, 2, '.', '') }}, {{ number_format($eligibleRefundPercentage, 2, '.', '') }}, {{ number_format($autoRefundAmountOnReject, 2, '.', '') }})">
                                    <option value="0" @selected($bookingStatus === 0)>
                                        {{ __('Pending') }}
                                    </option>
                                    <option value="1" @selected($bookingStatus === 1)>
                                        {{ __('Confirmed') }}
                                    </option>
                                    <option value="2" @selected($bookingStatus === 2)>
                                        {{ __('Rejected') }}
                                    </option>
                                </select>
                            </form>
                        @else
                            <span class="badge {{ $bookingStatusBadge }}">{{ $bookingStatusLabel }}</span>
                        @endif
                    </td>

                    <!-- booking source -->
                    <td>
                        @if ($booking->source === 'whatsapp_bot')
                            <span class="font-italic mb-0 badge badge-info">
                                <i class="fas fa-comments mr-1"></i>{{ __('Booking via WhatsApp') }}
                            </span>
                        @else
                            <span class="font-italic mb-0 badge badge-secondary">
                                <i class="fas fa-user mr-1"></i>{{ __('Booking via Website') }}
                            </span>
                        @endif
                    </td>

                    <!-- actions -->
                    <td>
                        @if ($canActionColumn)
                            @if (request()->input('unassigned') == 1)
                                @if ($canBookingEdit)
                                    <a href="{{ route('tenant.room_bookings.booking_edit', ['id' => $booking->id]) }}"
                                        class="btn btn-sm btn-primary mb-1">
                                        <i class="fas fa-tasks mr-1"></i> {{ __('Assign Room') }}
                                    </a>
                                @else
                                    <span class="badge badge-secondary">{{ __('No Access') }}</span>
                                @endif
                            @else
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                        id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        {{ __('Select') }}
                                    </button>

                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        @if ($canBookingDetails)
                                            <a href="{{ route('tenant.room_bookings.booking_details', ['id' => $booking->id]) }}"
                                                class="dropdown-item">
                                                {{ __('Details') }}
                                            </a>
                                        @endif

                                        @if ($canBookingEdit)
                                            <a href="{{ route('tenant.room_bookings.booking_edit', ['id' => $booking->id]) }}"
                                                class="dropdown-item">
                                                {{ __('Edit') }}
                                            </a>
                                        @endif

                                        @if (!empty($booking->attachment) && ($canBookingDetails || $canBookingEdit))
                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#attachmentModal{{ $booking->id }}">
                                                {{ __('Attachment') }}
                                            </a>
                                        @endif
                                        @if ($booking->invoice && ($canBookingDetails || $canBookingEdit))
                                            <a href="{{ asset('assets/tenant/invoices/rooms/' . $booking->invoice) }}"
                                                class="dropdown-item" target="_blank">
                                                {{ __('Invoice') }}
                                            </a>
                                        @endif

                                        @if ($canBookingSendMail)
                                            <a href="#" class="dropdown-item mailBtn" data-target="#mailModal"
                                                data-toggle="modal" data-customer_email="{{ $booking->customer_email }}">
                                                {{ __('Send Mail') }}
                                            </a>
                                        @endif

                                        @if ($canBookingWhatsapp)
                                            <a href="https://wa.me/{{ $booking->book_from_number }}" target="_blank"
                                                class="dropdown-item">
                                                {{ __('Message on WhatsApp') }}
                                            </a>
                                        @endif

                                        @if ($canBookingDelete)
                                            <form class="deleteForm d-block"
                                                action="{{ route('tenant.room_bookings.delete_booking', ['id' => $booking->id]) }}"
                                                method="post">
                                                @csrf
                                                <button type="submit" class="deleteBtn">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @else
                            <span class="badge badge-secondary">
                                <i class="fas fa-lock mr-1"></i>{{ __('Read Only') }}
                            </span>
                        @endif
                    </td>
                </tr>
                @includeIf('user.rooms.booking.show-attachment')
            @endforeach
        </tbody>
    </table>
</div>
