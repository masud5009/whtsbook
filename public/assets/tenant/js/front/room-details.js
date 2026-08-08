var objOfData = { minimumFractionDigits: 2, maximumFractionDigits: 2 };

(function ($) {
  'use strict';
  room_fee = parseFloat(room_fee);
  room_tax = parseFloat(room_tax);
  var dateArray = bookingDates;

  // initialize date range picker
  $('#date-range').daterangepicker({
    opens: 'left',
    autoUpdateInput: false,
    locale: {
      format: 'YYYY-MM-DD'
    },
    isInvalidDate: function (date) {
      for (let index = 0; index < dateArray.length; index++) {
        if (date.format('YYYY-MM-DD') == dateArray[index]) {
          return true;
        }
      }
    }
  });


  // show the dates and number of nights in input field when user select a date range
  $('#date-range').on('apply.daterangepicker', function (event, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));

    // get the difference of two dates, date should be in 'YYYY-MM-DD' format
    var dates = $(this).val();

    // first, slice the string and get the arrival_date & departure_date
    var arrOfDate = dates.split(' ');
    var arrival_date = arrOfDate[0];
    var departure_date = arrOfDate[2];

    // parse the strings into date using Date constructor
    arrival_date = new Date(arrival_date);
    departure_date = new Date(departure_date);

    // get the time difference (in millisecond) of two dates
    var difference_in_time = departure_date.getTime() - arrival_date.getTime();

    // finally, get the night difference of two dates (convert time to night)
    var difference_in_night = difference_in_time / (1000 * 60 * 60 * 24);

    $('#night').val(difference_in_night);
    // calculate room rent
    var totalRent = difference_in_night * parseFloat(roomRentPerNight);

    let room_rent_price = parseFloat(totalRent);
    let discount_price = 0;
    let newSubtotal = parseFloat(totalRent) - parseFloat(discount_price);
    let calculatedTax = calculation_tax(newSubtotal, room_tax);
    let newGrandTotal = newSubtotal + calculatedTax + room_fee;
    //remove coupon change data picker
    remove_coupon();
    showPriceFront(room_rent_price, newSubtotal, newGrandTotal, discount_price, room_fee, calculatedTax);
  });

  // remove the dates and number of nights when user click on cancel button
  $('#date-range').on('cancel.daterangepicker', function (event, picker) {
    $(this).val('');
    $('#night').val('');
    let room_rent_price = 0
    let discount_price = 0;
    let newSubtotal = 0;
    let calculatedTax = 0;
    let newGrandTotal = 0;
    room_fee = 0;
    //remove coupon change data picker
    remove_coupon();
    showPriceFront(room_rent_price, newSubtotal, newGrandTotal, discount_price, room_fee, calculatedTax);
  });


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

            if (offlineGateways[key].is_receipt == 1) {
              $('#gateway-attachment').removeClass('d-none');
            } else {
              $('#gateway-attachment').addClass('d-none');
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
  let id = $('#room-id').text();

  if (subtotal == undefined || parseFloat(subtotal)== 0) {
    alert('Please select check in/ out Date');
    return false;
  }

  if (code) {
    let url = baseURL + '/' + getUser.username + '/rooms/user/room_booking/apply_coupon';
    let data = {
      coupon: code,
      initTotal: subtotal,
      roomId: id,
      _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    $.post(url, data, function (response) {
      if ('success' in response) {
        $('#coupon-code').val('');

        let room_rent_price = parseFloat(subtotal);
        let discount_price = response.discount;
        let newSubtotal = parseFloat(subtotal) - parseFloat(discount_price);
        let calculatedTax = calculation_tax(newSubtotal,room_tax);
        let newGrandTotal = newSubtotal + calculatedTax + room_fee;
        showPriceFront(room_rent_price, newSubtotal,newGrandTotal, discount_price, room_fee, calculatedTax)
        toastr['success'](response.success);
      } else if ('error' in response) {
        toastr['error'](response.error);
      }
    });
  } else {
    alert('Please enter your coupon code.');
  }
}

function showPriceFront(room_rent_price, subtotal, grandTotal, discount = 0, fee = 0, tax = 0) {

   grandTotal = parseFloat(grandTotal);
   subtotal = parseFloat(subtotal);
   discount = parseFloat(discount);
   fee = parseFloat(fee);
   tax = parseFloat(tax);
  room_rent_price = parseFloat(room_rent_price);

  sessionStorage.setItem('room_rent_amount', room_rent_price);
  sessionStorage.setItem('room_subtotal', subtotal);
  sessionStorage.setItem('room_grand_total', grandTotal);
  sessionStorage.setItem('room_discount', discount);
  sessionStorage.setItem('room_fee', fee);
  sessionStorage.setItem('room_tax', tax);


  $('#rent-price').text(room_rent_price.toFixed(2));
  $('#tax-amount').text(tax.toFixed(2));
  $('#fee-amount').text(fee.toFixed(2));
  $('#subtotal-amount').text(subtotal.toFixed(2));
  $('#discount-amount').text(discount.toFixed(2));
  $('#total-amount').text(grandTotal.toFixed(2));
}

function calculation_tax($amount, $per){
  let tax = ($amount * $per) / 100;
  return tax;
}

function calculation_grand_total(newSubtotal , calculatedTax , room_fee) {
  let grandTotal = newSubtotal + calculatedTax + room_fee / 100;
  return grandTotal;
}

function show_exits_price(){

  let room_rent_amount = 0;
  let room_discount_amount = 0;
  let room_subtotal = 0;
  let room_fee_amount = 0;
  let room_tax_amount = 0;
  let room_grand_total_amount = 0;

  if (sessionStorage.getItem('room_rent_amount')) {
    room_rent_amount = sessionStorage.getItem('room_rent_amount');
  }
  if (sessionStorage.getItem('room_discount')) {
    room_discount_amount = sessionStorage.getItem('room_discount');
  }
  if (sessionStorage.getItem('room_subtotal')) {
     room_subtotal = sessionStorage.getItem('room_subtotal');
  }
  if (sessionStorage.getItem('room_fee')) {
    room_fee_amount = sessionStorage.getItem('room_fee');
  }
  if (sessionStorage.getItem('room_tax')) {
    room_tax_amount = sessionStorage.getItem('room_tax');
  }
  if (sessionStorage.getItem('room_grand_total')) {
    room_grand_total_amount = sessionStorage.getItem('room_grand_total');
  }
  showPriceFront(room_rent_amount, room_subtotal, room_grand_total_amount, room_discount_amount, room_fee_amount, room_tax_amount);
}

function remove_coupon() {
  let url = baseURL + '/' + getUser.username + '/rooms/user/room_booking/remove_coupon';
  $.get(url);
}

