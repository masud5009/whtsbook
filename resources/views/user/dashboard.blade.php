@extends('user.layout')

@php
    use App\Http\Helpers\StaffAuthHelper;
    use App\Http\Helpers\UserPermissionHelper;
    use App\Models\User\Language;
    use Illuminate\Support\Facades\Auth;
    $default = Language::where('is_default', 1)->first();
    $user = Auth::guard('web')->user();
    $package = UserPermissionHelper::currentPackage($user->id);
    if (!empty($user)) {
        $permissions = UserPermissionHelper::packagePermission($user->id);
        $permissions = json_decode($permissions, true);
    }
@endphp

@section('content')
    <div class="mt-2 mb-4">
        <h2 class="pb-2">{{ __('Welcome back') }}, {{ StaffAuthHelper::displayName() }}!</h2>
    </div>


    @if (is_null($package))
        @php
            $pendingMemb = \App\Models\Membership::query()
                ->where([['user_id', '=', Auth::id()], ['status', 0]])
                ->whereYear('start_date', '<>', '9999')
                ->orderBy('id', 'DESC')
                ->first();
            $pendingPackage = isset($pendingMemb)
                ? \App\Models\Package::query()->findOrFail($pendingMemb->package_id)
                : null;
        @endphp

        @if ($pendingPackage)
            <div class="alert alert-warning">
                {{ __('You have requested a package which needs an action (Approval / Rejection) by Admin. You will be notified via mail once an action is taken.') }}
            </div>
            <div class="alert alert-warning">
                <strong>{{ __('Pending Package') }}: </strong> {{ __($pendingPackage->title) }}
                <span class="badge badge-secondary">{{ __($pendingPackage->term) }}</span>
                <span class="badge badge-warning">{{ __('Decision Pending') }}</span>
            </div>
        @else
            <div class="alert alert-warning">
                {{ __('Your membership is expired. Please purchase a new package / extend the current package.') }}
            </div>
        @endif
    @else
        <div class="row justify-content-center align-items-center mb-1">
            <div class="col-12">
                <div class="alert border-left border-primary text-dark">
                    @if ($package_count >= 2)
                        @if ($next_membership->status == 0)
                            <strong
                                class="text-danger">{{ __('You have requested a package which needs an action (Approval / Rejection) by Admin. You will be notified via mail once an action is taken.') }}</strong><br>
                        @elseif ($next_membership->status == 1)
                            <strong
                                class="text-danger">{{ __('You have another package to activate after the current package expires. You cannot purchase / extend any package, until the next package is activated') }}</strong><br>
                        @endif
                    @endif

                    <strong>{{ __('Current Package') }}: </strong> {{ __($current_package->title) }}
                    <span class="badge badge-secondary">{{ __($current_package->term) }}</span>
                    @if ($current_membership->is_trial == 1)
                        ({{ __('Expire Date') }}:
                        {{ Carbon\Carbon::parse($current_membership->expire_date)->format('M-d-Y') }})
                        <span class="badge badge-primary">{{ __('Trial') }}</span>
                    @else
                        ({{ __('Expire Date') }}:
                        {{ $current_package->term === 'lifetime' ? __('Lifetime') : Carbon\Carbon::parse($current_membership->expire_date)->format('M-d-Y') }})
                    @endif

                    @if ($package_count >= 2)
                        <div>
                            <strong>{{ __('Next Package To Activate') }}: </strong> {{ __($next_package->title) }} <span
                                class="badge badge-secondary">{{ __($next_package->term) }}</span>
                            @if ($current_package->term != 'lifetime' && $current_membership->is_trial != 1)
                                (
                                {{ __('Activation Date') }}:
                                {{ Carbon\Carbon::parse($next_membership->start_date)->format('M-d-Y') }},
                                {{ __('Expire Date') }}:
                                {{ $next_package->term === 'lifetime' ? __('Lifetime') : Carbon\Carbon::parse($next_membership->expire_date)->format('M-d-Y') }})
                            @endif
                            @if ($next_membership->status == 0)
                                <span class="badge badge-warning">{{ __('Decision Pending') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="row">

        @if (!is_null($package))
            <div class="col-sm-6 col-md-3">
                <a class="card card-stats card-round card-primary"
                    href="{{ route('tenant.rooms_management.categories', ['language' => $default->code]) }}"
                    target="_self">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-hotel"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('Rooms') }}</p>
                                    <h4 class="card-title">{{ $roomsCount }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif
        @if (!is_null($package))
            <div class="col-sm-6 col-md-3">
                <a class="card card-stats card-round card-info"
                    href="{{ route('tenant.room_bookings.all_bookings', ['language' => $default->code]) }}"
                    target="_self">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-hotel"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('All Bookings') }}</p>
                                    <h4 class="card-title">{{ $allRbCount }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif
        @if (!is_null($package))
            <div class="col-sm-6 col-md-3">
                <a class="card card-stats card-round card-secondary"
                    href="{{ route('tenant.room_bookings.pending_bookings', ['language' => $default->code]) }}"
                    target="_self">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-check-square"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('Pending Bookings') }}</p>
                                    <h4 class="card-title">{{ $pendingBooking }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif
        @if (!is_null($package))
            <div class="col-sm-6 col-md-3">
                <a class="card card-stats card-round card-danger"
                    href="{{ route('tenant.room_bookings.canceled_bookings', ['language' => $default->code]) }}"
                    target="_self">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                            <div class="col-7 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('Rejected Bookings') }}</p>
                                    <h4 class="card-title">{{ $rejectedBooking }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif
    </div>

    @if (!is_null($package))
        <div class="row">
            <div class="col-md-6">
                <div class="card card-stats card-round card-success">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-4">
                                <div class="icon-big text-center">
                                    <i class="fas fa-globe"></i>
                                </div>
                            </div>
                            <div class="col-8 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('Booking via Dashboard') }}</p>
                                    <h4 class="card-title">{{ $webBookingSourceCount }}</h4>
                                    <p class="card-category mb-0">
                                        {{ number_format($webBookingSourcePercentage, 1) }}% {{ __('of total bookings') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-stats card-round card-info">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-4">
                                <div class="icon-big text-center">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                            </div>
                            <div class="col-8 col-stats">
                                <div class="numbers">
                                    <p class="card-category">{{ __('Booking via WhatsApp') }}</p>
                                    <h4 class="card-title">{{ $whatsappBotBookingSourceCount }}</h4>
                                    <p class="card-category mb-0">
                                        {{ number_format($whatsappBotBookingSourcePercentage, 1) }}%
                                        {{ __('of total bookings') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card p-4 rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>
                        {{ __('Token Usage') }}
                    </h3>
                    <button type="button" data-toggle="modal" data-target="#buyCreditModal" class="btn btn-sm btn-info">
                        <i class="fas fa-credit-card mr-1"></i> {{ __('Buy AI Credits') }}
                    </button>
                </div>

                <div style="position: relative; height: 400px; width: 100%;">
                    <canvas id="usageChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card p-4 rounded">
                <div class="dashboard-chart-card__header">
                    <h3 class="dashboard-chart-card__title">
                        {{ __('Monthly Bookings') }}
                    </h3>
                    <select id="bookingYearSelect" class="form-control dashboard-chart-card__year-select"
                        aria-label="{{ __('Select Year') }}">
                        @foreach ($dashboardYears as $year)
                            <option value="{{ $year }}"
                                {{ (int) $selectedBookingYear === (int) $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="position: relative; height: 400px; width: 100%;">
                    <canvas id="MonthlyBookingStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card p-4 rounded">
                <div class="dashboard-chart-card__header">
                    <h3 class="dashboard-chart-card__title">
                        {{ __('Monthly Income') }}
                    </h3>
                    <select id="incomeYearSelect" class="form-control dashboard-chart-card__year-select"
                        aria-label="{{ __('Select Year') }}">
                        @foreach ($dashboardYears as $year)
                            <option value="{{ $year }}"
                                {{ (int) $selectedIncomeYear === (int) $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="position: relative; height: 400px; width: 100%;">
                    <canvas id="MonthlyIncomeChart"></canvas>
                </div>
            </div>
        </div>

    </div>
    @includeIf('user.buy-credit')
@endsection
@section('script')
    <script>
        const CompletedText = "{{ __('Completed') }}"
        const PendingText = "{{ __('Pending') }}"
        const CancelledText = "{{ __('Cancelled') }}"
        const IncomeText = "{{ __('Income') }}"
        const DueText = "{{ __('Due') }}"
        const UsedTokensText = "{{ __('Used AI Credits') }}"
        const AvailableTokensText = "{{ __('Available AI Credits') }}"
        const bookingChartYear = {{ (int) $selectedBookingYear }};
        const incomeChartYear = {{ (int) $selectedIncomeYear }};

        const usedTokens = {{ $usedTokens }};
        const availableToken = {{ $availableToken }};

        const incomeRows = @json($monthly_room_booking_incomes ?? ($data['monthly_room_booking_incomes'] ?? []));
        const bookingRows = @json($monthly_room_bookings_status ?? ($data['monthly_room_bookings_status'] ?? []));
        const allIncomeRows = @json($allMonthlyRoomBookingIncomes ?? ($data['allMonthlyRoomBookingIncomes'] ?? []));
        const allBookingRows = @json($allMonthlyRoomBookingsStatus ?? ($data['allMonthlyRoomBookingsStatus'] ?? []));

        const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        const stripe_key = "{{ $stripe_key ?? '' }}";
        const ogateways = @php echo json_encode($offlineGateways) @endphp;
        const oinstructions = "{{ route('get_payment_instructions') }}";
        const clientKey = "{{ @$authorizeClientKey }}";
        const loginId = "{{ @$authorizeLoginId }}";
    </script>
    @if (!empty($stripe_key))
        <script src="https://js.stripe.com/v3/"></script>
    @endif

    <script type="text/javascript" src="{{ $anetSrc }}" charset="utf-8"></script>
    <!-- Chart JS -->
    <script src="{{ asset('assets/admin/js/plugin/chart.min.js') }}"></script>
    <script src="{{ asset('assets/tenant/js/dashboard.js') }}"></script>
    <script src="{{ asset('js/payment.js') }}"></script>
@endsection
