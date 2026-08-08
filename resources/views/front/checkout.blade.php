@extends('front.layout')
@section('pagename')
    - {{ __('Checkout') }}
@endsection

@section('meta-description', !empty($seo) ? $seo->checkout_meta_description : '')
@section('meta-keywords', !empty($seo) ? $seo->checkout_meta_keywords : '')

@section('breadcrumb-title', !empty($heading) ? $heading->checkout_title : __('Checkout'))
@section('breadcrumb-link', !empty($heading) ? $heading->checkout_title : __('Checkout'))

@section('content')
    @php
        use App\Services\TimzeZoneService;

        $timezone = TimzeZoneService::getAdminTimeZone();
        $today = \Carbon\Carbon::now($timezone)->startOfDay();
    @endphp

    <section class="checkout-area ptb-90">
        <div class="container">
            @if ($errors->has('username'))
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->get('username') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('front.membership.checkout') }}" method="POST" enctype="multipart/form-data"
                id="paymentForm">
                <div class="row">
                    <div class="col-lg-8 ">
                        <div class="billing_form form-block">
                            <div class="title mb-30">
                                <h3>{{ __('Billing Details') }}</h3>
                            </div>

                            @csrf

                            <div class="row">
                                <input type="hidden" name="username" value="{{ $username }}">
                                <input type="hidden" name="password" value="{{ $password }}">
                                <input type="hidden" name="package_type" value="{{ $status }}">
                                <input type="hidden" name="email" value="{{ $email }}">
                                <input type="hidden" name="package_id" value="{{ $id }}">
                                <input type="hidden" name="trial_days" id="trial_days" value="{{ $package->trial_days }}">

                                <input type="hidden" name="start_date"
                                    value="{{ $today->copy()->format('d-m-Y') }}">

                                @if ($status === 'trial')
                                    <input type="hidden" name="expire_date"
                                        value="{{ $today->copy()->addDays($package->trial_days)->format('d-m-Y') }}">
                                @else
                                    @if ($package->term === 'monthly')
                                        <input type="hidden" name="expire_date"
                                            value="{{ $today->copy()->addMonth()->format('d-m-Y') }}">
                                    @elseif($package->term === 'lifetime')
                                        <input type="hidden" name="expire_date"
                                            value="{{ $today->copy()->addYears(999)->format('d-m-Y') }}">
                                    @else
                                        <input type="hidden" name="expire_date"
                                            value="{{ $today->copy()->addYear()->format('d-m-Y') }}">
                                    @endif
                                @endif

                                <div class="col-lg-6">
                                    <div class="form-group mb-30">
                                        <label for="first_name">{{ __('First Name') }}**</label>
                                        <input id="first_name" type="text" class="form-control" name="first_name"
                                            placeholder="{{ __('First Name') }}" value="{{ old('first_name') }}">
                                        <p class="text-danger mb-0 em" id="errfirst_name"></p>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-30">
                                        <label for="last_name">{{ __('Last Name') }}**</label>
                                        <input id="last_name" type="text" class="form-control" name="last_name"
                                            placeholder="{{ __('Last Name') }}" value="{{ old('last_name') }}">
                                        <p class="text-danger mb-0 em" id="errlast_name"></p>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-30">
                                        <label for="phone">{{ __('Phone Number') }}**</label>
                                        <input id="phone" type="text" class="form-control" name="phone"
                                            placeholder="{{ __('Phone Number') }}" value="{{ old('phone') }}">
                                        <p class="text-danger mb-0 em" id="errphone"></p>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-30">
                                        <label for="email">{{ __('Email Address') }}**</label>
                                        <input id="email" type="email" class="form-control" name="email"
                                            value="{{ $email }}" disabled>
                                        <p class="text-danger mb-0 em" id="erremail"></p>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group mb-30">
                                        <label for="company_name">{{ __('Company Name') }}**</label>
                                        <input id="company_name" type="text" class="form-control" name="company_name"
                                            placeholder="{{ __('Company Name') }}" value="{{ old('company_name') }}">
                                        <p class="text-danger mb-0 em" id="errcompany_name"></p>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group mb-30">
                                        <label for="address">{{ __('Street Address') }} **</label>
                                        <input id="address" type="text" class="form-control" name="address"
                                            placeholder="{{ __('Street Address') }}" value="{{ old('address') }}">
                                        <p class="text-danger mb-0 em" id="erraddress"></p>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-30">
                                        <label for="city">{{ __('City') }} **</label>
                                        <input id="city" type="text" class="form-control" name="city"
                                            placeholder="{{ __('City') }}" value="{{ old('city') }}">
                                        <p class="text-danger mb-0 em" id="errcity"></p>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group mb-30">
                                        <label for="district">{{ __('State') }}</label>
                                        <input id="district" type="text" class="form-control" name="district"
                                            placeholder="{{ __('State') }}" value="{{ old('district') }}">
                                        <p class="text-danger mb-0 em" id="errdistrict"></p>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group mb-30">
                                        <label for="country">{{ __('Country') }}**</label>
                                        <input id="country" type="text" class="form-control" name="country"
                                            placeholder="{{ __('Country') }}" value="{{ old('country') }}">
                                        <p class="text-danger mb-0 em" id="errcountry"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="order_wrap_box mb-40">
                            <div id="couponReload">
                                <input type="hidden" name="price"
                                    value="{{ $status == 'trial' ? 0 : $package->price - $cAmount }}">

                                <div class="order-summery form-block mb-30 mt-30">
                                    <div class="title">
                                        <h3>{{ __('Package Summary') }}</h3>
                                    </div>

                                    <div class="order-list-info">
                                        <ul class="summery-list">
                                            <li>
                                                {{ __('Package') }}
                                                <span>{{ $package->title }} ({{ __(ucfirst($package->term)) }})</span>
                                            </li>

                                            <li>
                                                {{ __('Start Date') }}
                                                <span>{{ $today->copy()->format('d-m-Y') }}</span>
                                            </li>

                                            @if ($status === 'trial')
                                                <li>
                                                    {{ __('Expiry Date') }}
                                                    <span>{{ $today->copy()->addDays($package->trial_days)->format('d-m-Y') }}</span>
                                                </li>
                                            @else
                                                <li>
                                                    {{ __('Expiry Date') }}
                                                    <span>
                                                        @if ($package->term === 'monthly')
                                                            {{ $today->copy()->addMonth()->format('d-m-Y') }}
                                                        @elseif($package->term === 'lifetime')
                                                            {{ __('Lifetime') }}
                                                        @else
                                                            {{ $today->copy()->addYear()->format('d-m-Y') }}
                                                        @endif
                                                    </span>
                                                </li>
                                            @endif

                                            @if (session()->has('coupon'))
                                                <li>
                                                    <span>{{ __('Package Price') }}</span>
                                                    <span class="price">
                                                        @if ($status === 'trial')
                                                            {{ __('Free') }} ({{ $package->trial_days . ' days' }})
                                                        @elseif($package->price == 0)
                                                            {{ __('Free') }}
                                                        @else
                                                            {{ format_price($package->price) }}
                                                        @endif
                                                    </span>
                                                </li>

                                                <li>
                                                    <span>{{ __('Discount') }}</span>
                                                    <span class="price text-success">
                                                        - {{ format_price($cAmount) }}
                                                    </span>
                                                </li>
                                            @endif

                                            <li class="border-0">
                                                <span>{{ __('Total') }}</span>
                                                <span class="price">
                                                    @if ($status === 'trial')
                                                        {{ __('Free') }} ({{ $package->trial_days }} {{ __('days') }})
                                                    @elseif($package->price == 0)
                                                        {{ __('Free') }}
                                                    @else
                                                        {{ format_price($package->price - $cAmount) }}
                                                    @endif
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                @if ($package->price > 0 && $status != 'trial')
                                    @if (!session()->has('coupon'))
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="input-group checkout-coupon-group mb-3">
                                                    <input type="text" class="form-control coupon-input" name="coupon"
                                                        placeholder="{{ __('Enter Coupon Code Here') }}">
                                                    <button type="button"
                                                        class="btn primary-btn no-animation rounded-1 coupon-apply">
                                                        {{ __('Apply') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-success">
                                            {{ __('Coupon already applied') }}
                                        </div>
                                    @endif
                                @endif

                                @if ($package->price - $cAmount <= 0 || $status == 'trial')
                                    <div id="tab-stripe" class="dis-none gateway-details">
                                        <div class="row py-3">
                                            <div class="col-md-12">
                                                <div class="form-group mb-3">
                                                    <div id="stripe-element" class="mb-2"></div>
                                                </div>
                                                <p class="text-danger" id="stripe-errors"></p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="order-payment form-block">
                                        <div class="title">
                                            <h3>{{ __('Payment Method') }}</h3>
                                        </div>

                                        <div class="form-group mb-30">
                                            <select name="payment_method" id="payment-gateway"
                                                class="form-control nice-select olima_select">
                                                <option value="" selected disabled>{{ __('Choose an option') }}</option>

                                                @foreach ($onlineGateways as $onlineGateway)
                                                    <option value="{{ $onlineGateway->keyword }}" data-gtype="online">
                                                        {{ $onlineGateway->name }}
                                                    </option>
                                                @endforeach

                                                @foreach ($offlineGateways as $offlineGateway)
                                                    <option value="{{ $offlineGateway->id }}" data-gtype="offline">
                                                        {{ $offlineGateway->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <p class="text-danger mb-0 em" id="errpayment_method"></p>
                                            <p class="mb-0 text-danger" id="currency-error-message"></p>
                                        </div>

                                        <div id="tab-stripe" class="dis-none gateway-details">
                                            <div class="row py-3">
                                                <div class="col-md-12">
                                                    <div class="form-group mb-3">
                                                        <div id="stripe-element" class="mb-2"></div>
                                                    </div>
                                                    <p class="text-danger" id="stripe-errors"></p>
                                                </div>
                                            </div>
                                        </div>

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
                                                        <input class="form-control" type="text" id="anetCardCode"
                                                            placeholder="Card Code" disabled />
                                                    </div>
                                                </div>

                                                <input type="hidden" name="opaqueDataValue" id="opaqueDataValue" disabled />
                                                <input type="hidden" name="opaqueDataDescriptor" id="opaqueDataDescriptor" disabled />

                                                <ul id="anetErrors" class="dis-none"></ul>
                                            </div>
                                        </div>

                                        <div>
                                            <div id="instructions"></div>
                                            <input type="hidden" name="is_receipt" value="0" id="is_receipt">
                                        </div>

                                        <div class="iyzico-element d-none">
                                            <div class="form-group mb-3">
                                                <input type="text" name="identity_number" class="form-control"
                                                    placeholder="{{ __('Identity Number') }}">
                                                <p class="text-danger mb-0 em" id="erridentity_number"></p>
                                            </div>

                                            <div class="form-group">
                                                <input type="text" name="zip_code" class="form-control"
                                                    placeholder=" {{ __('Zip Code') }}">
                                                <p class="text-danger mb-0 em" id="errzip_code"></p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="text-center mt-4">
                                <button id="paymentSubmitBtn" class="btn primary-btn w-100 checkout-confirm-btn"
                                    type="button">
                                    {{ __('Confirm') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        "use strict";
        var couponRoute = "{{ route('front.membership.coupon') }}";
        var packageId = {{ $package->id }};
        const stripe_key = "{{ $stripe_key ?? '' }}";
        const ogateways = @php echo json_encode($offlineGateways) @endphp;
        const oinstructions = "{{ route('get_payment_instructions') }}";
        const clientKey = "{{ @$authorizeClientKey }}";
        const loginId = "{{ @$authorizeLoginId }}";
    </script>

    @if (!empty($stripe_key))
        <script src="https://js.stripe.com/v3/"></script>
    @endif

    <script type="text/javascript" src="{{ $anetSrc }}" charset="utf-8"></script>
    <script src="{{ asset('js/payment.js') }}"></script>
@endsection
