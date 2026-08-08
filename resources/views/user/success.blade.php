@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Payment Success') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="#">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Payment Success') }}</a>
            </li>
        </ul>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5 text-center">
                    <div class="mb-3">
                        <i class="fas fa-check-circle fa-4x text-success"></i>
                    </div>
                    <span class="badge badge-success mb-2">{{ __('Payment Successful') }}</span>
                    <h2 class="mb-2">{{ __('Thank you! Your payment is confirmed.') }}</h2>
                    <p class="text-muted mb-4">
                        {{ __('Your account has been updated and your credits are ready to use.') }}
                    </p>

                    <div class="bg-light rounded p-3 text-left mb-4">
                        <div class="d-flex">
                            <div class="mr-3 text-success">
                                <i class="fas fa-bolt fa-lg"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">{{ __('Instant Access') }}</div>
                                <div class="text-muted small">
                                    {{ __('You can start using your credits right away.') }}
                                </div>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex">
                            <div class="mr-3 text-success">
                                <i class="fas fa-list-alt fa-lg"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">{{ __('Payment Log') }}</div>
                                <div class="text-muted small">
                                    {{ __('See your latest transaction in the payment log.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center">
                        <a class="btn btn-primary mb-2 mr-2" href="{{ route('user-dashboard') }}">
                            {{ __('Go to Dashboard') }}
                        </a>
                        <a class="btn btn-outline-primary mb-2" href="{{ route('user.credit_topup.history') }}">
                            {{ __('View Payment Log') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
