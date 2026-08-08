@extends('user-front.layout')

@section('pageTitle')
    {{ $userBs->website_title }} | {{ $keywords['Payment Cancel'] ?? 'Payment Cancel' }}
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
        'pageHeading' => $keywords['Booking Cancelled'] ?? 'Booking Cancelled',
        'booking_number' => $booking->booking_number,
        'booking_step' => 'payment_cancel',
    ])

    <div class="row g-4">
        <div class="col-lg-8 mx-auto">
            <div class="cardx p-4 p-lg-5 text-center">
                <div class="mb-3">
                    <div class="pill mx-auto"
                        style="width: fit-content; border-color:#fecaca; background:#fff1f2; color:#991b1b;">
                        {{ $keywords['Payment Cancelled'] ?? 'Payment Cancelled' }}
                    </div>
                </div>

                <h3 class="mb-2">
                    {{ $keywords['Your payment was not completed.'] ?? 'Your payment was not completed.' }}
                </h3>

                <p class="subtle mb-4">
                    {{ $keywords['No worries — you can try again using a different payment method.'] ?? 'No worries — you can try again using a different payment method.' }}
                </p>

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
                        <div class="subtle">{{ $keywords['Amount'] ?? 'Amount' }}</div>
                        <div class="fw-semibold price">
                            @if ($booking->advance_amount > 0)
                                {{ currencyTextPrice($booking->advance_amount, $currency_text, $currency_text_position) }}
                            @else
                                {{ currencyTextPrice($booking->grand_total, $currency_text, $currency_text_position) }}
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
