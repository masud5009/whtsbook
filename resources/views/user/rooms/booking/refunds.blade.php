@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Refunds') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('user-dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Room Bookings') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Refunds') }}</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="card-title d-inline-block">{{ __('Refunds') }}</div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($refunds) == 0)
                                <h3 class="text-center">{{ __('NO REQUEST FOUND') . '!' }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" id="basic-datatables">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('#SL') }}</th>
                                                <th scope="col">{{ __('Name') }}</th>
                                                <th scope="col">{{ __('Email') }}</th>
                                                <th scope="col">{{ __('Phone') }}</th>
                                                <th scope="col">{{ __('Paying Amount') }}</th>
                                                <th scope="col">{{ __('Refund Amount') }}</th>
                                                <th scope="col">{{ __('Refunded At') }}</th>
                                                <th scope="col">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($refunds as $key => $refund)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $refund->customer_name }}</td>
                                                    <td>{{ $refund->customer_email }}</td>
                                                    <td>{{ $refund->customer_phone }}</td>
                                                    <td>
                                                        {{ currencySymbolPrice($refund->paying_amount, $refund->currency_symbol, $refund->currency_symbol_position) }}
                                                    </td>
                                                    <td>
                                                        {{ currencySymbolPrice($refund->refund_amount, $refund->currency_symbol, $refund->currency_symbol_position) }}
                                                    </td>
                                                   <td>
                                                    {{ $refund->created_at->format('d M Y h:i') }}
                                                   </td>

                                                    <!-- Delete Button -->
                                                    <td>
                                                        <form action="{{ route('tenant.room_bookings.refund.delete') }}"
                                                            method="POST" class="deleteForm">
                                                            @csrf
                                                            <input type="hidden" name="refund_id"
                                                                value="{{ $refund->id }}">

                                                            <button type="submit" class="btn btn-danger btn-sm deleteBtn">
                                                                <span class="btn-label"><i class="fas fa-undo"></i></span>
                                                                {{ __('Revert') }}
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
