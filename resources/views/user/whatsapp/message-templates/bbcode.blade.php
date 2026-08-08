<div class="list-group">

    @php
        $bbCodes = [
            '{customer_name}' => [
                'label' => __('Customer Name'),
                'template_name' => ['*'],
            ],
            '{payment_link}' => [
                'label' => __('Payment Link'),
                'template_name' => ['send_payment_link','price_increased'],
            ],
            '{status_link}' => [
                'label' => __('Booking Status Link'),
                'template_name' => ['refund_message'],
            ],
            '{refund_amount}' => [
                'label' => __('Refund Amount'),
                'template_name' => ['refund_message', 'price_decreased'],
            ],
            '{paid_amount}' => [
                'label' => __('Already Paid Amount'),
                'template_name' => ['refund_message','price_increased', 'price_decreased'],
            ],
            '{extra_amount}' => [
                'label' => __('Extra Amount'),
                'template_name' => ['price_increased'],
            ],
            '{refund_due}' => [
                'label' => __('Refund Due Amount'),
                'template_name' => ['refund_message'],
            ],
            '{invoice_number}' => [
                'label' => __('Invoice Number'),
                'template_name' => ['send_payment_link','refund_message','booking_placed','price_decreased','price_increased'],
            ],
            '{hotel_name}' => [
                'label' => __('Hotel Name'),
                'template_name' => ['send_payment_link', 'booking_placed'],
            ],

            '{booking_id}' => [
                'label' => __('Booking ID'),
                'template_name' => ['booking_placed', 'payment_received'],
            ],
            '{check_in_date}' => [
                'label' => __('Check-in Date'),
                'template_name' => ['booking_placed', 'send_payment_link'],
            ],
            '{total_amount}' => [
                'label' => __('Total Amount'),
                'template_name' => ['send_payment_link', 'payment_received'],
            ],
        ];
        $currentEvent = $template->event_type;

        $availableCodes = collect($bbCodes)->filter(function ($config, $code) use ($currentEvent) {
            return in_array('*', $config['template_name']) || in_array($currentEvent, $config['template_name']);
        });
    @endphp

    @foreach ($availableCodes as $code => $config)
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <code class="bg-light p-1 rounded bb-item" data-code="{{ $code }}"
                    title="Click to insert">{{ $code }}</code>
                <div class="text-muted small bb-meaning">{{ $config['label'] }}</div>
            </div>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary copy-code" data-code="{{ $code }}"
                    title="Copy">
                    <i class="fas fa-clone"></i>
                </button>
            </div>
        </div>
    @endforeach

</div>
