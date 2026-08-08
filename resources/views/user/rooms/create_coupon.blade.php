<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Coupon') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form id="ajaxForm" class="modal-form" action="{{ route('tenant.rooms_management.store_coupon') }}"
          method="post" autocomplete="off">
          @csrf
          <div class="row">
            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Name') }} <span class="text-danger">**</span></label>
                <input type="text" class="form-control" name="name" placeholder="{{ __('Enter Name') }}">
                <p id="err_name" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Code') }} <span class="text-danger">**</span></label>
                <input type="text" class="form-control" name="code" placeholder="{{ __('Enter Code') }}">
                <p id="err_code" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Coupon Type') }} <span class="text-danger">**</span></label>
                <select name="type" class="form-control">
                  <option selected disabled>{{ __('Select a Type') }}</option>
                  <option value="fixed">{{ __('Fixed') }}</option>
                  <option value="percentage">{{ __('Percentage') }}</option>
                </select>
                <p id="err_type" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Value') }} <span class="text-danger">**</span></label>
                <input type="number" step="0.01" class="form-control" name="value"
                  placeholder="{{ __('Enter Value') }}">
                <p id="err_value" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Start Date') }} <span class="text-danger">**</span></label>
                <input type="text" class="form-control datepicker" name="start_date"
                  placeholder="{{ __('Enter Start Date') }}">
                <p id="err_start_date" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('End Date') }} <span class="text-danger">**</span></label>
                <input type="text" class="form-control datepicker" name="end_date"
                  placeholder="{{ __('Enter End Date') }}">
                <p id="err_end_date" class="mt-2 mb-0 text-danger em"></p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Serial Number') }} <span class="text-danger">**</span></label>
                <input type="number" class="form-control" name="serial_number"
                  placeholder="{{ __('Enter Serial Number') }}">
                <p id="err_serial_number" class="mt-2 mb-0 text-danger em"></p>
                <p class="text-warning mt-2 mb-0">
                  <small>{{ __('The higher the serial number, the later it will be displayed.') }}</small>
                </p>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="form-group">
                <label for="">{{ __('Rooms') }}</label>
                <select name="rooms[]" class="form-control select2" multiple="multiple">
                  @foreach ($rooms as $room)
                    <option value="{{ $room->id }}">
                      {{ $room->title }}
                    </option>
                  @endforeach
                </select>
                <p class="text-warning mt-2 mb-0">
                  <small>
                    {{ __('This coupon can be applied to these rooms') }}<br>
                    {{ __('Leave this field empty for all rooms') }}
                  </small>
                </p>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-danger " data-dismiss="modal">
          {{ __('Close') }}
        </button>
        <button id="submitBtn" type="button" class="btn btn-primary ">
          {{ __('Save') }}
        </button>
      </div>
    </div>
  </div>
</div>
