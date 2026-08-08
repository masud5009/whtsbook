@if (!empty($warning))
  <div class="alert alert-warning">
    {{ $warning }}
  </div>
@endif

<div class="col-lg-12">
  @if ($insufficientDate)
    <div class="row booking-wrapper">
      <div class="col-12">
        <div class="form-group">
          <h3 class="text-primary"> {{ __('We have only') }} {{ $availableCount }}
            {{ __('room avaiable for') }}
            {{ $dateStr }}</h3>
        </div>
      </div>
    </div>

    <small id="err_rooms" class="text-danger em"></small>
  @else
    <div class="row booking-wrapper">
      <div class="col-xl-12">
        <div class="card">
          <!-- card-header -->
          <div class="card-header d-flex gap-2 flex-wrap justify-content-between">
            <div class="card-title d-flex justify-content-between booking-info-title mb-0">
              <h3 class="mb-0">{{ __('Room Assignment') }}</h3>
            </div>
            <div>
              <span class="fas fa-circle text-danger"></span>
              <span class="">{{ __('Booked') }}</span>
              <span class="fas fa-circle text-success"></span>
              <span class="">{{ __('Selected') }}</span>
              <span class="fas fa-circle text-primary"></span>
              <span>{{ __('Available') }}</span>
            </div>
          </div>
          <!-- card-Body -->
          <div class="card-body">

            <div class="alert alert-info room-assign-alert p-3" role="alert">
              {{ __('Select or deselect rooms with one click. Booked rooms are disabled. Ensure your selection matches the total room count.') }}
            </div>

            <div class="bookingInfo">
              <table class="table-light table-bordered booking-table table">
                <thead>
                  <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Room Number') }}</th>
                  </tr>
                </thead>
                <tbody class="room-table">
                  @foreach ($dates as $day)
                    <tr>
                      <td class="text-center">
                        {{ \Carbon\Carbon::parse($day['date'])->format('d M, Y') }} -
                        {{ \Carbon\Carbon::parse($day['date'])->addDay()->format('d M, Y') }}
                      </td>
                      <td class="room-column">
                        <div class="d-flex w-100 flex-wrap gap-2">

                          @php
                            $selectedRooms = [];
                          @endphp

                          @foreach ($day['rooms'] as $index => $room)
                            @php
                              $btnClass = $room['status'] === 'booked' ? 'btn-danger' : 'btn-primary';
                              $isAvailable = $room['status'] === 'available';

                              $selectedClass =
                                  $isAvailable && count($selectedRooms) < $totalRooms ? 'selected btn-success' : '';

                              // Skip booked rooms and mark them as disabled
                              $dataStatus = $room['status'] === 'booked' ? 1 : 0;
                              $roomId = str_pad($index + 1, 2, '0', STR_PAD_LEFT);

                              // Add the room to the selected list if it's available
if ($isAvailable && count($selectedRooms) < $totalRooms) {
    $selectedRooms[] = $room['room_number'];
                              }
                            @endphp
                            <button type="button"
                              class="btn btn-sm room-btn available {{ $btnClass }} {{ $selectedClass }}"
                              room="room-{{ $room['room_number'] }}" data-room_number="{{ $room['room_number'] }}"
                              data-room_id="{{ $room['id'] ?? $room['room_number'] }}"
                              data-rent="{{ $room['rent'] ?? 60 }}" data-date="{{ $day['date'] }}"
                              data-booked_status="{{ $dataStatus }}"
                              {{ $room['status'] === 'booked' ? 'disabled' : '' }}>
                              {{ $room['room_number'] }}
                            </button>
                          @endforeach
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
              <small id="err_rooms" class="text-danger em"></small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-12">
        <div class="card">
          <div class="card-header">
            <div class="card-title mb-0">
              <h3 class="mb-0">{{ __('Booked Rooms') }}</h3>
            </div>
          </div>
          <div class="card-body">
            <div class="orderList">
              <!-- list-group-flush -->
              <ul class="list-group list-group-flush orderItem">
                <li class="list-group-item">
                  <h5 class="mb-0">{{ __('Room') }}</h5>
                  <h5 class="mb-0">{{ __('Days') }}</h5>
                  <h5 class="mb-0">{{ __('Rent') }}</h5>
                  <h5 class="mb-0">{{ __('Total') }}</h5>
                </li>
                @php
                  $grandTotal = 0;
                @endphp

                @foreach ($dates2[0]['rooms'] as $room)
                  @php
                    $subtotal = $room['total_rent'] ?? (($room['rent'] ?? 0) * $room['days']);
                    $grandTotal += $subtotal;
                  @endphp

                  @php
                    $currencySymbol = $bs->base_currency_text;
                    $symbolPosition = $bs->base_currency_symbol_position;
                    $tax = $bs->tax;
                  @endphp
                  <li class="list-group-item" data-room_number="{{ $room['room_number'] }}">
                    <span>
                      <span class="removeItem btn btn-sm btn-danger">
                        <i class="fa fa-times"></i>
                      </span>
                      {{ $room['room_number'] }}
                    </span>
                    <span class="totalDays">{{ $room['days'] }}</span>
                    <span class="unitRent">
                      @if (!empty($room['is_variable_rent']))
                        {{ __('Varies by date') }}
                      @else
                        @if ($symbolPosition === 'left')
                          {{ $currencySymbol }} {{ number_format((float) ($room['rent'] ?? 0), 2) }}
                        @else
                          {{ number_format((float) ($room['rent'] ?? 0), 2) }} {{ $currencySymbol }}
                        @endif
                      @endif
                    </span>
                    <span class="subTotal" sub_total="{{ $subtotal }}">

                      @if ($symbolPosition === 'left')
                        {{ $currencySymbol }} {{ number_format($subtotal, 2) }}
                      @else
                        {{ number_format($subtotal, 2) }}{{ $currencySymbol }}
                      @endif
                    </span>
                  </li>
                @endforeach
              </ul>

              @php
                $taxRate = $userBs->room_tax_status == 1 ? $userBs->room_tax : 0;
                $roomFee = $userBs->room_fee_status == 1 ? $userBs->room_fee : 0;
                $taxAmount = (($grandTotal - $discount) * $taxRate) / 100;
                $finalTotal = $grandTotal - $discount + $taxAmount + $roomFee;
                $currencySymbol = $bs->base_currency_text;
                $symbolPosition = $bs->base_currency_symbol_position;
              @endphp

              <!-- Grand Total -->
              <div class="d-flex justify-content-between align-items-center border-top p-2 px-3">
                <span>{{ __('Total Rent') }}</span>
                <span class="totalRent" data-amount="{{ $grandTotal }}">

                  @if ($symbolPosition === 'left')
                    {{ $currencySymbol }} {{ number_format($grandTotal, 2) }}
                  @else
                    {{ number_format($grandTotal, 2) }}{{ $currencySymbol }}
                  @endif
                </span>
              </div>
              <div class="d-flex justify-content-between align-items-center border-top p-2 px-3">
                <span>{{ __('Discount') }}</span>
                <span class="totalDiscount" data-amount="{{ $discount }}">

                  @if ($symbolPosition === 'left')
                    {{ $currencySymbol }} {{ number_format($discount, 2) }}
                  @else
                    {{ number_format($discount, 2) }}{{ $currencySymbol }}
                  @endif
                </span>
              </div>
              @if ($userBs->room_tax_status == 1)
                <div class="d-flex justify-content-between align-items-center border-top p-2 px-3">
                  <span>{{ __('Tax') }} <small>({{ $taxRate }}%)</small></span>
                  <span>
                    @if ($symbolPosition === 'left')
                      <span class="taxCharge">{{ $currencySymbol }} {{ number_format($taxAmount, 2) }}</span>
                    @else
                      <span class="taxCharge">{{ number_format($taxAmount, 2) }} {{ $currencySymbol }}</span>
                    @endif
                  </span>
                  <input name="tax_charge" type="hidden" value="{{ number_format($taxAmount, 2) }}">
                </div>
              @endif
              @if ($userBs->room_fee_status == 1)
                <div class="d-flex justify-content-between align-items-center border-top p-2 px-3">
                  <span>{{ __('Fee') }} <small></small></span>
                  <span>
                    @if ($symbolPosition === 'left')
                      <span class="taxChargef">{{ $currencySymbol }}
                        {{ number_format($roomFee, 2) }}</span>
                    @else
                      <span class="taxChargfe">{{ number_format($roomFee, 2) }}
                        {{ $currencySymbol }}</span>
                    @endif
                  </span>
                  <input name="fee_charge" type="hidden" value="{{ number_format($roomFee, 2) }}">
                </div>
              @endif

              <div class="d-flex justify-content-between align-items-center border-top p-2 px-3">
                <span>{{ __('Grand Total') }}</span>
                <span class="grandTotalRent">
                  @if ($symbolPosition === 'left')
                    {{ $currencySymbol }} {{ number_format($finalTotal, 2) }}
                  @else
                    {{ number_format($finalTotal, 2) }} {{ $currencySymbol }}
                  @endif
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
