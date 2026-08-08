/**
 * Booking Check-In/Check-Out Handler
 * Manages date filtering and booking status changes
 */

function handleDateOptionChange() {
    const opt = document.getElementById('date_option').value;
    const single = document.getElementById('single_date');
    const start = document.getElementById('start_date');
    const end = document.getElementById('end_date');

    // Determine if this is upcoming or past check-in/out
    const isUpcoming = document.body.getAttribute('data-is-upcoming') === 'true';

    if (opt === 'custom') {
        single.style.display = 'none';
        start.style.display = 'inline-block';
        end.style.display = 'inline-block';
        return;
    }

    // specific day UI
    single.style.display = 'inline-block';
    start.style.display = 'none';
    end.style.display = 'none';

    const today = new Date();
    if (isUpcoming) {
        if (opt === 'today') single.value = today.toISOString().slice(0, 10);
        if (opt === 'tomorrow') {
            const t = new Date(today);
            t.setDate(today.getDate() + 1);
            single.value = t.toISOString().slice(0, 10);
        }
    } else {
        if (opt === 'today') single.value = today.toISOString().slice(0, 10);
        if (opt === 'yesterday') {
            const y = new Date(today);
            y.setDate(today.getDate() - 1);
            single.value = y.toISOString().slice(0, 10);
        }
    }
}

function handleBookingStatusChange(selectElem, bookingId) {
    const selectedValue = selectElem.value;
    if (selectedValue === '2') {
        // Show refund modal if canceling
        if (jQuery && jQuery('#refundModal-' + bookingId).length) {
            jQuery('#refundModal-' + bookingId).modal('show');
        }
    } else {
        document.getElementById('bookingStatusForm' + bookingId).submit();
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    handleDateOptionChange();
});
