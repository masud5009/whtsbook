@extends('user.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Paid Services') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('user-dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Room Bookings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Paid Services') }}</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row w-100 align-items-center">
            <div class="col-md-7">
              <h5 class="card-title mb-0 d-inline-block">{{ __('Paid Services') }}</h5>
            </div>
            <div class="col-md-5 text-md-right mt-2 mt-md-0">
              <a href="#" data-toggle="modal" data-target="#createModal" class="btn btn-primary btn-sm"><i
                  class="fas fa-plus"></i> {{ __('Add') }}</a>
            </div>
          </div>
        </div>


        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (is_array($paidServices) && count($paidServices) == 0)
                <h3 class="text-center">{{ __('NO SERVICE FOUND') . '!' }}</h3>
              @elseif (is_array($paidServices))
                <div class="table-responsive">
                  <table class="table table-striped mt-3" id="basic-datatables">
                    <thead>
                      <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Room') }}</th>
                        <th>{{ __('Service') }}</th>
                        <th>{{ __('Price') }}</th>
                        <th>{{ __('Quantity') }}</th>
                        <th>{{ __('Payment Status') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($paidServices as $service)
                        <tr>
                          <td>{{ \Carbon\Carbon::parse($service['date'])->format('d M, Y') }}</td>
                          <td>{{ $service['room'] }}</td>
                          <td>{{ $service['service'] }}</td>
                          <td>
                            @php
                              $symbol = $currencyInfo->base_currency_symbol;
                              $symbolPosition = $currencyInfo->base_currency_symbol_position;
                              $formattedPrice =
                                  $symbolPosition == 'left'
                                      ? $symbol . number_format($service['price'], 2)
                                      : number_format($service['price'], 2) . $symbol;
                            @endphp

                            {{ $formattedPrice }}
                          </td>
                          <td>{{ $service['quantity'] ?? 1 }}</td>
                          <td>
                            @if ($service['payment_status'] == 'paid')
                              <span class="badge bg-success">{{ __('Paid') }}</span>
                            @else
                              <form id="paymentStatusForm{{ $service['id'] }}" class="d-inline-block"
                                action="{{ route('tenant.room_bookings.paid_service.update_payment_status') }}"
                                method="post">
                                @csrf
                                <input type="hidden" name="service_id" value="{{ $service['id'] }}">
                                <input type="hidden" name="booking_id" value="{{ $id }}">
                                <select
                                  class="form-control form-control-sm {{ $service['payment_status'] == 'paid' ? 'bg-info' : 'bg-warning' }}"
                                  name="payment_status"
                                  onchange="document.getElementById('paymentStatusForm{{ $service['id'] }}').submit();">
                                  <option value="paid" {{ $service['payment_status'] == 'paid' ? 'selected' : '' }}>
                                    {{ __('Paid') }}
                                  </option>
                                  <option value="due" {{ $service['payment_status'] == 'due' ? 'selected' : '' }}>
                                    {{ __('Due') }}
                                  </option>
                                </select>
                              </form>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @else
                <h3 class="text-center">{{ __('NO SERVICE FOUND') . '!' }}</h3>
              @endif

            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="row">
          </div>
        </div>
      </div>
    </div>
  </div>
  {{-- create modal --}}
  @include('user.rooms.booking.add-service')
  {{-- paid services list --}}
@endsection
