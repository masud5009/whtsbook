<div class="modal fade" id="buyCreditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Buy AI Credits') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form action="{{ route('user.buy-credit') }}" method="POST" id="paymentForm">
                    @csrf


                    <div class="form-group">
                        <label for="tokenamount">{{ __('Credits to Buy') }} <span class="text-danger">**</span></label>
                        <input type="number" min="0" class="form-control" name="tokenamount" id="tokenamount"
                            placeholder="e.g. 1000">
                        <p id="errtokenamount" class="mb-0 text-danger em"></p>
                        @php
                            $tokenPrice = $price_per_token;
                            if (bccomp($tokenPrice, number_format($tokenPrice, 2, '.', ''), 8) === 0) {
                                $formattedPrice = number_format($tokenPrice, 2, '.', '');
                            } else {
                                $formattedPrice = rtrim(rtrim($tokenPrice, '0'), '.');
                            }
                        @endphp

                        <span class="text-info">
                            1 {{ __('Token') }} = {{ $formattedPrice }} {{ $adminCurrency }}
                            ({{ ucfirst($current_ai_provider ?? 'gemini') }})
                        </span>

                    </div>

                    <div class="form-group">
                        <label for="tokenamount">{{ __('Payment Method') }} <span class="text-danger">**</span></label>
                        <select name="payment_method" id="payment-gateway" class="form-control">
                            <option value="" disabled selected>{{ __('Select a Payment Method') }}</option>
                            @foreach ($onlineGateways as $onlineGateway)
                                <option value="{{ $onlineGateway->keyword }}" data-gtype="online">
                                    {{ __($onlineGateway->name) }}
                                </option>
                            @endforeach
                            @foreach ($offlineGateways as $offlineGateway)
                                <option value="{{ $offlineGateway->id }}" data-gtype="offline">
                                    {{ __($offlineGateway->name) }}
                                </option>
                            @endforeach
                        </select>
                        <p id="errpayment_method" class="mb-0 text-danger em"></p>
                        <p class="mb-0 text-danger" id="currency-error-message"></p>
                    </div>

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
                    <div class="iyzico-element d-none">
                        <div class="form-group">
                            <input type="text" name="identity_number" class="form-control"
                                placeholder="{{ __('Identity Number') }}">
                        </div>
                        <div class="form-group">
                            <input type="text" name="name" class="form-control"
                                value="{{ @$user->first_name . ' ' . @$user->last_name }}"
                                placeholder=" {{ __('Name') }}">
                            <p id="errname" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <input type="text" name="email" class="form-control" value="{{ $user->email }}"
                                placeholder=" {{ __('Email') }}">
                            <p id="erremail" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <input type="text" name="address" value="{{ @$user->address }}" class="form-control"
                                placeholder=" {{ __('Address') }}">
                            <p id="erraddress" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <input type="text" name="zip_code" class="form-control"
                                placeholder=" {{ __('Zip Code') }}">
                            <p id="errzip_code" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <input type="text" name="country" value="{{ @$user->country }}" class="form-control"
                                placeholder=" {{ __('Country') }}">
                            <p id="errcountry" class="mb-0 text-danger em"></p>
                        </div>
                        <div class="form-group">
                            <input type="text" name="city" value="{{ @$user->city }}" class="form-control"
                                placeholder=" {{ __('City') }}">
                            <p id="errcity" class="mb-0 text-danger em"></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="button" class="btn btn-primary w-100"
                            id="paymentSubmitBtn">{{ __('Buy Now') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
