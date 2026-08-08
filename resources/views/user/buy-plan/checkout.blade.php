@extends('user.layout')

@section('content')
    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-block">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>{{ $message }}</strong>
        </div>
    @endif
    @if (!empty($membership) && ($membership->package->term == 'lifetime' || $membership->is_trial == 1))
        <div class="alert bg-warning alert-warning text-white text-center">
            <h3>{{ __('If you purchase this package') }} <strong class="text-dark">({{ $package->title }})</strong>,
                {{ __('then your current package') }} <strong class="text-dark">({{ $membership->package->title }}
                    @if ($membership->is_trial == 1)
                        <span class="badge badge-secondary">{{ __('Trial') }}</span>
                    @endif)
                </strong> {{ __('will be replaced immediately') }}</h3>
        </div>
    @endif
    <div class="row justify-content-center align-items-center mb-1">
        <div class="col-md-1 pl-md-0">
        </div>
        <div class="col-md-6 pl-md-0 pr-md-0">
            <div class="card card-pricing card-pricing-focus card-secondary">
                <form id="paymentForm" action="{{ route('user.plan.checkout') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $package->id }}">
                    <input type="hidden" name="user_id" value="{{ auth()->guard('web')->user()->id }}">
                    <input type="hidden" name="payment_method" id="payment" value="{{ old('payment_method') }}">
                    <div class="card-header">
                        <h4 class="card-title">{{ __($package->title) }}</h4>
                        <div class="card-price">
                            <span class="price">{{ $package->price == 0 ? 'Free' : format_price($package->price) }}</span>
                            <span class="text">/{{ __($package->term) }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="specification-list">
                            <li>
                                <span class="name-specification">{{ __('Membership') }}</span>
                                <span class="status-specification">{{ __('Yes') }}</span>
                            </li>
                            <li>
                                <span class="name-specification">{{ __('Start Date') }}</span>
                                @if (
                                    (!empty($previousPackage) && $previousPackage->term == 'lifetime') ||
                                        (!empty($membership) && $membership->is_trial == 1))
                                    <input type="hidden" name="start_date"
                                        value="{{ \Illuminate\Support\Carbon::yesterday()->format('d-m-Y') }}">
                                    <span
                                        class="status-specification">{{ \Illuminate\Support\Carbon::today()->format('d-m-Y') }}</span>
                                @else
                                    <input type="hidden" name="start_date"
                                        value="{{ \Illuminate\Support\Carbon::parse($membership->expire_date ?? \Carbon\Carbon::yesterday())->addDay()->format('d-m-Y') }}">
                                    <span
                                        class="status-specification">{{ \Illuminate\Support\Carbon::parse($membership->expire_date ?? \Carbon\Carbon::yesterday())->addDay()->format('d-m-Y') }}</span>
                                @endif
                            </li>
                            <li>
                                <span class="name-specification">{{ __('Expire Date') }}</span>
                                <span class="status-specification">
                                    @if ($package->term == 'monthly')
                                        @if (
                                            (!empty($previousPackage) && $previousPackage->term == 'lifetime') ||
                                                (!empty($membership) && $membership->is_trial == 1))
                                            {{ \Illuminate\Support\Carbon::parse(now())->addMonth()->format('d-m-Y') }}
                                            <input type="hidden" name="expire_date"
                                                value="{{ \Illuminate\Support\Carbon::parse(now())->addMonth()->format('d-m-Y') }}">
                                        @else
                                            {{ \Illuminate\Support\Carbon::parse($membership->expire_date ?? now())->addMonth()->format('d-m-Y') }}
                                            <input type="hidden" name="expire_date"
                                                value="{{ \Illuminate\Support\Carbon::parse($membership->expire_date ?? now())->addMonth()->format('d-m-Y') }}">
                                        @endif
                                    @elseif($package->term == 'lifetime')
                                        {{ __('Lifetime') }}
                                        <input type="hidden" name="expire_date"
                                            value="{{ \Illuminate\Support\Carbon::now()->addYears(999)->format('d-m-Y') }}">
                                    @else
                                        @if (
                                            (!empty($previousPackage) && $previousPackage->term == 'lifetime') ||
                                                (!empty($membership) && $membership->is_trial == 1))
                                            {{ \Illuminate\Support\Carbon::parse(now())->addYear()->format('d-m-Y') }}
                                            <input type="hidden" name="expire_date"
                                                value="{{ \Illuminate\Support\Carbon::parse(now())->addYear()->format('d-m-Y') }}">
                                        @else
                                            {{ \Illuminate\Support\Carbon::parse($membership->expire_date ?? now())->addYear()->format('d-m-Y') }}
                                            <input type="hidden" name="expire_date"
                                                value="{{ \Illuminate\Support\Carbon::parse($membership->expire_date ?? now())->addYear()->format('d-m-Y') }}">
                                        @endif
                                    @endif
                                </span>
                            </li>
                            <li>
                                <span class="name-specification">{{ __('Total Cost') }}</span>
                                <input type="hidden" name="price" value="{{ $package->price }}">
                                <span class="status-specification">
                                    {{ $package->price == 0 ? 'Free' : format_price($package->price) }}
                                </span>
                            </li>
                            @if ($package->price != 0)
                                <li>
                                    <div class="form-group px-0">
                                        <label class="text-white">{{ __('Payment Method') }}</label>
                                        <select name="payment_method" class="form-control nice-select input-solid"
                                            id="payment-gateway" required>
                                            <option value="" disabled selected>{{ __('Select a Payment Method') }}
                                            </option>
                                            @foreach ($onlineGateways as $onlineGateway)
                                                <option value="{{ $onlineGateway->keyword }}" data-gtype="online">
                                                    {{ $onlineGateway->keyword == 'myfatoorah' ?  $onlineGateway->name : __($onlineGateway->name) }}
                                                </option>
                                            @endforeach
                                            @foreach ($offlineGateways as $offlineGateway)
                                                <option value="{{ $offlineGateway->id }}" data-gtype="offline">
                                                    {{ $offlineGateway->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p id="errpayment_method" class="mb-0 text-danger em"></p>
                                               <p class="mb-0 text-danger" id="currency-error-message"></p>
                                    </div>
                                </li>
                            @endif


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
                                            <input class="form-control" type="text" id="anetCardCode"
                                                placeholder="Card Code" disabled />
                                        </div>
                                    </div>
                                    <input type="hidden" name="opaqueDataValue" id="opaqueDataValue" disabled />
                                    <input type="hidden" name="opaqueDataDescriptor" id="opaqueDataDescriptor"
                                        disabled />
                                    <ul id="anetErrors" class="dis-none"></ul>
                                </div>
                            </div>

                            <!-- offline gateway-->
                            <div>
                                <div id="instructions"></div>
                                <input type="hidden" name="is_receipt" value="0" id="is_receipt">
                            </div>

                            <!-- Iyzico payment will be inserted here -->
                            <div class="iyzico-element d-none">
                                <div class="form-group">
                                    <input type="text" name="identity_number" class="form-control"
                                        placeholder="{{ __('Identity Number') }}">
                                    <p id="erridentity_number" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="name" class="form-control"
                                        value="{{ @$user->first_name . ' ' . @$user->last_name }}"
                                        placeholder=" {{ __('Name') }}">
                                    <p id="errname" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="email" class="form-control"
                                        value="{{ $user->email }}" placeholder=" {{ __('Email') }}">
                                    <p id="erremail" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="address" value="{{ @$user->address }}"
                                        class="form-control" placeholder=" {{ __('Address') }}">
                                    <p id="erraddress" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="zip_code" class="form-control"
                                        placeholder=" {{ __('Zip Code') }}">
                                    <p id="errzip_code" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="country" value="{{ @$user->country }}"
                                        class="form-control" placeholder=" {{ __('Country') }}">
                                    <p id="errcountry" class="mb-0 text-danger em"></p>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="city" value="{{ @$user->city }}"
                                        class="form-control" placeholder=" {{ __('City') }}">
                                    <p id="errcity" class="mb-0 text-danger em"></p>
                                </div>
                            </div>
                        </ul>

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-light btn-block" id="paymentSubmitBtn" type="button">
                            <b>{{ __('Checkout Now') }}</b></button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-1 pr-md-0"></div>
    </div>
@endsection

@section('script')
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
