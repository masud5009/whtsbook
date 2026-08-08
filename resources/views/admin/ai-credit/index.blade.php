@extends('admin.layout')
@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('Credit Purchase Requests') }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('AI Credits') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('Credit Purchase Requests') }}</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-title d-inline-block">{{ __('All Requests') }}</div>
                        </div>
                        <div class="col-lg-5">
                        </div>
                        <div class="col-lg-3 mt-2 mt-lg-0 justify-content-end">
                            <form action="{{ url()->current() }}" class="d-inline-block d-flex">
                                <input class="form-control" type="text" name="username"
                                    placeholder="{{ __('Search by Username') }}"
                                    value="{{ request()->input('username') ? request()->input('username') : '' }}">
                                <button class="dis-none" type="submit"></button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($topups) == 0)
                                <h3 class="text-center">{{ __('NO TOPUP FOUND') }}</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('Username') }}</th>
                                                <th scope="col">{{ __('Credits') }}</th>
                                                <th scope="col">{{ __('Paid Amount') }}</th>
                                                <th scope="col">{{ __('Gateway') }}</th>
                                                <th scope="col">{{ __('Payment Method') }}</th>
                                                <th scope="col">{{ __('Date') }}</th>
                                                <th scope="col">{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topups as $topup)
                                                <tr>
                                                    <td>{{ $topup->user->username ?? '-' }}</td>
                                                    <td>{{ human_number($topup->token_amount ?? 0) }}</td>
                                                    <td>
                                                        {{ currencyTextPrice($topup->paid_amount, $topup->currency_text, $topup->currency_text_position) }}
                                                    </td>
                                                    <td>
                                                        {{ __($topup->gateway_type) ?? '-' }}
                                                    </td>
                                                    <td>
                                                        {{ __($topup->payment_method) ?? '-' }}
                                                    </td>
                                                    <td>{{ optional($topup->created_at)->format('d M, Y H:i') }}</td>
                                                    <td>
                                                        @if ($topup->status === 'pending')
                                                            <form action="{{ route('admin.ai-credit.topup-status') }}"
                                                                method="POST">
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $topup->id }}">
                                                                <select name="status" class="form-control"
                                                                    onchange="this.form.submit()">
                                                                    <option value="" disabled selected>
                                                                        {{ __('Select') }}</option>
                                                                    <option value="approved">{{ __('Approved') }}</option>
                                                                    <option value="rejected">{{ __('Rejected') }}</option>
                                                                </select>
                                                            </form>
                                                        @elseif ($topup->status === 'approved')
                                                            <span class="badge badge-success">{{ __('Approved') }}</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ __('Rejected') }}</span>
                                                        @endif
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
                <div class="card-footer">
                    <div class="row">
                        <div class="d-inline-block mx-auto">
                            {{ $topups->appends(['username' => request()->input('username'), 'usage_page' => request()->input('usage_page')])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recharge Credit Modal -->
    <div class="modal fade" id="rechargeModal" tabindex="-1" role="dialog" aria-labelledby="rechargeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="ajaxForm" action="{{ route('admin.ai-credit.recharge') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rechargeModalLabel">{{ __('Add Credits') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="in_user_id">
                        <div class="form-group">
                            <label>{{ __('Username') }}</label>
                            <input type="text" class="form-control" id="in_username" readonly>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Credits Remaining') }}</label>
                            <input type="text" class="form-control" id="in_credit_left" readonly>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Credits to Add') }} <span class="text-danger">**</span></label>
                            <input type="number" class="form-control ltr" name="tokens" min="1" step="1"
                                placeholder="{{ __('e.g. 1000') }}">
                            <p id="err_tokens" class="mb-0 text-danger em"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" id="submitBtn" class="btn btn-primary">{{ __('Add Credits') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        "use strict";
        $('#rechargeModal').on('show.bs.modal', function() {
            $(this).find('input[name="tokens"]').val('');
        });
    </script>
@endsection
