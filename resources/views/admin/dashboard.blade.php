@extends('admin.layout')

@php
    $admin = Auth::guard('admin')->user();
    if (!empty($admin->role)) {
        $permissions = $admin->role->permissions;
        $permissions = json_decode($permissions, true);
    }
@endphp

@section('content')
    <div class="mt-2 mb-4">
        <h2 class="@if (request()->cookie('admin-theme') == 'dark') text-white:'' @endif pb-2">{{ __('Welcome back') }},
            {{ Auth::guard('admin')->user()->first_name }}
            {{ Auth::guard('admin')->user()->last_name }}!
        </h2>
    </div>
    <div class="row">

        @if (empty($admin->role) || (!empty($permissions) && in_array('Users Management', $permissions)))
            <div class="col-sm-6 col-md-4">
                <a class="card card-stats card-info card-round" href="{{ route('admin.register.user') }}" target="_blank">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-user-friends"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('Registered Users') }}</p>
                                    <h4 class="card-title">{{ App\Models\User::count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif


        @if (empty($admin->role) || (!empty($permissions) && in_array('Users Management', $permissions)))
            <div class="col-sm-6 col-md-4">
                <a class="card card-stats card-warning card-round" href="{{ route('admin.subscriber.index') }}"
                    target="_blank">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-envelope-open"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('Subscribers') }}</p>
                                    <h4 class="card-title">{{ App\Models\Subscriber::count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif


        @if (empty($admin->role) || (!empty($permissions) && in_array('Packages', $permissions)))
            <div class="col-sm-6 col-md-4">
                <a class="card card-stats card-success card-round" href="{{ route('admin.package.index') }}"
                    target="_blank">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-box-open"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('Packages') }}</p>
                                    <h4 class="card-title">{{ App\Models\Package::count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif


        @if (empty($admin->role) || (!empty($permissions) && in_array('Payment Log', $permissions)))
            <div class="col-sm-6 col-md-4">
                <a class="card card-stats card-danger card-round" href="{{ route('admin.payment-log.index') }}"
                    target="_blank">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('Payment Logs') }}</p>
                                    <h4 class="card-title">{{ App\Models\Membership::count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @if (empty($admin->role) || (!empty($permissions) && in_array('Admins Management', $permissions)))
            <div class="col-sm-6 col-md-4">
                <a class="card card-stats card-secondary card-round" href="{{ route('admin.user.index') }}"
                    target="_blank">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('Admins') }}</p>
                                    <h4 class="card-title">{{ App\Models\Admin::count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @if (empty($admin->role) || (!empty($permissions) && in_array('Pages', $permissions)))
            <div class="col-sm-6 col-md-4">
                <a class="card card-stats card-primary card-round"
                    href="{{ route('admin.blog.index', ['language' => $defaultLang->code]) }}" target="_blank">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('Blog') }}</p>
                                    <h4 class="card-title">{{ $defaultLang->blogs()->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

    </div>

    <div class="row">

        <div class="col-lg-6">
            <div class="card p-4 rounded">
                <div style="position: relative; height: 400px; width: 100%;">
                    <canvas id="usageChart"></canvas>
                </div>
            </div>
        </div>

        @if (empty($admin->role) || (!empty($permissions) && in_array('Payment Log', $permissions)))
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">{{ __('Monthly Income') }} ({{ date('Y') }})</div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (empty($admin->role) || (!empty($permissions) && in_array('Users Management', $permissions)))
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">{{ __('Monthly Premium Users') }} ({{ date('Y') }})</div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="usersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection

@php
    $months = [];
    $inTotals = [];

    for ($i = 1; $i <= 12; $i++) {
        $monthNum = $i;
        $dateObj = DateTime::createFromFormat('!m', $monthNum);
        $months[] = $dateObj->format('M');

        $inFound = 0;
        foreach ($incomes as $key => $income) {
            if ($income->month == $i) {
                $inTotals[] = $income->total;
                $inFound = 1;
                break;
            }
        }
        if ($inFound == 0) {
            $inTotals[] = 0;
        }

        $userFound = 0;
        foreach ($users as $key => $user) {
            if ($user->month == $i) {
                $userTotals[] = $user->total;
                $userFound = 1;
                break;
            }
        }
        if ($userFound == 0) {
            $userTotals[] = 0;
        }
    }

@endphp
@section('scripts')
    <!-- Chart JS -->
    <script src="{{ asset('assets/admin/js/plugin/chart.min.js') }}"></script>
    <script>
        "use strict";

        const __UsedTokens__ = "{{ __('Used Credits') }}";
        const __RemainingTokens__ = "{{ __('Remaining Credits') }}";
        const __EstimatedUsage__ = "{{ __('Estimated Usage') }}";
        const __MonthlyPremiumUsers__ = "{{ __('Monthly Premium Users') }}";
        const __MonthlyIncome__ = "{{ __('Monthly Income') }}";

        var months = @php echo json_encode($months) @endphp;
        var inTotals = @php echo json_encode($inTotals) @endphp;
        var userTotals = @php echo json_encode($userTotals) @endphp;


        const usedToken = {{ $aiUsage->total_usage ?? 0 }};
        const allocatedToken = {{ ($aiUsage->total_usable_tokens ?? 0) + ($aiUsage->total_extend_tokens ?? 0) }};
        const availableToken =
            {{ $bs->admin_total_ai_token - (($aiUsage->total_usable_tokens ?? 0) + ($aiUsage->total_extend_tokens ?? 0)) }};
        const estimatedToken =
            {{ ($aiUsage->total_usable_tokens ?? 0) + ($aiUsage->total_extend_tokens ?? 0) - ($aiUsage->total_usage ?? 0) }};
    </script>
    <script src="{{ asset('assets/admin/js/dashboard.js') }}"></script>
@endsection
