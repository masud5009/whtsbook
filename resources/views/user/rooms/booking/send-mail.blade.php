{{-- send mail modal --}}
<div class="modal fade" id="mailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">
          {{ __('Send Mail') }}
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form id="ajaxForm" action="{{ route('tenant.room_bookings.send_mail') }}" method="POST">
          @csrf
          <div class="form-group">
            <label for="">{{ __('Customer Mail Address') }} <span class="text-danger">**</span></label>
            <input type="email" class="form-control" id="mail-id" name="customer_email" readonly>
          </div>

          <div class="form-group">
            <label for="">{{ __('Subject') }} <span class="text-danger">**</span></label>
            <input type="text" class="form-control" name="subject" placeholder="">
            <p id="err_subject" class="mt-1 mb-0 text-danger em"></p>
          </div>

          <div class="form-group">
            <label for="">{{ __('Message') }} <span class="text-danger">**</span></label>
            <textarea class="form-control summernote" id="summernote" name="message" placeholder="{{ __('Enter Message') }}"
              data-height="150"></textarea>
            <p id="err_message" class="mb-0 text-danger em"></p>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">
          {{ __('Close') }}
        </button>
        <button id="submitBtn" type="button" class="btn btn-primary">
          {{ __('Send') }}
        </button>
      </div>
    </div>
  </div>
</div>
