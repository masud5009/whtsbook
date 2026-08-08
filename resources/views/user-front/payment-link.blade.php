@extends('user-front.layout')
@php
    $keywords = json_decode($defaultLang->keywords, true);
@endphp
@section('pageTitle')
    {{ $userBs->website_title }} | {{ $keywords['Payment'] ?? 'Payment' }}
@endsection

@section('content')
    @include('user-front.heading', [
        'pageHeading' => $keywords['Review & Payment'] ?? 'Review & Payment',
        'booking_number' => $booking->booking_number,
        'booking_step' => 'payment_link',
    ])

    <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-7">
            <form id="paymentForm" action="{{ route('payment.process', ['id' => $booking->id]) }}" method="post"
                class="cardx p-4 p-lg-5">
                @csrf

                <h5 class="mb-3"> {{ $keywords['Guest Information'] ?? 'Guest Information' }}</h5>
                <div class="divider"></div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"> {{ $keywords['Full name'] ?? 'Full name' }}</label>
                        <input name="customer_name" class="form-control" value="{{ $booking->customer_name }}" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"> {{ $keywords['Phone'] ?? 'Phone' }}</label>
                        <input name="customer_phone" type="text" class="form-control"
                            value="{{ $booking->customer_phone }}" disabled>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label"> {{ $keywords['Email'] ?? 'Email' }}</label>
                        <input name="customer_email" type="email" class="form-control"
                            value="{{ $booking->customer_email }}" disabled>
                    </div>

                    <div class="col-md-12" id="paymentCouponWrap">
                        <label class="form-label">{{ $keywords['Enter Your Coupon'] ?? 'Enter Your Coupon' }}</label>
                        <div class="d-flex gap-2">
                            <input type="text" name="coupon" id="payment-coupon" class="form-control"
                                value="{{ $appliedCoupon['code'] ?? '' }}"
                                placeholder="{{ $keywords['Enter Your Coupon'] ?? 'Enter Your Coupon' }}">
                            <button type="button" class="btn btn-outline-success booking-coupon-apply">
                                {{ $keywords['Apply'] ?? 'Apply' }}
                            </button>
                        </div>
                        <p id="errcoupon" class="mb-0 mt-1 text-danger em"></p>
                        <p id="coupon-success-message" class="mb-0 mt-1 text-success"></p>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">
                            {{ $keywords['Payment Method'] ?? 'Payment Method' }} <span class="text-danger">**</span>
                        </label>

                        <select name="payment_method" class="form-control niceselect" id="payment-gateway">
                            <option disabled {{ old('payment_method') ? '' : 'selected' }}>
                                {{ $keywords['Select Payment Method'] ?? 'Select Payment Method' }}
                            </option>

                            @foreach ($onlineGateways as $onlineGateway)
                                <option value="{{ $onlineGateway->keyword }}" data-gtype="online"
                                    {{ old('payment_method') == $onlineGateway->keyword ? 'selected' : '' }}>
                                    {{ $keywords[$onlineGateway->name] ?? $onlineGateway->name }}
                                </option>
                            @endforeach

                            @foreach ($offlineGateways as $offlineGateway)
                                <option value="{{ $offlineGateway->id }}" data-gtype="offline"
                                    {{ old('payment_method') == $offlineGateway->id ? 'selected' : '' }}>
                                    {{ $keywords[$offlineGateway->name] ?? $offlineGateway->name }}
                                </option>
                            @endforeach
                        </select>
                        <p id="errpayment_method" class="mb-0 text-danger em"></p>
                        <p class="mb-0 text-danger" id="currency-error-message"></p>
                        <!-- Stripe-->
                        <div id="tab-stripe" class="dis-none gateway-details">
                            <div class="row py-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <div id="stripe-element" class="mb-2">
                                            <!-- A Stripe Element will be inserted here. -->
                                        </div>
                                    </div>
                                    <p class="text-danger" id="stripe-errors"></p>
                                </div>
                            </div>
                        </div>

                        <!-- authorize.net-->
                        <div class="dis-none gateway-details" id="tab-anet">
                            <div class="row py-3">
                                <div class="col-lg-6">
                                    <div class="form-group mb-3">
                                        <input class="form-control" type="text" id="anetCardNumber"
                                            placeholder="Card Number" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <div class="form-group">
                                        <input class="form-control" type="text" id="anetExpMonth"
                                            placeholder="Expire Month" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class="form-group">
                                        <input class="form-control" type="text" id="anetExpYear"
                                            placeholder="Expire Year" disabled />
                                    </div>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class="form-group">
                                        <input class="form-control" type="text" id="anetCardCode" placeholder="Card Code"
                                            disabled />
                                    </div>
                                </div>
                                <input type="hidden" name="opaqueDataValue" id="opaqueDataValue" disabled />
                                <input type="hidden" name="opaqueDataDescriptor" id="opaqueDataDescriptor" disabled />
                                <ul id="anetErrors" class="dis-none"></ul>
                            </div>
                        </div>

                        <!-- offline gateway-->
                        <div>
                            <div id="instructions"></div>
                            <input type="hidden" name="is_receipt" value="0" id="is_receipt">
                        </div>

                        <!-- Iyzico payment will be inserted here -->
                        <div class="iyzico-element {{ old('payment_method') == 'iyzico' ? '' : 'd-none' }} mt-3">
                            <div class="form-group mb-3">
                                <input type="text" name="identity_number" value="{{ old('identity_number') }}"
                                    class="form-control"
                                    placeholder="{{ $keywords['Identity Number'] ?? 'Identity Number' }}">
                                <p id="erridentity_number" class="mb-0 text-danger em"></p>
                            </div>

                            <div class="form-group mb-3">
                                <input type="text" name="address" value="{{ old('address') }}" class="form-control"
                                    placeholder="{{ $keywords['Address'] ?? 'Address' }}">
                                <p id="erraddress" class="mb-0 text-danger em"></p>
                            </div>

                            <div class="form-group mb-3">
                                <input type="text" name="zip_code" value="{{ old('zip_code') }}"
                                    class="form-control" placeholder="{{ $keywords['Zip Code'] ?? 'Zip Code' }}">
                                <p id="errzip_code" class="mb-0 text-danger em"></p>
                            </div>

                            <div class="form-group mb-3">
                                <input type="text" name="country" value="{{ old('country') }}" class="form-control"
                                    placeholder="{{ $keywords['Country'] ?? 'Country' }}">
                                <p id="errcountry" class="mb-0 text-danger em"></p>
                            </div>

                            <div class="form-group mb-3">
                                <input type="text" name="city" value="{{ old('city') }}" class="form-control"
                                    placeholder="{{ $keywords['City'] ?? 'City' }}">
                                <p id="errcity" class="mb-0 text-danger em"></p>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>

        <!-- Summary -->
        <div class="col-lg-5">
            <div class="summary-sticky">
                <div class="cardx p-4">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <h5 class="mb-1"> {{ $keywords['Booking Summary'] ?? 'Booking Summary' }}</h5>
                            <div class="subtle">
                                {{ $roomTitle }}
                            </div>
                        </div>
                        <span class="pill">{{ $interval->days }} {{ $keywords['Nights'] ?? 'Nights' }}</span>
                    </div>

                    <div class="divider"></div>

                    <div class="mini-card p-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="subtle">{{ $keywords['Arrival Date'] ?? 'Arrival Date' }}</div>
                            <div class="fw-semibold">
                                {{ \Carbon\Carbon::parse($booking->arrival_date)->format('F d, Y') }}
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div class="subtle">{{ $keywords['Departure Date'] ?? 'Departure Date' }}</div>
                            <div class="fw-semibold">
                                {{ \Carbon\Carbon::parse($booking->departure_date)->format('F d, Y') }}
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div class="subtle">{{ $keywords['Number Of Adult'] ?? 'Number Of Adult' }}</div>
                            <div class="fw-semibold">{{ $booking->adult }}</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div class="subtle">{{ $keywords['Number Of Child'] ?? 'Number Of Child' }}</div>
                            <div class="fw-semibold">{{ $booking->child }}</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <div class="fw-bold">{{ $keywords['Date'] ?? 'Date' }}</div>
                        <div class="fw-bold price">{{ $keywords['Room Numbers'] ?? 'Room Numbers' }}</div>
                    </div>

                    @foreach ($reserved_dates_info as $item)
                        <div class="d-flex justify-content-between">
                            <div class="subtle">{{ $item->formatted_date }}</div>
                            <div class="price">{{ $item->room_number }}</div>
                        </div>
                    @endforeach

                    <div class="divider"></div>

                    <div class="d-flex justify-content-between">
                        <div class="subtle">{{ $keywords['Total Rent'] ?? 'Total Rent' }}</div>
                        <div class="fw-semibold price">
                            {{ currencyTextPrice($booking->total_rent, $currency_text, $currency_text_position) }}
                        </div>
                    </div>

                    @if ($booking->discount > 0)
                        <div class="d-flex justify-content-between mt-2">
                            <div class="subtle">{{ $keywords['Discount'] ?? 'Discount' }}</div>
                            <div class="fw-semibold price">
                                {{ currencyTextPrice($booking->discount, $currency_text, $currency_text_position) }}
                            </div>
                        </div>
                    @endif

                    @if ($bs->room_tax_status == 1 || $booking->tax_amount > 0)
                        <div class="d-flex justify-content-between mt-2">
                            <div class="subtle">
                                {{ $keywords['Tax'] ?? 'Tax' }}({{ (int) $booking->tax_percentage . '%' }})
                            </div>
                            <div class="fw-semibold price">
                                {{ currencyTextPrice($booking->tax_amount, $currency_text, $currency_text_position) }}
                            </div>
                        </div>
                    @endif

                    @if ($bs->room_fee_status == 1 || $booking->room_fee > 0)
                        <div class="d-flex justify-content-between mt-2">
                            <div class="subtle">{{ $keywords['Fee'] ?? 'Fee' }}</div>
                            <div class="fw-semibold price">
                                {{ currencyTextPrice($booking->fee, $currency_text, $currency_text_position) }}
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mt-2">
                        <div class="subtle">{{ $keywords['Grand Total'] ?? 'Grand Total' }}</div>
                        <div class="fw-semibold price">
                            {{ currencyTextPrice($booking->grand_total, $currency_text, $currency_text_position) }}
                        </div>
                    </div>

                    @if ($booking->advance_payment_status == 2)
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
                        <div class="d-flex justify-content-between mt-2">
                            <div class="subtle">{{ $keywords['Advance'] ?? 'Advance' }}</div>
                            <div class="fw-semibold price">
                                {{ currencyTextPrice($booking->advance_amount, $currency_text, $currency_text_position) }}
                            </div>
                        </div>
                    @endif

                    @if ($booking->advance_payment_status == 1)
                        <div class="d-flex justify-content-between mt-2">
                            <div class="subtle">{{ $keywords['Due'] ?? 'Due' }}</div>
                            <div class="fw-semibold price">
                                @php
                                    $due = $booking->grand_total - $booking->advance_amount;
                                @endphp
                                {{ currencyTextPrice($due, $currency_text, $currency_text_position) }}
                            </div>
                        </div>
                    @endif

                    @php
                        $couponDiscount = (float) ($appliedCoupon['discount'] ?? 0);
                    @endphp
                    <div id="coupon-discount-row"
                        class="d-flex justify-content-between mt-2 {{ $couponDiscount > 0 ? '' : 'd-none' }}">
                        <div class="subtle">{{ $keywords['Coupon Discount'] ?? 'Coupon Discount' }}</div>
                        <div class="fw-semibold price" id="coupon-discount-amount">
                            {{ currencyTextPrice($couponDiscount, $currency_text, $currency_text_position) }}
                        </div>
                    </div>

                     @if (optional($bookingAdjustment)->type == 'extra_payment')
                        <div class="paid-amount-box mt-2 p-2">
                            <div class="d-flex justify-content-between">
                                <div>{{ $keywords['Paid Amount'] ?? 'Paid Amount' }}</div>
                                <div class="fw-semibold price">
                                    {{ currencyTextPrice(optional($bookingAdjustment)->grand_total, $currency_text, $currency_text_position) }}
                                </div>
                            </div>
                            <p class="mb-0 mt-1" style="font-size: 0.85rem; opacity: 0.85;">
                                {{ $keywords['An extra payment of this amount has already been received.'] ?? 'An extra payment of this amount has already been received.' }}
                            </p>
                        </div>
                    @endif

                    <div class="divider"></div>

                    <div class="d-flex justify-content-between">
                        <div class="fw-bold">{{ $keywords['Amount to Pay'] ?? 'Amount to Pay' }}</div>
                        @php
                            $baseAmountToPay = (float) $payableAmount;
                            $finalAmountToPay = max($baseAmountToPay - $couponDiscount, 0);
                        @endphp
                        <div class="fw-bold fs-4 price" id="amount-to-pay-value">
                            {{ currencyTextPrice($finalAmountToPay, $currency_text, $currency_text_position) }}
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="row g-2">
                        <div class="col-12 d-grid">
                            <button id="paymentSubmitBtn" class="btn btn-success w-100" type="button">
                                {{ $keywords['Pay Now'] ?? 'Pay Now' }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const __Processing__ = @json($keywords['Processing'] ?? 'Processing');
        const user_id = "{{ $booking->user_id }}";
        const paymentCouponRoute = "{{ route('payment.apply_coupon', ['id' => $booking->id]) }}";
        const couponApplySuccessText = @json($keywords['Coupon applied successfully'] ?? 'Coupon applied successfully');
        const amountToPayBase = Number("{{ (float) $payableAmount }}");
        const appliedCouponDiscount = Number("{{ (float) ($appliedCoupon['discount'] ?? 0) }}");
        const currencyText = @json($currency_text);
        const currencyTextPosition = @json($currency_text_position);
        const stripe_key = "{{ $stripe_key ?? '' }}";
        const ogateways = @php echo json_encode($offlineGateways) @endphp;
        const oinstructions = "{{ route('get_payment_instructions') }}";
        const clientKey = "{{ @$authorizeClientKey }}";
        const loginId = "{{ @$authorizeLoginId }}";
        $(function() {
            $(".niceselect").select2({
                width: "100%",
                placeholder: "{{ __('Select Payment Method') }}",
                minimumResultsForSearch: Infinity
            });
        });
    </script>
    @if (!empty($stripe_key))
        <script src="https://js.stripe.com/v3/"></script>
    @endif

    <script type="text/javascript" src="{{ $anetSrc }}" charset="utf-8"></script>
    <script src="{{ asset('js/payment.js') }}"></script>
@endsection
