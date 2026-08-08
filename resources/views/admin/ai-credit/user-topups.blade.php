@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">{{ __('AI Credit Topups') }}</h4>
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
                <a href="{{ route('admin.register.user') }}">{{ __('Registered Users') }}</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">{{ __('AI Credit Topups') }}</a>
            </li>
        </ul>
    </div>



    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="card-title d-inline-block">{{ __('AI Credit Recharge History') }}</div>

                        <button class="btn btn-sm btn-primary editbtn" data-toggle="modal" data-target="#rechargeModal"
                            data-user_id="{{ $aiUsage->user_id }}" data-username="{{ $aiUsage->user->username }}"
                            data-credit_left="{{ human_number($creditLeft) }}">
                            {{ __('Add Credits') }}
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
                                                    {{ ucwords(str_replace('_', ' ', $topup->payment_method)) }}
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
