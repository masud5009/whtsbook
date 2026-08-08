"use strict";

$("#paymentForm").attr('onsubmit', 'return false');
$('#error-message').html('');
const setBtnLoading = (isLoading) => {
    const $btn = $("#paymentSubmitBtn");

    if (isLoading) {
        $btn.data('oldText', $btn.text());
        $btn.prop('disabled', true).text(`${__Processing__}...`);
    } else {
        $btn.prop('disabled', false).text($btn.data('oldText') || 'Buy Now');
    }
};

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

/*================================= coupon apply ================================*/
const hasMembershipCouponConfig = typeof couponRoute !== 'undefined' && typeof packageId !== 'undefined';
const hasBookingCouponConfig = typeof paymentCouponRoute !== 'undefined';

const formatPaymentAmount = (amount) => {
    const value = Number(amount || 0).toFixed(2);

    if (typeof currencyText === 'undefined' || !currencyText) {
        return value;
    }

    if ((currencyTextPosition || '').toLowerCase() === 'right') {
        return `${value} ${currencyText}`;
    }

    return `${currencyText} ${value}`;
};

const applyMembershipCoupon = () => {
    $.ajax({
        url: couponRoute,
        type: 'POST',
        data: {
            coupon: $("input[name='coupon']").val(),
            package_id: packageId
        },
        success: res => {
            if (res === 'success') {
                $("#couponReload").load(location.href + " #couponReload", () => {
                    $('select').niceSelect();
                });
                toastr.success("Coupon applied successfully!");
            } else {
                toastr.warning(res);
            }
        }
    });
};

const applyBookingCoupon = () => {
    $("#errcoupon").text('');
    $("#coupon-success-message").text('');

    $.ajax({
        url: paymentCouponRoute,
        type: 'POST',
        data: {
            coupon: $("#payment-coupon").val()
        },
        success: (res) => {
            if (res?.status === 'success') {
                const discount = Number(res.discount || 0);
                const payable = Number(res.amount_to_pay || 0);

                $("#coupon-discount-row").removeClass('d-none');
                $("#coupon-discount-amount").text(formatPaymentAmount(discount));
                $("#amount-to-pay-value").text(formatPaymentAmount(payable));
                $("#coupon-success-message").text(res.message || couponApplySuccessText || 'Coupon applied successfully.');
                return;
            }

            $("#errcoupon").text(res?.message || 'Coupon could not be applied.');
        },
        error: (error) => {
            const responseJson = error?.responseJSON;
            const message = responseJson?.errors?.coupon?.[0] || responseJson?.message || 'Coupon could not be applied.';
            $("#errcoupon").text(message);
        }
    });
};

if (hasMembershipCouponConfig) {
    $("input[name='coupon']").on('keypress', e => {
        if (e.which === 13) {
            e.preventDefault();
            applyMembershipCoupon();
        }
    });

    $(".coupon-apply").on('click', applyMembershipCoupon);
}

if (hasBookingCouponConfig) {
    $("#payment-coupon").on('keypress', e => {
        if (e.which === 13) {
            e.preventDefault();
            applyBookingCoupon();
        }
    });

    $(".booking-coupon-apply").on('click', applyBookingCoupon);

    if (Number(appliedCouponDiscount || 0) > 0) {
        const payable = Number(amountToPayBase || 0) - Number(appliedCouponDiscount || 0);
        $("#coupon-discount-row").removeClass('d-none');
        $("#coupon-discount-amount").text(formatPaymentAmount(appliedCouponDiscount));
        $("#amount-to-pay-value").text(formatPaymentAmount(payable));
    }
}


/*================================= payment gateway ================================*/
function bootnotify(message, title, type) {
    var content = {};

    content.message = message;
    content.title = title;
    content.icon = 'fa fa-bell';

    $.notify(content, {
        type: type,
        placement: {
            from: 'top',
            align: 'right'
        },
        showProgressbar: true,
        time: 1000,
        allow_dismiss: true,
        delay: 4000
    });
}
$('body').on('change', "#payment-gateway", function () {
    let offline = ogateways;
    let data = [];
    offline.map(({
        id,
        name
    }) => {
        data.push(name);
    });
    let paymentMethod = $("#payment-gateway").val();
    $("input[name='payment_method']").val(paymentMethod);

    $(".gateway-details").hide();
    $(".gateway-details input").attr('disabled', true);
    $('.iyzico-element').addClass('d-none');

    if (paymentMethod == 'stripe') {
        $("#tab-stripe").show();
    }
    if (paymentMethod == 'iyzico') {
        $('.iyzico-element').removeClass('d-none');
    }

    if (paymentMethod == 'authorize.net') {
        $("#tab-anet").show();
        $("#tab-anet input").removeAttr('disabled');
    }

    let gateway_type = $("#payment-gateway option:selected").data('gtype');
    if (gateway_type == 'offline') {
        let formData = new FormData();
        formData.append('name', paymentMethod);
        if (typeof user_id != 'undefined') {
            formData.append('user_id', user_id);
        }
        $.ajax({
            url: oinstructions,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            contentType: false,
            processData: false,
            cache: false,
            data: formData,
            success: function (data) {
                let instruction = $("#instructions");
                let instructions =
                    `<div class="gateway-desc">${data.instructions}</div>`;
                if (data.description != null) {
                    var description =
                        `<div class="gateway-desc"><p>${data.description}</p></div>`;
                } else {
                    var description = `<div></div>`;
                }
                let receipt = `<div class="form-element mb-2">
                                      <label>Receipt<span>*</span></label><br>
                                      <input type="file" name="receipt" value="" class="file-input" required>
                                      <p class="mb-0 text-warning">** Receipt image must be .jpg / .jpeg / .png</p>
                                   </div>`;
                if (data.is_receipt == 1) {
                    $("#is_receipt").val(1);
                    let finalInstruction = instructions + description + receipt;
                    instruction.html(finalInstruction);
                } else {
                    $("#is_receipt").val(0);
                    let finalInstruction = instructions + description;
                    instruction.html(finalInstruction);
                }
                $('#instructions').fadeIn();
            },
            error: function (data) { }
        })
    } else {
        $('#instructions').fadeOut();
    }
});

//stripe start
if (stripe_key != '') {
    var stripe = Stripe(stripe_key);

    // Create a Stripe Element for the card field
    var elements = stripe.elements();
    var cardElement = elements.create('card', {
        hidePostalCode: true,
        style: {
            base: {
                iconColor: '#454545',
                color: '#454545',
                fontWeight: '500',
                lineHeight: '50px',
                fontSmoothing: 'antialiased',
                backgroundColor: '#f2f2f2',
                ':-webkit-autofill': {
                    color: '#454545',
                },
                '::placeholder': {
                    color: '#454545',
                },
            }
        },
    });

    // Add an instance of the card Element into the `card-element` div
    cardElement.mount('#stripe-element');
}
//stripe end

function setHiddenInput(form, name, value) {
    let input = form.querySelector(`input[name="${name}"]`);
    if (!input) {
        input = document.createElement('input');
        input.setAttribute('type', 'hidden');
        input.setAttribute('name', name);
        form.appendChild(input);
    }
    input.value = value;
}

// Send the token to your server
function stripeTokenHandler(token) {
    var form = document.getElementById('paymentForm');
    setHiddenInput(form, 'stripeToken', token.id);
    submitPaymentForm();
}

function sendPaymentDataToAnet() {
    // Set up authorisation to access the gateway.
    var authData = {};
    authData.clientKey = clientKey;
    authData.apiLoginID = loginId;

    var cardData = {};
    cardData.cardNumber = document.getElementById("anetCardNumber").value;
    cardData.month = document.getElementById("anetExpMonth").value;
    cardData.year = document.getElementById("anetExpYear").value;
    cardData.cardCode = document.getElementById("anetCardCode").value;

    // Now send the card data to the gateway for tokenisation.
    // The responseHandler function will handle the response.
    var secureData = {};
    secureData.authData = authData;
    secureData.cardData = cardData;
    Accept.dispatchData(secureData, responseHandler);
}

function responseHandler(response) {
    if (response.messages.resultCode === "Error") {
        var i = 0;
        let errorLists = ``;
        while (i < response.messages.message.length) {
            errorLists += `<li class="text-danger">${response.messages.message[i].text}</li>`;

            i = i + 1;
        }
        $("#anetErrors").show();
        $("#anetErrors").html(errorLists);
        $("#paymentSubmitBtn").attr('disabled', false);
        $("#paymentSubmitBtn").text('Buy Now');
    } else {
        paymentFormUpdate(response.opaqueData);
    }
}

function paymentFormUpdate(opaqueData) {
    document.getElementById("opaqueDataDescriptor").value = opaqueData.dataDescriptor;
    document.getElementById("opaqueDataValue").value = opaqueData.dataValue;
    document.getElementById("opaqueDataDescriptor").removeAttribute('disabled');
    document.getElementById("opaqueDataValue").removeAttribute('disabled');
    submitPaymentForm();
}

function submitPaymentForm() {
    let paymentForm = document.getElementById('paymentForm');
    let fd = new FormData(paymentForm);
    let url = $("#paymentForm").attr('action');
    let method = $("#paymentForm").attr('method');

    $.ajax({
        url: url,
        method: method,
        data: fd,
        contentType: false,
        processData: false,
        success: function (data) {
            setBtnLoading(false);

            $(".em").each(function () {
                $(this).html('');
            });

            // currency error
            if (data.status == 'currency-error') {
                $('#currency-error-message').text(data.message);
                return;
            }

            if (data.status === 'success') {
                // redirect type
                if (data.action === 'redirect' && data.url) {
                    window.location.href = data.url;
                    return;
                }

                // html type
                if (data.action === 'html' && data.html) {
                    document.open();
                    document.write(data.html);
                    document.close();
                    return;
                }
            }

            // if error occurs
            else if (typeof data.error != 'undefined') {
                for (let x in data) {
                    if (x == 'error') {
                        continue;
                    }
                    document.getElementById('err' + x).innerHTML = data[x][0];
                    markValidFields(x);
                }
            } else if (data?.errors?.error) {
                const errors = data?.errors;
                Object.keys(errors).map(function (key) {
                    if (key !== 'error')
                        document.getElementById('err' + key).innerHTML = errors[key][0];
                });
            }
        },
        error: function (error) {
            setBtnLoading(false);

            $(".em").each(function () {
                $(this).html('');
            })

            // Clear previous validation styles
            $('.form-control').removeClass('valid-field invalid-field');

            const responseJson = error?.responseJSON;
            if (responseJson?.errors) {
                for (let x in responseJson.errors) {
                    document.getElementById('err' + x).innerHTML = responseJson.errors[x][0];
                    markValidFields(x);
                }
                if (responseJson?.errors?.error) {
                    bootnotify(responseJson.errors.error[0], "Warning", "warning");
                }
                return;
            }

            if (responseJson?.exception) {
                bootnotify(responseJson.exception, "Warning", "warning");
                return;
            }
            bootnotify('Payment request failed. Please try again.', "Warning", "warning");
        }
    });
}

$("#paymentSubmitBtn").on('click', function (e) {
    setBtnLoading(true);

    // Clear previous validation styles
    $('.form-control').removeClass('valid-field invalid-field');
    $('.form-control').css('border', '');

    let val = $("#payment-gateway").val();

    if (val == 'authorize.net') {
        sendPaymentDataToAnet();
        return;
    } else if (val == 'stripe') {
        if (typeof stripe == 'undefined') {
            setBtnLoading(false);
            bootnotify('Stripe is not configured.', "Warning", "warning");
            return;
        }

        stripe.createToken(cardElement).then(function (result) {
            if (result.error) {
                setBtnLoading(false);
                document.getElementById('stripe-errors').textContent = result.error.message;
            } else {
                stripeTokenHandler(result.token);
            }
        });
        return;
    }

    submitPaymentForm();
});


function markValidFields(field_name) {
    let $field = $('[name="' + field_name + '"]');
    if ($field.length) {
        $field.addClass('invalid-field').removeClass('valid-field');
    }
}
