<!DOCTYPE html>
<html lang="{{ $language->code }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title> {{ __('ROOM BOOKING INVOICE') }} || {{ @$user->company_name }} </title>
  <link rel="stylesheet" href="{{ asset('assets/tenant/css/design-pdf-2.css') }}">

  @php
    $position = $bookingInfo->currency_text_position;
    $currency = $bookingInfo->currency_text;
    $font_family = 'DejaVu Sans, serif';
    $color = '#' . $userBs->primary_color;
    $rtl = 'rtl';
    $unicode_bidi = 'bidi-override';
    $di_block = 'inline-block';
    $w_60 = '60%';
    $w_10 = '10%';
    $w_30 = '30%';
    $w_80 = '80%';
    $w_20 = '20%';
    $w_45 = '45%';
    $direction = $language->rtl = 1 ? 'rtl' : 'ltr';

  @endphp

  <style>
    body {}

    @page {
      size: A4 portrait;
      margin: 20mm;
      /* এখানে আসল margin apply হবে */
    }

    .rtl {
      unicode-bidi: "{{ $unicode_bidi }}" !important;
      direction: "{{ $rtl }}" !important;
    }

    span {
      display: "{{ $di_block }}"
    }

    .w_50 {
      width: "{{ $w_60 }}" !important;
    }

    .w_10 {
      width: "{{ $w_10 }}" !important;
    }

    .w_40 {
      width: "{{ $w_30 }}" !important;
    }

    .w_80 {
      width: "{{ $w_80 }}";
    }

    .w-20 {
      width: "{{ $w_20 }}";
    }

    .w_45 {
      width: "{{ $w_45 }}";
    }

    .invoice-header {
      background: #ddd;
      padding: 10px 14px;
    }

    .package-info-table thead {
      background: #{{ $userBs->primary_color }};
    }

    .bg-primary {
      background: #{{ $userBs->primary_color }};
    }

    .text-primary {
      color: #{{ $userBs->primary_color }};
    }
  </style>

</head>

<body>
  <div class="main" style="padding: 40px 60px">
    <div class="invoice-container">
      <div class="invoice-wrapper">
        <div class="invoice-area pb-30">
          <!-- invoice-header -->
          <div class="invoice-header clearfix">
            <div class="float-left">
              @if ($userBs->logo)
                <img src="{{ asset('assets/tenant/img/logo/' . $userBs->logo) }}" height="40"
                  class="d-inline-block ">
              @else
                <img src="{{ asset('assets/admin/img/noimage.jpg') }}" height="40" class="d-inline-block">
              @endif
            </div>
            <div class="text-right strong invoice-heading float-right">
              {{ $keywords['INVOICE'] ?? __('INVOICE') }}
            </div>
          </div>
          <!-- invoice_info_table -->
          <div class="mb-15 clearfix tm_invoice_info_table-2 ">
            <table class="">
              <tbody>
                <tr>
                  <td class="">
                    <span><b> {{ $keywords['Number'] ?? __('Booking Number') }}:</b>
                      #{{ $bookingInfo->booking_number }}
                    </span>
                  </td>
                  <td class="text-right">
                    <span><b> {{ $keywords['Date'] ?? __('Date') }}:</b>
                      {{ \Illuminate\Support\Carbon::parse($bookingInfo->created_at)->format('jS, M Y') }}</span>
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- order-info -->
          <div class="order-info clearfix mb-30">
            <div class="text-left float-left">
              <div class="strong">{{ $keywords['Bill to'] ?? __('Bill to') }}:</div>
              <div>
                <strong>{{ $keywords['Customer Name'] ?? __('Customer Name') }}: </strong>
                <span class="{{ $direction }}" dir="{{ $direction }}">
                  {{ ucfirst($bookingInfo->customer_name) }}
                </span>
              </div>

              <div>
                <strong>{{ $keywords['Customer Phone'] ?? __('Customer Phone') }}: </strong>
                <span class="{{ $direction }}" dir="{{ $direction }}">{{ $bookingInfo->customer_phone }}</span>
              </div>

              <div>
                <strong>{{ $keywords['Email'] ?? __('Email') }}: </strong>
                <span class="{{ $direction }}" dir="{{ $direction }}">{{ $bookingInfo->customer_email }}</span>
              </div>
            </div>

            <div class="order-details float-right">
              <div class="text-left">
                <div class="strong">{{ $keywords['From'] ?? __('From') }}:</div>

                <div><strong>{{ $user->company_name }}</strong></div>
                <div><strong>{{ $bs->support_contact }}</strong></div>
                <div><strong>{{ $bs->support_email }}</strong></div>
                <div><strong>{{ $bs->address }}</strong></div>
              </div>
            </div>
          </div>

          <!--package-info table -->
          <div class="mb-15">
            <table class="text-left custom-bordered-table table-striped">
              <thead>
                <tr>
                  <td class="text-left small">
                    <strong>{{ $keywords['Room Type'] ?? __('Room Type') }}</strong>
                  </td>
                  <td class="tm_border_left text-center small">
                    <strong> {{ $keywords['Check-In'] ?? __('Check-In') }}</strong>
                  </td>
                  <td class="tm_border_left text-center small">
                    <strong> {{ $keywords['Check-Out'] ?? __('Check-Out') }}</strong>
                  </td>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <span class="{{ $direction }}" dir="{{ $direction }}">
                      {{ @$bookingInfo->hotelRoom->roomContent->where('language_id', $language->id)->first()->title }}
                    </span>
                  </td>
                  <td class="text-center">
                    {{ \Illuminate\Support\Carbon::parse($bookingInfo->arrival_date)->format('jS, M Y') }}
                  </td>
                  <td class="text-center">
                    {{ \Illuminate\Support\Carbon::parse($bookingInfo->departure_date)->format('jS, M Y') }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- new table -->
          <!--package-info table -->
          @php
            $position = $bookingInfo->currency_text_position;
            $currency = $bookingInfo->currency_text;
          @endphp
          @php
            $grouped = collect($bookingInfo->reserved_dates_info)->groupBy('date');
          @endphp

          <table class="table table-striped mb-20 table-bordered">
            <thead>
              <tr>
                <th scope="col">{{ __('SL') }}</th>
                <th scope="col">{{ __('Date') }}</th>
                <th scope="col">{{ __('Room Numbers') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($grouped as $date => $rooms)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ \Carbon\Carbon::parse($date)->format('d M, Y') }}</td>
                  <td>
                    {{ collect($rooms)->pluck('room_number')->implode(', ') }}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

          <div class="clearfix mb-15">
            <div class="float-left">
              <div>
                <b> {{ $keywords['Payment Method'] ?? __('Payment Method') }}:</b>
                {{ $bookingInfo->payment_method }}
              </div>
              <div>
                <b> {{ __('Payment Status:') }}</b>
                @if ($bookingInfo->payment_status == 1)
                  <h5 class="d-inline-block text-success">{{ __('Complete') }}</h5>
                @elseif ($bookingInfo->payment_status == 3)
                  <h5 class="d-inline-block text-info">{{ __('Partial Paid') }}</h5>
                @elseif ($bookingInfo->payment_status == 0)
                  <h5 class="d-inline-block text-warning">{{ __('Pending') }}</h5>
                @else
                  <h5 class="text-danger d-inline-block ">{{ __('Rejected') }}</h5>
                @endif
              </div>
            </div>
            <!--invoice_footer -->
            <div class="tm_invoice_footer float-right clearfix">
              <div class="tm_right_footer text-right float-right">
                <div>
                  <span class="fw-bold">{{ $keywords['Total Rent:'] ?? __('Total Rent:') }}: </span>
                  <span class="fw-bold">
                    {{ currencyTextPrice($bookingInfo->total_rent, $currency, $position) }}
                  </span>
                </div>
                <div>
                  <span class="fw-bold">{{ $keywords['Sub Total:'] ?? __('Sub Total:') }}: </span>
                  <span class="fw-bold">
                    {{ currencyTextPrice($bookingInfo->subtotal, $currency, $position) }}
                  </span>
                </div>
                <div>
                  <span class="fw-bold">{{ $keywords['Discount:'] ?? __('Discount:') }}: </span>
                  <span class="fw-bold">
                    {{ currencyTextPrice($bookingInfo->discount, $currency, $position) }}
                  </span>
                </div>
                @if ($userBs->room_tax_status === 1)
                  <div>
                    <span class="fw-bold">
                      {{ $keywords['Tax'] ?? __('Tax') }} ({{ $bookingInfo->tax_percentage }} %):
                    </span>

                    <span class="fw-bold">
                      {{ currencyTextPrice($bookingInfo->tax, $currency, $position) }}
                    </span>
                  </div>
                @endif

                @if ($userBs->room_fee_status === 1)
                  <div>
                    <span class="fw-bold">{{  __('Fee') }}:</span>
                    <span class=" fw-bold">
                      {{ currencyTextPrice($bookingInfo->fee, $currency, $position) }}
                    </span>
                  </div>
                @endif
                <div>
                  <span class="fw-bold">{{ $keywords['Grand Total:'] ?? __('Grand Total:') }}: </span>
                  <span class="fw-bold">
                    {{ currencyTextPrice($bookingInfo->grand_total, $currency, $position) }}
                  </span>
                </div>
                <div>
                  <span class="fw-bold">
                    {{ $keywords['Paid Amount:'] ?? __('Paid Amount:') }}:</span>
                  <span class="fw-bold">
                    {{ currencyTextPrice($bookingInfo->paying_amount, $currency, $position) }}
                  </span>
                </div>
                <div>
                  <span class="fw-bold">
                    {{ $keywords['Due:'] ?? __('Due:') }}:</span>
                  <span class="fw-bold">
                    {{ currencyTextPrice($bookingInfo->due, $currency, $position) }}
                  </span>
                </div>
              </div>
            </div>
          </div>


          <!--Thanks & Regards -->
          <div class="mt-50 text-center">
            {{ $keywords['Thanks & Regards'] ?? __('Thanks & Regards') }},
            <span class="text-primary {{ $direction }}" dir="{{ $direction }}">
              {{ @$user->company_name }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
