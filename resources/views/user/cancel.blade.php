@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Payment Cancelled') }}</h4>
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
                <a href="#">{{ __('Payment Cancelled') }}</a>
            </li>
        </ul>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5 text-center">
                    <div class="mb-3">
                        <i class="fas fa-times-circle fa-4x text-danger"></i>
                    </div>
                    <span class="badge badge-danger mb-2">{{ __('Payment Cancelled') }}</span>
                    <h2 class="mb-2">{{ __('Your payment was not completed.') }}</h2>
                    <p class="text-muted mb-4">
                        {{ __('No charges were made. Please try again or choose another payment method.') }}
                    </p>

                    <div class="bg-light rounded p-3 text-left mb-4">
                        <div class="d-flex">
                            <div class="mr-3 text-danger">
                                <i class="fas fa-redo-alt fa-lg"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">{{ __('Try Again') }}</div>
                                <div class="text-muted small">
                                    {{ __('Return to the dashboard and top up your credits.') }}
                                </div>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex">
                            <div class="mr-3 text-danger">
                                <i class="fas fa-receipt fa-lg"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold">{{ __('Payment Log') }}</div>
                                <div class="text-muted small">
                                    {{ __('Review past transactions if needed.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center">
                        <a class="btn btn-primary mb-2 mr-2" href="{{ route('user-dashboard') }}">
                            {{ __('Back to Dashboard') }}
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
