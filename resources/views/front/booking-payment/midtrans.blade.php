<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
</head>

<body>
    <button class="btn btn-primary" id="pay-button" style="display: none">Pay Now</button>

    <script src="{{ asset('assets/front/js/jquery.min.js') }}"></script>
    @if ($is_production == 0)
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ $client_key }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $client_key }}"></script>
    @endif

    <script>
        'use strict';
        var success_url = "{{ $success_url }}";
        var _cancel_url = "{{ $cancel_url }}";
        $(document).ready(function() {
            $('#pay-button').trigger('click');
        })

        const payButton = document.querySelector('#pay-button');
        payButton.addEventListener('click', function(e) {
            e.preventDefault();

            snap.pay('{{ $snapToken }}', {
                // Optional
                onSuccess: function(result) {
                    const orderId = encodeURIComponent(result.order_id || '');
                    const statusCode = encodeURIComponent(result.status_code || '');

                    window.location.href = success_url + '&order_id=' + orderId + '&status_code=' +
                        statusCode;
                },
                // Optional
                onPending: function(result) {
                    /* You may add your own js here, this is just example */
                    window.location.href = _cancel_url;
                },
                // Optional
                onError: function(result) {
                    /* You may add your own js here, this is just example */
                    window.location.href = _cancel_url;
                }
            });
        });
    </script>
</body>

</html>
