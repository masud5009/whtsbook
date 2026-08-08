    @php
        $keywords = json_decode($defaultLang->keywords, true);
    @endphp

    <div class="row g-4 align-items-center mb-3">
        <div class="col-lg-12">
            <div class="cardx p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h3 class="mb-1"> {{ $pageHeading }}</h3>

                        @if (isset($booking->booking_number))
                            <div class="subtle">
                                {{ ($keywords['Booking Number'] ?? 'Booking Number') . ':' }}
                                #{{ $booking->booking_number }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
