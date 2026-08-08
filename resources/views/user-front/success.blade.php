@extends('user-front.layout')

@section('pageTitle')
    {{ $userBs->website_title }} | {{ $keywords['Payment Success'] ?? 'Payment Success' }}
@endsection

@section('content')
    @php
        $currency_text = $booking->currency_text;
        $currency_text_position = $booking->currency_text_position;

        $shareInfo = \App\Models\User\BotShareInfo::where('wp_id', $booking->wp_id)
            ->select('hotel_name', 'email_address', 'phone_numbers', 'locations')
            ->first();
        $keywords = json_decode($defaultLang->keywords, true);
    @endphp

    @include('user-front.heading', [
        'pageHeading' => $keywords['Payment Success'] ?? 'Payment Success',
        'booking_number' => $booking->booking_number,
        'booking_step' => 'payment_success',
    ])

    <div class="row g-4">
        <div class="col-lg-8 mx-auto">
            <div class="cardx p-4 p-lg-5 text-center">
                <div class="mb-3">
                    <div class="pill mx-auto" style="width: fit-content;">
                        {{ $keywords['Payment Successful'] ?? 'Payment Successful' }}
                    </div>
                </div>

                <h3 class="mb-4">
                    {{ $keywords['We have received your payment successfully.'] ?? 'We have received your payment successfully.' }}
                </h3>


                <div class="mini-card p-3 text-center mb-4">
                    <p class="subtle mb-3">
                        {{ __("Please check your WhatsApp. We've sent all your booking details and important information there.") }}
                    </p>

                    @if (!empty($whatsappOpenUrl))
                        <a href="{{ $whatsappOpenUrl }}" target="_blank" rel="noopener" class="btn btn-success">
                            <i class="fab fa-whatsapp mr-1"></i>
                            {{ __('Open WhatsApp') }}
                        </a>
                    @endif
                </div>

                <div class="mini-card p-3 text-start mb-4">
                    <div class="d-flex justify-content-between">
                        <div class="subtle">{{ $keywords['Booking Number'] ?? 'Booking Number' }}</div>
                        <div class="fw-semibold">#{{ $booking->booking_number }}</div>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <div class="subtle">{{ $keywords['Guest Name'] ?? 'Guest Name' }}</div>
                        <div class="fw-semibold">{{ $booking->customer_name }}</div>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <div class="subtle">{{ $keywords['Paying Amount'] ?? 'Paying Amount' }}</div>
                        <div class="fw-semibold price">
                            @if ($booking->advance_amount > 0)
                                {{ currencyTextPrice($booking->advance_amount, $currency_text, $currency_text_position) }}
                            @else
                                {{ currencyTextPrice($booking->grand_total, $currency_text, $currency_text_position) }}
                            @endif
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="d-flex justify-content-between">
                        <div class="subtle">{{ $keywords['Arrival Date'] ?? 'Arrival Date' }}</div>
                        <div class="fw-semibold">
                            {{ \Carbon\Carbon::parse($booking->arrival_date)->format('F d, Y') }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <div class="subtle">{{ $keywords['Departure Date'] ?? 'Departure Date' }}</div>
                        <div class="fw-semibold">
                            {{ \Carbon\Carbon::parse($booking->departure_date)->format('F d, Y') }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
