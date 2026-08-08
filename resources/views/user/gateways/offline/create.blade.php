<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Gateway') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <form id="ajaxForm" class="modal-form" action="{{ route('user.gateway.offline.store') }}" method="POST">
          @csrf
          <div class="row">
            <div class="col-lg-12">
              <div class="form-group">
                <label for="">{{ __('Name') }} <span class="text-danger">**</span></label>
                <input type="text" class="form-control" name="name" placeholder="{{ __('Enter Name') }}"
                  value="">
                <p id="err_name" class="mb-0 text-danger em"></p>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="">{{ __('Short Description') }}</label>
            <textarea class="form-control" name="short_description" rows="3" cols="80"
              placeholder="{{ __('Enter short description') }}"></textarea>
            <p id="err_short_description" class="mb-0 text-danger em"></p>
          </div>

          <div class="form-group">
            <label for="">{{ __('Instructions') }}</label>
            <textarea class="form-control summernote" id="summernote" name="instructions" rows="3" cols="80"
              placeholder="{{  __('Enter instructions') }}" data-height="150"></textarea>
          </div>

          <div class="row">
            <div class="col-lg-6">
              <div class="form-group">
                <label>{{  __('Attachment Status') }} <span class="text-danger">**</span></label>
                <div class="selectgroup w-100">
                  <label class="selectgroup-item">
                    <input type="radio" name="is_receipt" value="1" class="selectgroup-input" checked>
                    <span class="selectgroup-button">{{  __('Active') }}</span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="is_receipt" value="0" class="selectgroup-input">
                    <span class="selectgroup-button">{{ __('Deactive') }}</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Serial Number') }} <span class="text-danger">**</span></label>
                <input type="number" class="form-control ltr" name="serial_number" value=""
                  placeholder="{{  __('Enter Serial Number') }}">
                <p id="err_serial_number" class="mb-0 text-danger em"></p>
                <p class="text-warning">
                  <small>{{ __('The higher the serial number, the later it will be displayed.') }}</small>
                </p>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
        <button id="submitBtn" type="button" class="btn btn-primary">{{ __('Submit') }}</button>
      </div>
    </div>
  </div>
</div>
