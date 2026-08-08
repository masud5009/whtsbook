@extends('user-front.layout')

@section('pageTitle')
    {{ $userBs->website_title }} | {{ $keywords['Booking Details'] ?? 'Booking Details' }}
@endsection

@section('content')
    @php
        $keywords = json_decode($defaultLang->keywords, true);
        // payment_status: 0=unpaid,1=full paid,2=partial paid,3=cancelled
        $paymentMap = [
            0 => [
                'label' => $keywords['Unpaid'] ?? 'Unpaid',
                'class' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            ],
            1 => [
                'label' => $keywords['Paid'] ?? 'Paid',
                'class' => 'badge bg-success-subtle text-success border border-success-subtle',
            ],
            2 => [
                'label' => $keywords['Partial Paid'] ?? 'Partial Paid',
                'class' => 'badge bg-warning-subtle text-warning border border-warning-subtle',
            ],
            3 => [
                'label' => $keywords['Cancelled'] ?? 'Cancelled',
                'class' => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
            ],
        ];

        // booking_status: 0=pending,1=confirmed,2=rejected
        $bookingMap = [
            0 => [
                'label' => $keywords['Pending'] ?? 'Pending',
                'class' => 'badge bg-warning-subtle text-warning border border-warning-subtle',
            ],
            1 => [
                'label' => $keywords['Confirmed'] ?? 'Confirmed',
                'class' => 'badge bg-success-subtle text-success border border-success-subtle',
            ],
            2 => [
                'label' => $keywords['Rejected'] ?? 'Rejected',
                'class' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            ],
        ];

        $p = $paymentMap[$booking->payment_status] ?? [
            'label' => $keywords['Unknown'] ?? 'Unknown',
            'class' => 'badge bg-light text-dark border',
        ];

        $b = $bookingMap[$booking->booking_status] ?? [
            'label' => $keywords['Unknown'] ?? 'Unknown',
            'class' => 'badge bg-light text-dark border',
        ];
    @endphp

    <div class="col-lg-12 booking-details-page">
        <div class="summary-sticky">
            <div class="cardx p-4">
                <div class="d-flex align-items-start justify-content-between gap-3 booking-header flex-wrap">
                    <div>
                        <h5 class="mb-1 booking-page-title">
                            {{ $keywords['Booking Details'] ?? 'Booking Details' }}
                        </h5>
                        <div class="subtle booking-room-title">
                            {{ $roomTitle }}
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap header-actions">
                        <span class="pill">
                            {{ $interval->days }} {{ $keywords['Nights'] ?? 'Nights' }}
                        </span>

                        @if (!empty($invoiceDownloadUrl))
                            <a href="{{ $invoiceDownloadUrl }}" class="btn btn-outline-danger btn-sm invoice-top-btn"
                                download="{{ $booking->booking_number }}-invoice.pdf">
                                <i
                                    class="fa-solid fa-download me-1"></i>{{ $keywords['Download Invoice'] ?? 'Download Invoice' }}
                            </a>
                        @endif
                    </div>
                </div>

                <div class="divider"></div>

                <div class="row">
                    <!-- Booking Details -->
                    <div class="col-lg-6">
                        <div class="mini-card p-3 mb-3">
                            <div class="section-heading">
                                <div class="fw-bold">
                                    {{ $keywords['Booking Details'] ?? 'Booking Details' }}
                                </div>
                            </div>

                            <div class="d-flex justify-content-between py-1 info-row">
                                <div class="subtle label-text">{{ $keywords['Booking Number'] ?? 'Booking Number' }}</div>
                                <div class="fw-semibold value-text">#{{ $booking->booking_number }}</div>
                            </div>

                            <div class="d-flex justify-content-between py-1 info-row">
                                <div class="subtle label-text">{{ $keywords['Name'] ?? 'Name' }}</div>
                                <div class="fw-semibold value-text">{{ $booking->customer_name }}</div>
                            </div>

                            <div class="d-flex justify-content-between py-1 info-row">
                                <div class="subtle label-text">{{ $keywords['Email'] ?? 'Email' }}</div>
                                <div class="fw-semibold value-text">{{ $booking->customer_email }}</div>
                            </div>

                            <div class="d-flex justify-content-between py-1 info-row">
                                <div class="subtle label-text">{{ $keywords['Phone'] ?? 'Phone' }}</div>
                                <div class="fw-semibold value-text">{{ $booking->customer_phone }}</div>
                            </div>

                            <div class="d-flex justify-content-between py-1 info-row">
                                <div class="subtle label-text">{{ $keywords['Arrival Date'] ?? 'Arrival Date' }}</div>
                                <div class="fw-semibold value-text">
                                    {{ \Carbon\Carbon::parse($booking->arrival_date)->format('F d, Y') }}
                                </div>
                            </div>

                            <div class="d-flex justify-content-between py-1 info-row">
                                <div class="subtle label-text">{{ $keywords['Departure Date'] ?? 'Departure Date' }}</div>
                                <div class="fw-semibold value-text">
                                    {{ \Carbon\Carbon::parse($booking->departure_date)->format('F d, Y') }}
                                </div>
                            </div>

                            <div class="d-flex justify-content-between py-1 info-row">
                                <div class="subtle label-text">{{ $keywords['Number Of Adult'] ?? 'Number Of Adult' }}
                                </div>
                                <div class="fw-semibold value-text">{{ $booking->adult }}</div>
                            </div>

                            <div class="d-flex justify-content-between py-1 info-row">
                                <div class="subtle label-text">{{ $keywords['Number Of Child'] ?? 'Number Of Child' }}
                                </div>
                                <div class="fw-semibold value-text">{{ $booking->child }}</div>
                            </div>

                            <div class="d-flex justify-content-between py-1 info-row">
                                <div class="subtle label-text">{{ $keywords['Payment Status'] ?? 'Payment Status' }}</div>
                                <div class="fw-semibold value-text">
                                    <span class="{{ $p['class'] }} px-3 py-2 rounded-pill status-badge">
                                        {{ $p['label'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between py-1 info-row">
                                <div class="subtle label-text">{{ $keywords['Booking Status'] ?? 'Booking Status' }}</div>
                                <div class="fw-semibold value-text">
                                    <span class="{{ $b['class'] }} px-3 py-2 rounded-pill status-badge">
                                        {{ $b['label'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Details -->
                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mini-card p-3 mb-3">
                                    <div class="section-heading">
                                        {{ $keywords['Reserved Dates'] ?? 'Reserved Dates' }}
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table booking-status-table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>{{ $keywords['Date'] ?? 'Date' }}</th>
                                                    <th class="text-end">{{ $keywords['Room Numbers'] ?? 'Room Numbers' }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($reserved_dates_info as $item)
                                                    <tr>
                                                        <td class="subtle">{{ $item->formatted_date }}</td>
                                                        <td class="fw-semibold price text-end">{{ $item->room_number }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="subtle text-center">
                                                            {{ $keywords['No Data Found'] ?? 'No Data Found' }}
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="mini-card p-3">
                                    <div class="section-heading">
                                        <div class="fw-bold">{{ $keywords['Billing Details'] ?? 'Billing Details' }}</div>
                                    </div>

                                    <div class="d-flex justify-content-between py-1 bill-row">
                                        <div class="subtle label-text">{{ $keywords['Total Rent'] ?? 'Total Rent' }}</div>
                                        <div class="fw-semibold price value-text">
                                            {{ currencyTextPrice($booking->total_rent, $currency_text, $currency_text_position) }}
                                        </div>
                                    </div>

                                    @if ($booking->discount > 0)
                                        <div class="d-flex justify-content-between py-1 bill-row">
                                            <div class="subtle label-text">{{ $keywords['Discount'] ?? 'Discount' }}</div>
                                            <div class="fw-semibold price value-text">
                                                {{ currencyTextPrice($booking->discount, $currency_text, $currency_text_position) }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($userBs->room_tax_status == 1 || $booking->tax_amount > 0)
                                        <div class="d-flex justify-content-between py-1 bill-row">
                                            <div class="subtle label-text">
                                                {{ $keywords['Tax'] ?? 'Tax' }}({{ (int) $booking->tax_percentage . '%' }})
                                            </div>
                                            <div class="fw-semibold price value-text">
                                                {{ currencyTextPrice($booking->tax_amount, $currency_text, $currency_text_position) }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($userBs->room_fee_status == 1 || $booking->fee > 0)
                                        <div class="d-flex justify-content-between mt-2 bill-row">
                                            <div class="subtle label-text">{{ $keywords['Fee'] ?? 'Fee' }}</div>
                                            <div class="fw-semibold price value-text">
                                                {{ currencyTextPrice($booking->fee, $currency_text, $currency_text_position) }}
                                            </div>
                                        </div>
                                    @endif

                                    <div class="d-flex justify-content-between mt-2">
                                        <div class="subtle label-text">{{ $keywords['Grand Total'] ?? 'Grand Total' }}
                                        </div>
                                        <div class="fw-semibold price value-text">
                                            {{ currencyTextPrice($booking->grand_total, $currency_text, $currency_text_position) }}
                                        </div>
                                    </div>
                                    {{-- <div class="d-flex justify-content-between mt-2 grand-total-row">
                                        <div class="subtle label-text">{{ $keywords['Grand Total'] ?? 'Grand Total' }}
                                        </div>
                                        <div class="fw-semibold price value-text">
                                            {{ currencyTextPrice($booking->grand_total, $currency_text, $currency_text_position) }}
                                        </div>
                                    </div> --}}

                                    @if ($booking->advance_payment_status == 2  && in_array($bookingAdjustment->type, ['refund,extra_payment']))
                                        <div class="paid-amount-box mt-2 p-2">
                                            <div class="d-flex justify-content-between">
                                                <div>{{ $keywords['Paid Amount'] ?? 'Paid Amount' }}</div>
                                                <div class="fw-semibold price">
                                                    {{ currencyTextPrice($booking->paid_amount, $currency_text, $currency_text_position) }}
                                                </div>
                                            </div>
                                            <p class="mb-0 mt-1" style="font-size: 0.85rem; opacity: 0.85;">
                                                {{ __('An advance payment of this amount has already been received.') }}
                                            </p>
                                        </div>
                                    @endif

                                    @if ($booking->advance_amount > 0)
                                        <div class="d-flex justify-content-between mt-2 bill-row">
                                            <div class="subtle label-text">{{ $keywords['Advance'] ?? 'Advance' }}</div>
                                            <div class="fw-semibold price value-text">
                                                {{ currencyTextPrice($booking->advance_amount, $currency_text, $currency_text_position) }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($booking->advance_payment_status == 1)
                                        <div class="d-flex justify-content-between mt-2 bill-row">
                                            <div class="subtle label-text">{{ $keywords['Due'] ?? 'Due' }}</div>
                                            <div class="fw-semibold price value-text">
                                                @php
                                                    $due = $booking->grand_total - $booking->advance_amount;
                                                @endphp
                                                {{ currencyTextPrice($due, $currency_text, $currency_text_position) }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($bookingAdjustment->type == 'refund')
                                        <div class="d-flex justify-content-between mt-2 bill-row">
                                            <div class="subtle label-text">{{ $keywords['Paid Amount'] ?? 'Paid Amount' }}
                                            </div>
                                            <div class="fw-semibold price value-text">
                                                {{ currencyTextPrice($bookingAdjustment->grand_total, $currency_text, $currency_text_position) }}
                                            </div>
                                        </div>


                                        <div class="paid-amount-box mt-2 p-2">
                                            <div class="d-flex justify-content-between">
                                                <div>{{ $keywords['Refund Amount'] ?? 'Refund Amount' }}</div>
                                                <div class="fw-semibold price">
                                                    {{ currencyTextPrice($bookingAdjustment->amount, $currency_text, $currency_text_position) }}
                                                </div>
                                            </div>
                                            <p class="mb-0 mt-1" style="font-size: 0.85rem; opacity: 0.85;">
                                                {{ $keywords['This amount will be refunded.'] ?? 'This amount will be refunded.' }}
                                            </p>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($refunds->isNotEmpty())
                        <div class="col-12">
                            <div class="divider"></div>
                        </div>

                        <div class="col-lg-12">
                            <div class="mini-card p-3 mb-3">
                                <div class="fw-bold mb-2 section-heading">
                                    {{ $keywords['Refund History'] ?? 'Refund History' }}
                                </div>

                                <div class="table-responsive">
                                    <table class="table booking-status-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>{{ $keywords['Date'] ?? 'Date' }}</th>
                                                <th class="text-end">{{ $keywords['Paying Amount'] ?? 'Paying Amount' }}
                                                </th>
                                                <th class="text-end">{{ $keywords['Refund Amount'] ?? 'Refund Amount' }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($refunds as $refund)
                                                <tr>
                                                    <td class="fw-semibold price">
                                                        {{ \Carbon\Carbon::parse($refund->created_at)->format('F d, Y') }}
                                                    </td>
                                                    <td class="subtle text-end">
                                                        {{ currencySymbolPrice($refund->paying_amount, $refund->currency_symbol, $refund->currency_symbol_position) }}
                                                    </td>
                                                    <td class="fw-semibold price text-end">
                                                        {{ currencySymbolPrice($refund->refund_amount, $refund->currency_symbol, $refund->currency_symbol_position) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
