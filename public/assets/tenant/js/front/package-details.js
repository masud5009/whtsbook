var objOfData = { minimumFractionDigits: 2, maximumFractionDigits: 2 };

(function ($) {
  "use strict";
  package_fee = parseFloat(package_fee);
  package_tax = parseFloat(package_tax);
  // show or hide the attachment input field for offline payment gateway
  $('#payment-gateways').on('change', function () {
    // get the selected offline payment gateway id
    var gatewayId = $(this).val();
    if (gatewayId == 'authorize.net') {
      $("#tab-anet").removeClass('d-none');
      $("#tab-anet").addClass('d-block');
      $("#tab-anet input").removeAttr('disabled');
      $("#tab-stripe").removeClass('d-block');
      $("#tab-stripe").addClass('d-none');

    }
    else if (gatewayId == 'stripe') {
      $("#tab-stripe").removeClass('d-none');
      $("#tab-stripe").addClass('d-block');
      $("#tab-anet").removeClass('d-block');
      $("#tab-anet").addClass('d-none');

      $('#gateway-description').removeClass('d-block');
      $('#gateway-description').addClass('d-none');
      $('#gateway-instruction').removeClass('d-block');
      $('#gateway-instruction').addClass('d-none');
      $('#gateway-attachment').removeClass('d-block');
      $('#gateway-attachment').addClass('d-none');
    } else {
      $("#tab-stripe").removeClass('d-block');
      $("#tab-stripe").addClass('d-none');
      $("#tab-anet").removeClass('d-block');
      $("#tab-anet").addClass('d-none');

      // change string type to integer type
      gatewayId = parseInt(gatewayId);

      // loop to check which element's id match with selected offline payment's id
      for (var key in offlineGateways) {
        if (Object.hasOwnProperty.call(offlineGateways, key)) {
          var elementId = offlineGateways[key].id;

          if (elementId == gatewayId) {
            if (offlineGateways[key].is_receipt == 1) {
              $('#gateway-attachment').removeClass('d-none');
            } else {
              $('#gateway-attachment').addClass('d-none');
            }

            if (offlineGateways[key].short_description.length > 0) {
              $('#gateway-description').removeClass('d-none');
              $('#gateway-description').html(offlineGateways[key].short_description);
            } else {
              $('#gateway-description').addClass('d-none');
            }

            if (offlineGateways[key].instructions.length > 0) {
              $('#gateway-instruction').removeClass('d-none');
              $('#gateway-instruction').html(offlineGateways[key].instructions);
            } else {
              $('#gateway-instruction').addClass('d-none');
            }
            break;
          } else {
            $('#gateway-description').addClass('d-none');
            $('#gateway-instruction').addClass('d-none');
            $('#gateway-attachment').addClass('d-none');
          }
        }
      }
    }
  });


  // get the rating (star) value in integer
  $('.review-value li a').on('click', function () {
    var ratingValue = $(this).attr('data-ratingVal');

    // first, remove star color from all the 'review-value' class
    $('.review-value li a i').removeClass('text-warning');

    // second, add star color to the selected parent class
    var parentClass = 'review-' + ratingValue;
    $('.' + parentClass + ' li a i').addClass('text-warning');

    // finally, set the rating value to a hidden input field
    $('#ratingId').val(ratingValue);
  });


  if (pricingType == 'per-person') {
    $('input[name="visitors"]').on('input', function () {
      var visitors = $(this).val();
      var rent_price = visitors != "" ? parseInt(visitors) * parseFloat(initialPrice) : initialPrice ;
      remove_coupon();
      let package_rent_price = parseFloat(rent_price);
      let discount_price = 0.0;
      let newSubtotal = package_rent_price - parseFloat(discount_price);
      let calculatedTax = calculation_tax(newSubtotal, package_tax);
      let newGrandTotal = newSubtotal + calculatedTax + package_fee;
      showPriceFront(package_rent_price, newSubtotal, newGrandTotal, discount_price, package_fee, calculatedTax)
    });
  }

  $('#coupon-code').on('keypress', function (e) {
    let key = e.which;

    if (key == 13) {
      applyCoupon(e);
    }
  });
})(jQuery);

function applyCoupon(event) {
  event.preventDefault();

  let code = $('#coupon-code').val();
  let subtotal = $('#subtotal-amount').text();
  let id = $('#package-id').text();

  if (code) {
    let url = baseURL +'/'+ getUser.username + '/packages/user/package_booking/apply_coupon';

    let data = {
      coupon: code,
      initTotal: subtotal,
      packageId : id,
      _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    $.post(url, data, function (response) {
      if ('success' in response) {
        $('#coupon-code').val('');
        let package_rent_price = parseFloat(subtotal);
        let discount_price = response.discount;
        let newSubtotal = package_rent_price - parseFloat(discount_price);
        let calculatedTax = calculation_tax(newSubtotal, package_tax);
        let newGrandTotal = newSubtotal + calculatedTax + package_fee;
        showPriceFront(package_rent_price, newSubtotal, newGrandTotal, discount_price, package_fee, calculatedTax)

        toastr['success'](response.success);
      } else if ('error' in response) {
        toastr['error'](response.error);
      }
    });
  } else {
    alert('Please enter your coupon code.');
  }
}
function calculation_tax($amount, $per) {
  let tax = ($amount * $per) / 100;
  return tax;
}
function calculation_grand_total(newSubtotal, calculatedTax, package_fee) {
  let grandTotal = newSubtotal + calculatedTax + package_fee / 100;
  return grandTotal;
}
function showPriceFront(package_rent_price, subtotal, grandTotal, discount = 0, fee = 0, tax = 0) {

  grandTotal = parseFloat(grandTotal);
  subtotal = parseFloat(subtotal);
  discount = parseFloat(discount);
  fee = parseFloat(fee);
  tax = parseFloat(tax);
  package_rent_price = parseFloat(package_rent_price);

  sessionStorage.setItem('package_rent_amount', package_rent_price);
  sessionStorage.setItem('package_subtotal', subtotal);
  sessionStorage.setItem('package_grand_total', grandTotal);
  sessionStorage.setItem('package_discount', discount);
  sessionStorage.setItem('package_fee', fee);
  sessionStorage.setItem('package_tax', tax);


  $('#rent-price').text(package_rent_price.toFixed(2));
  $('#tax-amount').text(tax.toFixed(2));
  $('#fee-amount').text(fee.toFixed(2));
  $('#subtotal-amount').text(subtotal.toFixed(2));
  $('#discount-amount').text(discount.toFixed(2));
  $('#total-amount').text(grandTotal.toFixed(2));
}
function package_show_exiting_price() {
  let package_rent_amount = 0;
  let package_discount_amount = 0;
  let package_subtotal = 0;
  let package_fee_amount = 0;
  let package_tax_amount = 0;
  let package_grand_total_amount = 0;

  if (sessionStorage.getItem('package_rent_amount')) {
    package_rent_amount = sessionStorage.getItem('package_rent_amount');
  }
  if (sessionStorage.getItem('package_discount')) {
    package_discount_amount = sessionStorage.getItem('package_discount');
  }
  if (sessionStorage.getItem('package_subtotal')) {
    package_subtotal = sessionStorage.getItem('package_subtotal');
  }
  if (sessionStorage.getItem('package_fee')) {
    package_fee_amount = sessionStorage.getItem('package_fee');
  }
  if (sessionStorage.getItem('package_tax')) {
    package_tax_amount = sessionStorage.getItem('package_tax');
  }
  if (sessionStorage.getItem('package_grand_total')) {
    package_grand_total_amount = sessionStorage.getItem('package_grand_total');
  }

  showPriceFront(package_rent_amount, package_subtotal, package_grand_total_amount, package_discount_amount, package_fee_amount,
    package_tax_amount);
}
function remove_coupon() {
  let url = baseURL + '/' + getUser.username + '/packages/user/package_booking/remove_coupon';
  $.get(url);
}
