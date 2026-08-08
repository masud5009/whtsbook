<html>
    <head>
        <title>{{$bs->website_title}} - Maintainance Mode</title>
        <!-- favicon -->
        <link rel="shortcut icon" href="{{asset('assets/front/img/'.$bs->favicon)}}" type="image/x-icon">
        <!-- bootstrap css -->
        <link rel="stylesheet" href="{{asset('assets/front/css/bootstrap.min.css')}}">
        <link rel="stylesheet" href="{{asset('assets/front/css/503.css')}}">
        <link rel="stylesheet" href="{{asset('assets/front/css/style.css')}}">
    </head>
    <body>
    <div class="container ptb-90">
        <div class="row align-items-center g">
            <div class="col-md-12 mx-auto text-center">
                <div class="payment-img ">
                    <img src="{{asset('assets/front/img/' . $bs->maintenance_img)}}" alt="">
                </div>
            </div>
        </div>
        <div class="row align-items-center ">
            <div class="col-md-12 mx-auto text-center" id="mt">
                <div class="payment mb-30">
                    <div class="payment_header">
                        <div class="check color-primary">
                            <i class="fa fa-check" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="content">
                        <h2 class="mb-4">{{__('Under Maintenance !')}}</h2>
                        <p class="paragraph-text mb-4">
                            {!! nl2br($bs->maintainance_text) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </body>
    </html>



