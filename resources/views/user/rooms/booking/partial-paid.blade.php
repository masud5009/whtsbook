<!-- Modal HTML -->
<div class="modal fade" id="paymentModal{{ $booking->id }}" tabindex="-1" role="dialog"
  aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel">{{ __('Payment') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('tenant.room_bookings.update_partial_amount') }}" method="POST">
        @csrf
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
        <div class="modal-body">
          <div class="form-group">
            <label for="total_amount">{{ __('Total Amount') }} ({{ $currencyInfo->base_currency_text }})</label>
            <input type="number"step="0.01" class="form-control" id="total_amount" value="{{ $booking->grand_total }}"
              disabled>
          </div>
          <div class="form-group">
            <label for="due">{{ __('Due') }} ({{ $currencyInfo->base_currency_text }})</label>
            <input type="number"step="0.01" class="form-control" value="{{ $booking->due }}" disabled>
          </div>
          <div class="form-group">
            <label for="paying_amount">{{ __('Amount to Pay Now') . '*' }}
              ({{ $currencyInfo->base_currency_text }})</label>
            <input type="number" step="0.01" class="form-control" id="paying_amount" name="paying_amount" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('Submit Payment') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
