<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"> {{ __('Refund Amount') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="refundForm" action="{{ route('tenant.room_bookings.update_booking_cancel_refund') }}"
                method="POST">
                @csrf
                <input type="hidden" name="booking_id" id="in_booking_id">
                <input type="hidden" name="refund_context" id="in_refund_context" value="adjustment_refund">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="paying_amount">{{ __('Refundable Amount') }}
                        </label>
                        <input type="text" class="form-control" id="in_paying_amount" name="paying_amount" readonly>
                    </div>

                    <div class="form-group" id="refund_type_group">
                        <label for="in_refund_type">{{ __('Refund Type') }} <span class="text-danger">**</span></label>
                        <select class="form-control" id="in_refund_type" name="refund_type">
                            <option value="partial" selected>{{ __('Partial') }}</option>
                            <option value="full">{{ __('Full Refund') }}</option>
                        </select>
                        <p id="err_refund_type" class="mt-1 mb-0 text-danger em"></p>
                    </div>

                    <div class="d-none" id="auto_refund_breakdown">
                        <div class="form-group">
                            <label for="in_refund_percentage">{{ __('Refund Percentage') }}</label>
                            <input type="text" class="form-control" id="in_refund_percentage" readonly>
                        </div>
                        <div class="form-group">
                            <label for="in_calculated_refund_amount">{{ __('Calculated Refund Amount') }}</label>
                            <input type="text" class="form-control" id="in_calculated_refund_amount" readonly>
                        </div>
                    </div>

                    <div class="form-group" id="refund_amount_group">
                        <label for="refund_amount">{{ __('Refund Amount') }} <span class="text-danger">**</span>
                        </label>
                        <input type="text" class="form-control" id="in_refund_amount" name="refund_amount">
                        <p id="err_refund_amount" class="mt-1 mb-0 text-danger em"></p>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('No') }}</button>
                <button type="submit" id="refundBtn" class="btn btn-danger">{{ __('Yes, Refund') }}</button>
            </div>
        </div>

    </div>
</div>
