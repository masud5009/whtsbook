(function ($) {
    'use strict';

    const roomFee = parseFloat(window.room_fee || 0) || 0;
    const taxRate = parseFloat(window.room_tax || 0) || 0;

    /**
     * Format any number into 2 decimal places
     */
    function fmt(n) {
        n = parseFloat(n) || 0;
        return n.toFixed(2);
    }

    /**
     * Get the "Total Room" limit from the input field.
     * This limit decides how many rooms can be selected per date row.
     * If user enters invalid value, it defaults to 1.
     */
    function getTotalRoomsLimit() {
        const v = parseInt($('input[name="total_rooms"]').val(), 10);
        return isNaN(v) || v < 1 ? 1 : v;
    }

    /** Enforce selection limit for a single date row.
     * If user selects more than allowed rooms for that row,
     * it removes the extra selections to keep it within limit.
     */
    function enforceRowLimit($row) {
        const limit = getTotalRoomsLimit();
        const $selected = $row.find('.room-btn.selected:not(:disabled)');

        if ($selected.length > limit) {
            // remove extra selections
            $selected.slice(limit).removeClass('selected btn-success');
        }
    }

    //Calculate and update all totals in the UI
    function updateTotals() {
        const discount = parseFloat($('#discount').val()) || 0;

        /**
         * Total rent = sum of all selected room button rents.
         * Each selected room button represents a charge for that date slot/night.
         */
        let totalRent = 0;
        $('.room-btn.selected:not(:disabled)').each(function () {
            totalRent += parseFloat($(this).data('rent')) || 0;
        });

        const taxableBase = Math.max(0, totalRent - discount);
        const taxAmount = (taxableBase * taxRate) / 100;
        const grandTotal = taxableBase + taxAmount + roomFee;
        // Update UI
        $('.totalRent').data('amount', totalRent).text(currency + ' ' + fmt(totalRent));
        $('.totalDiscount').data('amount', discount).text(currency + ' ' + fmt(discount));

        $('.taxCharge').text(currency + ' ' + fmt(taxAmount));

        $('input[name="tax_charge"]').val(fmt(taxAmount));

        // Fee spans in your blade have typos: taxChafrge / taxfCharge — update both just in case
        $('.taxChafrge, .taxfCharge').text(currency + ' ' + fmt(roomFee));
        $('input[name="fee_charge"]').val(fmt(roomFee));

        $('.grandTotalRent').text(currency + ' ' + fmt(grandTotal));

        // also update the readonly form input total
        $('#total').val(fmt(totalRent));
    }

    function updateSelectedRoomsJson() {
        const items = [];

        $('.room-btn.selected:not(:disabled)').each(function () {
            items.push({
                room_number: $(this).data('room_number'),
                date: $(this).data('date'),
                room_id: $(this).data('room_id')
            });
        });

        $('#rooms_json').val(JSON.stringify(items));
    }


    // Click on a room button
    $('body').on('click', '.room-btn', function () {
        const $btn = $(this);
        if ($btn.is(':disabled')) return;

        const $row = $btn.closest('tr');
        const limit = getTotalRoomsLimit();
        const isSelecting = !$btn.hasClass('selected');

        if (isSelecting) {
            const selectedCount = $row.find('.room-btn.selected:not(:disabled)').length;
            if (selectedCount >= limit) {
                // Reached limit, do not allow more selections
                alert('You can select up to ' + limit + ' room(s) for this date.');
                return;
            }
        }


        $btn.toggleClass('selected btn-success');
        updateTotals();
        updateSelectedRoomsJson();

    });

    // When total_rooms changes, re-enforce each row
    $('body').on('change', 'input[name="total_rooms"]', function () {
        $('.room-table tr').each(function () {
            enforceRowLimit($(this));
        });
        updateTotals();
        updateSelectedRoomsJson();

    });

    // When discount changes, recalc
    $('body').on('input change', '#discount', function () {
        updateTotals();
    });

    $(document).ready(function () {
        $('.room-btn.selected:not(:disabled)').addClass('btn-success');
        updateTotals();
        updateSelectedRoomsJson();

    });

    /**
     * Handle payment status change
     */
    $('body').on('change', '#payment_status', function () {
        const status = $(this).val();
        // Get grand total value from UI text
        let grandTotalText = $('.grandTotalRent').text();
        let grandTotal = parseFloat(grandTotalText.replace(/[^\d.]/g, '')) || 0;

        const $payingWrapper = $('#paying_amount');
        const $payingInput = $('input[name="paying_amount"]');

        // FULL PAID
        if (status === '1') {
            $payingWrapper.hide();
            $payingInput.val(grandTotal.toFixed(2));
            $payingInput.prop('required', false);
        }

        // PARTIAL PAID
        else if (status === '2') {
            $payingWrapper.show();
            $payingInput.val('0.00');
            $payingInput.prop('required', true);
        }
    });

    /**
     * Prevent paying amount from exceeding grand total
     */
    $('body').on('input', 'input[name="paying_amount"]', function () {
        let payingAmount = parseFloat($(this).val()) || 0;

        let grandTotalText = $('.grandTotalRent').text();
        let grandTotal = parseFloat(grandTotalText.replace(/[^\d.]/g, '')) || 0;

        if (payingAmount > grandTotal) {
            $(this).val(grandTotal.toFixed(2));
            $('#er_paying_amount').text('Paying amount cannot exceed grand total.');
            $('#payment_status').val('2').trigger('change'); // Set to partial paid if user tries to exceed
        }else{
            $('#er_paying_amount').text('');
        }
    });

    // Submit extra-payment acceptance to its own route using a normal POST request.
    $('body').on('click', '#extra-payment-accept', function (e) {
        e.preventDefault();

        const submitUrl = $(this).data('url');
        if (!submitUrl) {
            return;
        }

        const token = $('meta[name="csrf-token"]').attr('content') || $('#roomBookingForm input[name="_token"]').val();
        if (!token) {
            return;
        }

        const $form = $('<form>', {
            method: 'POST',
            action: submitUrl
        });

        $form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: token
        }));

        $('body').append($form);
        $form.trigger('submit');
    });


    /*==============================Booking Submit===========================*/
 $('#roomBookingForm').on('submit', function (e) {
    e.preventDefault();

    // Get the submit button that triggered the form
    let submitBtn = $(e.target).find('button[type="submit"]');
    submitBtn.attr('disabled', true);
    $('.request-loader').addClass('show');

    // Clear previous validation styles
    $('#roomBookingForm .form-control, #roomBookingForm select.form-control, #roomBookingForm textarea.form-control').removeClass('valid-field invalid-field');
    $('#roomBookingForm .form-control, #roomBookingForm select.form-control, #roomBookingForm textarea.form-control').css('border', '');

    let action = $('#roomBookingForm').attr('action');
    let fd = new FormData(document.querySelector('#roomBookingForm'));

    $.ajax({
        url: action,
        method: 'POST',
        data: fd,
        contentType: false,
        processData: false,
        success: function (data) {
            $('.request-loader').removeClass('show');
            submitBtn.attr('disabled', false);

            $('.em').each(function () {
                $(this).html('');
            });

            // Mark all fields as valid on success
            $('#roomBookingForm .form-control, #roomBookingForm select.form-control, #roomBookingForm textarea.form-control').each(function () {
                if ($(this).prop('required')) {
                    $(this).addClass('valid-field').removeClass('invalid-field');
                }
            });

            if (data == 'success') {
                location.reload(true);
            }
            if (data == "downgrade") {
                $('.modal').modal('hide');
                "use strict";
                var content = {};
                content.message = 'Your feature limit is over or down graded!';
                content.title = "Warning";
                content.icon = 'fa fa-bell';
                $.notify(content, {
                    type: 'warning',
                    placement: {
                        from: 'top',
                        align: 'right'
                    },
                    showProgressbar: true,
                    time: 1000,
                    delay: 4000,
                });
                $("#limitModal").modal('show');
            }
        },
        error: function (error) {
            $('.em').each(function () {
                $(this).html('');
            });

            // Clear previous validation styles
            $('#roomBookingForm .form-control, #roomBookingForm select.form-control, #roomBookingForm textarea.form-control').removeClass('valid-field invalid-field');

            // Mark invalid fields with red border and show error messages
            for (let x in error.responseJSON.errors) {
                let errorElement = document.getElementById('er_' + x);
                if (errorElement) {
                    errorElement.innerHTML = error.responseJSON.errors[x][0];
                }

                // Add red border to invalid fields
                let $field = $('#roomBookingForm [name="' + x + '"]');
                if ($field.length) {
                    $field.addClass('invalid-field').removeClass('valid-field');
                }
            }

            // Mark valid required fields with green border
            $('#roomBookingForm .form-control, #roomBookingForm select.form-control, #roomBookingForm textarea.form-control').each(function () {
                if ($(this).prop('required') && !$(this).hasClass('invalid-field')) {
                    let fieldValue = $(this).val();

                    // Special handling for select tags
                    if ($(this).is('select')) {
                        if (fieldValue && fieldValue !== '' && fieldValue !== null) {
                            $(this).addClass('valid-field');
                        } else {
                            $(this).addClass('invalid-field');
                        }
                    }
                    // Special handling for summernote
                    else if ($(this).hasClass('summernote')) {
                        let editorId = $(this).attr('id');
                        if (typeof tinyMCE !== 'undefined' && tinyMCE.get(editorId)) {
                            fieldValue = tinyMCE.get(editorId).getContent();
                            if (fieldValue && fieldValue.trim() !== '') {
                                $(this).addClass('valid-field');
                            } else {
                                $(this).addClass('invalid-field');
                            }
                        }
                    }
                    // Special handling for checkboxes and radio buttons
                    else if ($(this).is(':checkbox') || $(this).is(':radio')) {
                        let isChecked = false;
                        if ($(this).is(':checkbox')) {
                            isChecked = $(this).is(':checked');
                        } else if ($(this).is(':radio')) {
                            let radioGroup = $('[name="' + $(this).attr('name') + '"]');
                            isChecked = radioGroup.is(':checked');
                        }
                        if (isChecked) {
                            $(this).addClass('valid-field');
                        } else {
                            $(this).addClass('invalid-field');
                        }
                    }
                    // Regular inputs and textareas
                    else {
                        if (fieldValue && fieldValue.trim() !== '') {
                            $(this).addClass('valid-field');
                        } else {
                            $(this).addClass('invalid-field');
                        }
                    }
                }
            });

            $('.request-loader').removeClass('show');
            submitBtn.attr('disabled', false);
        }
    });
});
})(jQuery);
