<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Service') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form id="ajaxForm" class="modal-form" action="{{ route('tenant.room_bookings.update_paid_service') }}"
          method="post">
          @csrf
          <div class="row">
            <input type="text" name="booking_id" value="{{ $id }}" hidden>
            <div class="col-6">
              @php
                $uniqueDates = collect($roomDates)->pluck('date')->unique();
              @endphp

              <div class="form-group">
                <label for="date">{{ __('Select Date') . '*' }} </label>
                <select id="date" name="date" class="form-control">
                  <option selected disabled>{{ __('Select date') }}</option>

                  @foreach ($uniqueDates as $date)
                    <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d M, Y') }}</option>
                  @endforeach
                </select>
                <p id="err_date" class="mt-1 mb-0 text-danger em"></p>
              </div>
            </div>
            <div class="col-6">
              @php
                $uniqueRooms = collect($roomDates)->pluck('room_number')->unique();
              @endphp

              <div class="form-group">
                <label for="room">{{ __('Select Room') . '*' }} </label>
                <select id="room" name="room" class="form-control">
                  <option selected disabled>{{ __('Select Room') }}</option>

                  @foreach ($uniqueRooms as $room)
                    <option value="{{ $room }}">{{ $room }}</option>
                  @endforeach
                </select>
                <p id="err_room" class="mt-1 mb-0 text-danger em"></p>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label for="service">{{ __('Select Service') . '*' }} </label>
                <select id="service" name="service" class="form-control">
                  <option selected disabled>{{ __('Select Service') }}</option>
                  @foreach ($services as $service)
                    @php
                      $symbol = $currencyInfo->base_currency_symbol;
                      $symbolPosition = $currencyInfo->base_currency_symbol_position;
                      $formattedPrice =
                          $symbolPosition == 'left'
                              ? $symbol . number_format($service->price, 2)
                              : number_format($service->price, 2) . $symbol;
                    @endphp
                    <option value="{{ $service->name }}">{{ $service->name }} ({{ $formattedPrice }})</option>
                  @endforeach

                </select>
                <p id="err_service" class="mt-1 mb-0 text-danger em"></p>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label for="quantity">{{ __('Quantity') }} <span class="text-danger">**</span></label>
                <input type="number" id="quantity" name="quantity" class="form-control"
                  placeholder="{{ __('Enter Quantity') }}" min="1" required>
                <p id="err_quantity" class="mt-1 mb-0 text-danger em"></p>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label for="payment_status">{{ __('Select Payment Status') . '*' }} </label>
                <select id="payment_status" name="payment_status" class="form-control">
                  <option selected disabled>{{ __('Select Payment Status') }}</option>
                  <option value="paid">
                    {{ __('Paid') }}
                  </option>
                  <option value="due">
                    {{ __('Due') }}
                  </option>
                </select>
                <p id="err_payment_status" class="mt-1 mb-0 text-danger em"></p>
              </div>
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">
          {{ __('Close') }}
        </button>
        <button id="submitBtn" type="button" class="btn btn-primary">
          {{ __('Save') }}
        </button>
      </div>
    </div>
  </div>
</div>
