<!-- Refund Modal -->
<div class="modal fade" id="refundModal-{{ $booking->id }}" tabindex="-1" role="dialog"
  aria-labelledby="refundModalLabel{{ $booking->id }}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form action="{{ route('tenant.room_bookings.update_booking_cancel_refund') }}" method="POST">
      @csrf
      <input type="hidden" name="booking_id" value="{{ $booking->id }}">

      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="refundModalLabel{{ $booking->id }}">{{ __('Cancel Booking & Refund') }}</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <p>{{ __('Are you sure you want to cancel this booking and issue a refund?') }}</p>

          <div class="form-group">
            <label for="paying_amount_{{ $booking->id }}">{{ __('Paying Amount') }}
              ({{ $currencyInfo->base_currency_text }})</label>
            <input type="text" class="form-control" id="paying_amount_{{ $booking->id }}"
              value="{{ number_format($booking->paying_amount, 2) }}" readonly>
          </div>

          <div class="form-group">
            <label for="refund_amount_{{ $booking->id }}">{{ __('Refund Amount') }}
              ({{ $currencyInfo->base_currency_text }})</label>
            <input type="number" step="0.01" class="form-control" id="refund_amount_{{ $booking->id }}"
              name="refund_amount" value="{{ number_format($booking->paying_amount, 2) }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('No') }}</button>
          <button type="submit" class="btn btn-danger">{{ __('Yes, Refund') }}</button>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- End of Refund Modal -->
