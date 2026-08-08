<html>

<head>
    <title>{{ $bs->website_title }}</title>
    <!-- favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/front/img/' . $bs->favicon) }}" type="image/x-icon">
    <!-- bootstrap css -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/503.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/style.css') }}">
</head>

<body>

    <!--    Error section start   -->

    <div class="container ptb-90">

        <div class="row align-items-center ">
            <div class="text-center">
                <h1>Please do not refresh this page...</h1>
            </div>
            <form method="post" action="{{ $paytm_txn_url }}" name="f1">
                {{ csrf_field() }}
                <table border="1">
                    <tbody>
                        <?php
                        foreach ($paramList as $name => $value) {
                            echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
                        }

                        ?>
                        <input type="hidden" name="user_id" value="{{ $user_id }}">
                        <input type="hidden" name="booking_id" value="{{ @$booking_id }}">
                        <input type="hidden" name="tokens" value="{{ @$tokens }}">
                        <input type="hidden" name="amount" value="{{ @$amount }}">
                        <input type="hidden" name="payment_for" value="{{ @$payment_for }}">
                        <input type="hidden" name="package_id" value="{{ @$package_id }}">
                        <input type="hidden" name="start_date" value="{{ @$start_date }}">
                        <input type="hidden" name="expire_date" value="{{ @$expire_date }}">
                        <input type="hidden" name="status" value="{{ @$status }}">
                        <input type="hidden" name="payment_method" value="Paytm">
                        <input type="hidden" name="CHECKSUMHASH" value="<?php echo htmlspecialchars($checkSum); ?>">
                    </tbody>
                </table>

            </form>
        </div>
    </div>
    <!--    Error section end   -->

    <!-- Jquery JS -->
    <script src="{{ asset('assets/front/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/front/js/paytm.js') }}"></script>
</body>

</html>
