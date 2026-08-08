(function ($) {
    'use strict';

    let selected_dates;
    let num_of_nights;
    let subtotal;
    let discount;
    let total;

    function syncAdjustmentRefundInputs() {
        const isAdjustmentRefund = $('#in_refund_context').val() === 'adjustment_refund';
        if (!isAdjustmentRefund) {
            return;
        }

        const isFullRefund = $('#in_refund_type').val() === 'full';
        const refundableAmount = Number($('#in_paying_amount').val() || 0);

        $('#refund_amount_group').toggleClass('d-none', isFullRefund);

        if (isFullRefund) {
            $('#in_refund_amount').val(refundableAmount.toFixed(2));
            $('#in_refund_amount').prop('readonly', true);
            $('#in_refund_amount').removeAttr('required');
            $('#in_refund_amount').attr('data-full-refund', '1');
            $('#err_refund_amount').text('');
        } else {
            if ($('#in_refund_amount').attr('data-full-refund') === '1') {
                $('#in_refund_amount').val('');
            }
            $('#in_refund_amount').prop('readonly', false);
            $('#in_refund_amount').attr('required', true);
            $('#in_refund_amount').removeAttr('data-full-refund');
        }
    }

    function setRefundModalMode(mode) {
        const isAutoRefund = mode === 'booking_rejected';

        if (!$('#in_refund_context').length) {
            return;
        }

        $('#in_refund_context').val(mode);
        $('#refund_type_group').toggleClass('d-none', isAutoRefund);
        $('#refund_amount_group').toggleClass('d-none', isAutoRefund);
        $('#auto_refund_note').toggleClass('d-none', !isAutoRefund);
        $('#auto_refund_breakdown').toggleClass('d-none', !isAutoRefund);

        if (isAutoRefund) {
            $('#in_refund_type').val('partial');
            $('#in_refund_amount').prop('readonly', true);
            $('#in_refund_amount').removeAttr('required');
        } else {
            syncAdjustmentRefundInputs();
        }
    }

    // Must be global because booking table uses inline onchange handler.
    window.handleBookingStatusChange = function (selectElem, bookingId, refundableAmount, refundPercentage, refundAmount) {
        const selectedValue = selectElem.value;
        const calculatedRefundAmount = Number(refundAmount || 0);

        if (selectedValue === '2' && calculatedRefundAmount > 0) {
            setRefundModalMode('booking_rejected');
            $('#editModal').modal('show');
            $('#in_booking_id').val(bookingId);
            $('#in_paying_amount').val(Number(refundableAmount || 0).toFixed(2));
            $('#in_refund_percentage').val(Number(refundPercentage || 0).toFixed(2) + '%');
            $('#in_calculated_refund_amount').val(calculatedRefundAmount.toFixed(2));
            $('#in_refund_amount').val(calculatedRefundAmount.toFixed(2));
        } else {
            const form = document.getElementById('bookingStatusForm' + bookingId);
            if (form) {
                form.submit();
            }
        }
    };




    /*******************************************************
    ==========Room Booking with AJAX Request Start==========
    *******************************************************/
 $('#roomBookingNextBtn').on('click', function (e) {
    $(e.target).attr('disabled', true);
    $('.request-loader').addClass('show');

    // Clear previous validation styles
    $('#roomSelectForm .form-control, #roomSelectForm select.form-control').removeClass('valid-field invalid-field');
    $('#roomSelectForm .form-control, #roomSelectForm select.form-control').css('border', '');

    let action = $('#roomSelectForm').attr('action');
    let roomId = $('#selected-room').val();
    let dates = $('#date-range').val();
    let languageId = $('#roomSelectForm [name="language_id"]').val();

    $.get(action, {
        room_category_id: roomId,
        dates: dates,
        language_id: languageId
    }, function (response) {
        if ('success' in response) {
            $('.request-loader').removeClass('show');
            $(e.target).attr('disabled', false);

            $('.em').each(function () {
                $(this).html('');
            });

            // Mark all fields as valid on success
            $('#roomSelectForm .form-control, #roomSelectForm select.form-control').each(function () {
                if ($(this).prop('required')) {
                    $(this).addClass('valid-field').removeClass('invalid-field');
                }
            });

            let url = response.success;
            window.location = url;

        } else if ('error' in response) {
            $('.em').each(function () {
                $(this).html('');
            });

            // Clear previous validation styles
            $('#roomSelectForm .form-control, #roomSelectForm select.form-control').removeClass('valid-field invalid-field');

            let errMsg = response.error.room_category_id ? response.error.room_category_id[0] : '';
            let errMsg2 = response.error.dates ? response.error.dates[0] : '';

            $('#err_room_category_id').text(errMsg);
            $('#err_dates').text(errMsg2);

            // Mark invalid fields with red border
            if (response.error.room_category_id) {
                let $roomField = $('#selected-room');
                if ($roomField.length) {
                    $roomField.addClass('invalid-field').removeClass('valid-field');
                }
            }

            if (response.error.dates) {
                let $dateField = $('#date-range');
                if ($dateField.length) {
                    $dateField.addClass('invalid-field').removeClass('valid-field');
                }
            }

            // Mark valid required fields with green border
            // Check room field
            let $roomField = $('#selected-room');
            if ($roomField.prop('required') && !$roomField.hasClass('invalid-field')) {
                let roomValue = $roomField.val();
                if (roomValue && roomValue !== '' && roomValue !== null) {
                    $roomField.addClass('valid-field');
                } else {
                    $roomField.addClass('invalid-field');
                }
            }

            // Check date field
            let $dateField = $('#date-range');
            if ($dateField.prop('required') && !$dateField.hasClass('invalid-field')) {
                let dateValue = $dateField.val();
                if (dateValue && dateValue !== '' && dateValue !== null) {
                    $dateField.addClass('valid-field');
                } else {
                    $dateField.addClass('invalid-field');
                }
            }

            $('.request-loader').removeClass('show');
            $(e.target).attr('disabled', false);
        }
    }).fail(function(error) {
        // Handle AJAX failure
        $('.request-loader').removeClass('show');
        $(e.target).attr('disabled', false);

        // Clear previous validation styles
        $('#roomSelectForm .form-control, #roomSelectForm select.form-control').removeClass('valid-field invalid-field');

        // If there's a validation error from server
        if (error.responseJSON && error.responseJSON.errors) {
            for (let x in error.responseJSON.errors) {
                let $field = $('#roomSelectForm [name="' + x + '"]');
                if ($field.length) {
                    $field.addClass('invalid-field').removeClass('valid-field');
                }
                if (document.getElementById('err_' + x)) {
                    document.getElementById('err_' + x).innerHTML = error.responseJSON.errors[x][0];
                }
            }
        }

        console.error('AJAX Error:', error);
    });
});
    /*****************************************************
    ==========Room Booking with AJAX Request End==========
    *****************************************************/


    // initialize date range picker
    let dateArray;

    if (typeof bookedDates != 'undefined') {
        dateArray = bookedDates;
    } else {
        dateArray = [];
    }

    $('#date-range').daterangepicker({
        minDate: new Date(),
        opens: 'left',
        autoUpdateInput: false,
        autoApply: true,
        locale: {
            format: 'YYYY-MM-DD'
        },
        isCustomDate: function (date) {
            for (let index = 0; index < dateArray.length; index++) {
                if (date.format('YYYY-MM-DD') == dateArray[index]) {
                    return ['room-booked-date', 'text-white'];
                }
            }
        }
    });

    // show the dates and number of nights in input field when user select a date range
    $('#date-range').on('apply.daterangepicker', function (event, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));

        // get the difference of two dates, date should be in 'YYYY-MM-DD' format
        let dates = $(this).val();

        // first, slice the string and get the arrival_date & departure_date
        let arrOfDate = dates.split(' ');
        let arrival_date = arrOfDate[0];
        let departure_date = arrOfDate[2];

        // parse the strings into date using Date constructor
        arrival_date = new Date(arrival_date);
        departure_date = new Date(departure_date);

        // get the time difference (in millisecond) of two dates
        let difference_in_time = departure_date.getTime() - arrival_date.getTime();

        // finally, get the night difference of two dates (convert time to night)
        let difference_in_night = difference_in_time / (1000 * 60 * 60 * 24);

        $('#night').val(difference_in_night);

        sendRoomData();
    });

    // remove the dates and number of nights when user click on cancel button
    $('#date-range').on('cancel.daterangepicker', function (event, picker) {
        $(this).val(selected_dates);
        $('#night').val(num_of_nights);
        $('#subtotal').val(subtotal);
        $('#discount').val(discount);
        $('#total').val(total);
    });


    /*==============================Send Payment Link===========================*/
    $('body').on('click', '#sendPaymentLinkBtn', function (e) {
        e.preventDefault();
        let booking_id = $(this).data('id');
        let action = $(this).data('href');
        let btn = $(this);
        btn.text('Sending...').attr('disabled', true);
        $.ajax({
            url: action,
            method: 'GET',
            data: { booking_id: booking_id },
            success: function (data) {
                location.reload(true);
            }
        });
    });

    $(document).on('change', '#in_refund_type', function () {
        syncAdjustmentRefundInputs();
        $('#err_refund_type').text('');
    });

    $(document).on('click', '.editBtn', function () {
        setRefundModalMode('adjustment_refund');
        $('#in_refund_type').val('partial');
        $('#in_refund_amount').val('');
        $('#in_refund_percentage').val('');
        $('#in_calculated_refund_amount').val('');
        $('#err_refund_type').text('');
    });

    $('#editModal').on('shown.bs.modal', function () {
        syncAdjustmentRefundInputs();
    });

    $('#editModal').on('hidden.bs.modal', function () {
        setRefundModalMode('adjustment_refund');
        $('#in_refund_type').val('partial');
        $('#in_refund_amount').val('');
        $('#in_refund_percentage').val('');
        $('#in_calculated_refund_amount').val('');
        $('#err_refund_amount').text('');
        $('#err_refund_type').text('');
    });

    const roomAssignmentAlert = document.getElementById('roomAssignmentAlert');
    if (roomAssignmentAlert) {
        setTimeout(function () {
            roomAssignmentAlert.style.transition = 'opacity 0.5s ease-in-out';
            roomAssignmentAlert.style.opacity = '0';
            setTimeout(function () {
                roomAssignmentAlert.remove();
            }, 500);
        }, 5000);
    }
})(jQuery);
