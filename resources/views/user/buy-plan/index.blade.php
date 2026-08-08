@extends('user.layout')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/buy_plan.css') }}">
@endsection
@php
    $user = Auth::guard('web')->user();
    $package = \App\Http\Helpers\UserPermissionHelper::currentPackage($user->id);
@endphp
@section('content')
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
                {{ __('You have requested a package which needs an action (Approval / Rejection) by Admin. You will be notified via mail once an action is taken') }}
            </div>
            <div class="alert alert-warning">
                <strong>{{ __('Pending Package') . ':' }} </strong> {{ __($pendingPackage->title) }}
                <span class="badge badge-secondary">{{ __($pendingPackage->term) }}</span>
                <span class="badge badge-warning">{{ __('Decision Pending') }}</span>
            </div>
        @else
            <div class="alert alert-warning">
                {{ __('Your membership is expired. Please purchase a new package / extend the current  package') }}
            </div>
        @endif
    @else
        <div class="row justify-content-center align-items-center mb-1">
            <div class="col-12">
                <div class="alert border-left border-primary text-dark">
                    @if ($package_count >= 2)
                        @if ($next_membership->status == 0)
                            <strong
                                class="text-danger">{{ __('You have requested a package which needs an action (Approval / Rejection) by Admin. You will be notified via mail once an action is taken') . '.' }}</strong><br>
                        @elseif ($next_membership->status == 1)
                            <strong
                                class="text-danger">{{ __('You have another package to activate after the current package expires. You cannot purchase / extend any package, until the next package is activated') }}</strong><br>
                        @endif
                    @endif

                    <strong>{{ __('Current Package') . ':' }} </strong> {{ __($current_package->title) }}
                    <span class="badge badge-secondary">{{ __($current_package->term) }}</span>
                    @if ($current_membership->is_trial == 1)
                        ({{ __('Expire Date') . ':' }}
                        {{ Carbon\Carbon::parse($current_membership->expire_date)->format('M-d-Y') }})
                        <span class="badge badge-primary">{{ __('Trial') }}</span>
                    @else
                        ({{ __('Expire Date') . ':' }}
                        {{ $current_package->term === 'lifetime' ? __('Lifetime') : Carbon\Carbon::parse($current_membership->expire_date)->format('M-d-Y') }})
                    @endif

                    @if ($package_count >= 2)
                        <div>
                            <strong>{{ __('Next Package To Activate') . ':' }} </strong> {{ __($next_package->title) }} <span
                                class="badge badge-secondary">{{ __($next_package->term) }}</span>
                            @if ($current_package->term != 'lifetime' && $current_membership->is_trial != 1)
                                ({{ __('Activation Date') . ':' }}
                                {{ Carbon\Carbon::parse($next_membership->start_date)->format('M-d-Y') }},
                                {{ __('Expire Date') . ':' }}
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
    <div class="row mb-5 justify-content-center">
        @foreach ($packages as $key => $package)
            @php
                $pFeatures = json_decode($package->features);
            @endphp
            <div class="col-md-3 pr-md-0 mb-5">
                <div class="card-pricing2 @if (isset($current_package->id) && $current_package->id === $package->id) card-success @else card-primary @endif">
                    <div class="pricing-header">
                        <h3 class="fw-bold d-inline-block">
                            {{ __($package->title) }}
                        </h3>
                        @if (isset($current_package->id) && $current_package->id === $package->id)
                            <h3 class="badge badge-danger d-inline-block float-right ml-2">{{ __('Current') }}</h3>
                        @endif
                        @if ($package_count >= 2 && $next_package->id == $package->id)
                            @if ($next_membership->status == 1)
                                <h3 class="badge badge-warning d-inline-block float-right ml-2">
                                    {{ __('Next') }}</h3>
                            @endif
                        @endif
                        <span class="sub-title"></span>
                    </div>
                    <div class="price-value">
                        <div class="value">
                            <span class="amount">{{ $package->price == 0 ? 'Free' : format_price($package->price) }}</span>
                            <span class="month">/{{ __($package->term) }}</span>
                        </div>
                    </div>
                    <ul class="pricing-content">
                        <li>
                            {{ __('AI Token') }}
                            ({{ human_number($package->total_ai_token ?? 0) }})
                        </li>
                        <li>
                            {{ __('Whatsapp Business Number') }}
                            ({{ $package->whatsapp_limit ?? 1 }})
                        </li>
                        <li>
                            {{ $package->room_categories_limit === 1 ? __('Room Category') : __('Room Categories') }}
                            ({{ $package->room_categories_limit === 999999 ? ' ' . __('Unlimited') : $package->room_categories_limit . ' ' }})
                        </li>

                        <li>
                            {{ $package->room_limit === 1 ? __('Room') : __('Rooms') }}
                            ({{ $package->room_limit === 999999 ? ' ' . __('Unlimited') : $package->room_limit . ' ' }})
                        </li>

                        <li>
                            {{ $package->room_booking_limit === 1 ? __('Room Booking') : __('Room Bookings') }}
                            ({{ $package->room_booking_limit === 999999 ? ' ' . __('Unlimited') : $package->room_booking_limit . ' ' }})
                        </li>
                        <li>
                            {{ $package->language_limit === 1 ? __('Additional Language') : __('Additional Languages') }}
                            ({{ $package->language_limit === 999999 ? ' ' . __('Unlimited') : $package->language_limit . ' ' }})
                        </li>

                        @foreach ($allPfeatures as $feature)
                            <li class="{{ is_array($pFeatures) && in_array($feature, $pFeatures) ? '' : 'disabled' }}">
                                {{ __("$feature") }}

                            </li>
                        @endforeach
                    </ul>

                    @php
                        $hasPendingMemb = \App\Http\Helpers\UserPermissionHelper::hasPendingMembership(Auth::id());
                    @endphp
                    @if ($package_count < 2 && !$hasPendingMemb)
                        <div class="px-4">
                            @if (isset($current_package->id) && $current_package->id === $package->id)
                                @if ($package->term != 'lifetime' || $current_membership->is_trial == 1)
                                    <a href="{{ route('user.plan.extend.checkout', $package->id) }}"
                                        class="btn btn-success btn-lg w-75 fw-bold mb-3">{{ __('Extend') }}</a>
                                @endif
                            @else
                                <a href="{{ route('user.plan.extend.checkout', $package->id) }}"
                                    class="btn btn-primary btn-block btn-lg fw-bold mb-3">{{ __('Buy Now') }}</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection
