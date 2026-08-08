@extends('user.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('AI Credit Recharge History') }}</h4>
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
                <a href="#">{{ __('AI Credit Recharge History') }}</a>
            </li>
        </ul>
    </div>



    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="card-title d-inline-block">{{ __('AI Credit Recharge History') }}</div>

                        <button type="button" data-toggle="modal" data-target="#buyCreditModal"
                            class="btn btn-info">
                            <i class="fas fa-credit-card mr-1"></i> {{ __('Buy AI Credits') }}
                        </button>
                    </div>
                </div>
                <div class="card-body p-2">
                    @if ($topups->count() == 0)
                        <h3 class="text-center">{{ __('NO TOPUP FOUND') }}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mt-2 mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">{{ __('Date') }}</th>
                                        <th scope="col">{{ __('Credits') }}</th>
                                        <th scope="col">{{ __('Paid Amount') }}</th>
                                        <th scope="col">{{ __('Payment Method') }}</th>
                                        <th scope="col">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topups as $topup)
                                        <tr>
                                            <td>
                                                {{ $loop->iteration + ($topups->currentPage() - 1) * $topups->perPage() }}
                                            </td>
                                            <td title="{{ optional($topup->created_at)->format('d M, Y H:i') }}">
                                                {{ optional($topup->created_at)->format('d M, Y H:i') }}
                                            </td>
                                            <td>{{ human_number($topup->token_amount) }}</td>
                                            <td>
                                                {{ currencyTextPrice($topup->paid_amount, $topup->currency_text, $topup->currency_text_position) }}
                                            </td>
                                            <td>
                                                @if ($topup->payment_method == 'admin')
                                                    <p class="font-italic text-muted">{{ __('Admin Recharge') }}</p>
                                                @else
                                                @php
                                                    $formatedPaymentMethod = ucwords(str_replace('_', ' ', $topup->payment_method));
                                                @endphp
                                                    {{ __($formatedPaymentMethod) }}
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                @if ($topup->status === 'approved')
                                                    <span class="badge badge-success">
                                                        {{ __('Approved') }}
                                                    </span>
                                                @elseif ($topup->status === 'pending')
                                                    <span class="badge badge-warning">
                                                        {{ __('Pending') }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                        {{ __('Rejected') }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-inline-block mx-auto">
                        {{ $topups->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @includeIf('user.buy-credit')
@endsection

@section('script')
    <script>
        const stripe_key = "{{ $stripe_key ?? '' }}";
        const ogateways = @php echo json_encode($offlineGateways ?? []) @endphp;
        const oinstructions = "{{ route('get_payment_instructions') }}";
        const clientKey = "{{ $authorizeClientKey ?? '' }}";
        const loginId = "{{ $authorizeLoginId ?? '' }}";
    </script>
    @if (!empty($stripe_key))
        <script src="https://js.stripe.com/v3/"></script>
    @endif
    @if (!empty($anetSrc))
        <script type="text/javascript" src="{{ $anetSrc }}" charset="utf-8"></script>
    @endif
    <script src="{{ asset('js/payment.js') }}"></script>
@endsection
