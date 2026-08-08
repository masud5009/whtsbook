@php
    $infoIcon = false;
    use App\Constants\Constant;
    use App\Http\Helpers\StaffAuthHelper;
    use App\Models\Package;
    use App\Http\Helpers\Uploader;
    use App\Http\Helpers\LimitCheckerHelper;

    $owner = Auth::guard('web')->user();
    $isStaff = StaffAuthHelper::isStaff();
    $displayName = StaffAuthHelper::displayName();
    $displayEmail = StaffAuthHelper::displayEmail();

    $packageId = LimitCheckerHelper::getMembershipId($owner->id);
    $currentPackage = Package::find($packageId);

    $feature = json_decode($currentPackage?->features, true);
    $languageCount = $roomCount = $roomcategoryCount = $roomBookingCount = $roomBookingCouponCount = $packageCount = $packagecategoryCount = $packageBookingCouponCount = $whatsappNumberCount = 0;

    if ($currentPackage && $feature) {
        $languageCount = LimitCheckerHelper::countLanguages($owner->id);
        $roomCount = LimitCheckerHelper::countRooms($owner->id);
        $roomcategoryCount = LimitCheckerHelper::countRoomCategorys($owner->id);
        $roomBookingCount = LimitCheckerHelper::countRoomBookings($owner->id);
        $availableToken = LimitCheckerHelper::availableToken($owner->id);
        $whatsappNumberCount = \App\Models\User\Whatsapp::where('user_id', $owner->id)->count();

        if (
            $languageCount > $currentPackage->language_limit ||
            $roomCount > $currentPackage->room_limit ||
            $roomcategoryCount > $currentPackage->room_categories_limit ||
            $roomBookingCount > $currentPackage->room_booking_limit ||
            $whatsappNumberCount > $currentPackage->whatsapp_limit
        ) {
            $infoIcon = true;
        }
    }
@endphp

<div class="main-header">
    <!-- Logo Header -->
    <div class="logo-header" @if (request()->cookie('user-theme') == 'dark') data-background-color="dark2" @endif>
        <a href="{{ url('/') }}" class="logo" target="_blank">
            <img src="{{ $userBs->logo ? '/assets/tenant/img/logo/' . $userBs->logo : asset('assets/tenant/img/defaultlogo.png') }}"
                alt="navbar brand" style="width:109px;" class="navbar-brand">
        </a>
        <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse"
            data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon">
                <i class="icon-menu"></i>
            </span>
        </button>
        <button class="topbar-toggler more"><i class="icon-options-vertical"></i></button>
        <div class="nav-toggle">
            <button class="btn btn-toggle toggle-sidebar">
                <i class="icon-menu"></i>
            </button>
        </div>
    </div>
    <!-- End Logo Header -->

    <!-- Navbar Header -->
    <nav class="navbar navbar-header navbar-expand-lg"
        @if (request()->cookie('user-theme') == 'dark') data-background-color="dark" @endif>
        <div class="container-fluid">
            <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                <li class="">
                    <form action="{{ route('user.theme.change') }}" class="mr-4 mb-0 form-inline" id="adminThemeForm">
                        <div class="form-group">
                            <div class="selectgroup selectgroup-secondary selectgroup-pills">
                                <label class="selectgroup-item">
                                    <input type="radio" name="theme" value="light" class="selectgroup-input"
                                        {{ empty(request()->cookie('user-theme')) || request()->cookie('user-theme') == 'light' ? 'checked' : '' }}
                                        onchange="document.getElementById('adminThemeForm').submit();">
                                    <span class="selectgroup-button selectgroup-button-icon"><i
                                            class="fa fa-sun"></i></span>
                                </label>
                                <label class="selectgroup-item">
                                    <input type="radio" name="theme" value="dark" class="selectgroup-input"
                                        {{ request()->cookie('user-theme') == 'dark' ? 'checked' : '' }}
                                        onchange="document.getElementById('adminThemeForm').submit();">
                                    <span class="selectgroup-button selectgroup-button-icon"><i
                                            class="fa fa-moon"></i></span>
                                </label>
                            </div>
                        </div>
                    </form>
                </li>

                @if (!$isStaff)
                    <li class="d-flex mr-4">
                        <a class="btn btn-secondary  btn-sm" data-toggle="modal" data-target="#limitModal">
                            <span class="text-white">{{ __('Check Limits') }}
                            </span>

                        </a>
                        <sup class="float-start">
                            @if ($infoIcon == true)
                                <img src="{{ asset('assets/tenant/img/error.png') }}" width="15" class="errorIcon">
                            @endif
                        </sup>
                    </li>

                    <li class="d-flex mr-4">
                        <label class="switch">
                            <input type="checkbox" name="online_status" id="toggle-btn" data-toggle="toggle"
                                data-on="1" data-off="0" @if ($owner->online_status == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                        @if ($owner->online_status == 1)
                            <h5 class="mt-2 ml-2 @if (request()->cookie('user-theme') == 'dark') text-white @endif">
                                {{ __('Active') }}
                            </h5>
                        @else
                            <h5 class="mt-2 ml-2 @if (request()->cookie('user-theme') == 'dark') text-white @endif">
                                {{ __('Deactive') }}
                            </h5>
                        @endif
                    </li>
                @endif

                <li class="nav-item dropdown hidden-caret">
                    <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
                        <div class="avatar-sm">
                            @if (!$isStaff && !empty($owner->photo))
                                <img src="{{ asset('assets/tenant/img/users/' . $owner->photo) }}" alt="..."
                                    class="avatar-img rounded-circle">
                            @else
                                <img src="{{ asset('assets/admin/img/propics/blank_user.jpg') }}" alt="..."
                                    class="avatar-img rounded-circle">
                            @endif
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-user animated fadeIn">
                        <div class="dropdown-user-scroll scrollbar-outer">
                            <li>
                                <div class="user-box">
                                    <div class="avatar-lg">
                                        @if (!$isStaff && !empty($owner->photo))
                                            <img src="{{ asset('assets/tenant/img/users/' . $owner->photo) }}"
                                                alt="..." class="avatar-img rounded">
                                        @else
                                            <img src="{{ asset('assets/admin/img/propics/blank_user.jpg') }}"
                                                alt="..." class="avatar-img rounded">
                                        @endif
                                    </div>
                                    <div class="u-text">
                                        <h4>{{ $displayName }}</h4>
                                        <p class="text-muted">{{ $displayEmail }}</p>
                                        @if (StaffAuthHelper::hasPermission('Profile'))
                                            <a href="{{ route('user.profile_edit') }}"
                                                class="btn btn-xs btn-secondary btn-sm">{{ __('Edit Profile') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                            <li>
                                @if (StaffAuthHelper::hasPermission('Profile'))
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item"
                                        href="{{ route('user.profile_edit') }}">{{ __('Edit Profile') }}</a>
                                @endif
                                @if (StaffAuthHelper::hasPermission('Change Password'))
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item"
                                        href="{{ route('user.changePass') }}">{{ __('Change Password') }}</a>
                                @endif
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('user-logout') }}">{{ __('Logout') }}</a>
                            </li>
                        </div>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <!-- End Navbar -->
</div>


<div class="modal fade" id="limitModal" tabindex="-1" aria-labelledby="limitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="limitModalLabel"> {{ __('All Limits') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if ($currentPackage)
                    <ul class="list-group limit-modal">

                        <li class="list-group-item border">
                            <div class="d-flex justify-content-between">
                                <span>
                                    @if ($roomCount > $currentPackage->room_limit)
                                        <img src="{{ asset('assets/tenant/img/error.png') }}" width="15"
                                            class="errorIcon ">
                                    @endif {{ __('Rooms Left') . ':' }}
                                </span>
                                <span
                                    class="badge badge-primary badge-sm">{{ $currentPackage->room_limit == 999999 ? __('Unlimited') : ($currentPackage->room_limit - $roomCount < 0 ? 0 : $currentPackage->room_limit - $roomCount) }}</span>
                            </div>
                            @if ($roomCount - $currentPackage->room_limit == 0)
                                <p class="text-warning m-0">
                                    {{ __('Your room limit has been reached. You cannot add more rooms.') }}
                                </p>
                            @endif
                            @if ($roomCount > $currentPackage->room_limit)
                                <p class="text-warning m-0">{{ __('Limit has been crossed, you have to delete') }}
                                    {{ abs($currentPackage->room_limit - $roomCount) }}
                                    {{ abs($currentPackage->room_limit - $roomCount) == 1 ? __('room') : __('rooms') }}
                                </p>
                            @endif
                        </li>
                        <li class="list-group-item border">
                            <div class="d-flex  justify-content-between">
                                <span>
                                    @if ($roomcategoryCount > $currentPackage->room_categories_limit)
                                        <img src="{{ asset('assets/tenant/img/error.png') }}" width="15"
                                            class="errorIcon ">
                                    @endif {{ __('Room Categories Left') }} :
                                </span>
                                <span
                                    class="badge badge-primary badge-sm">{{ $currentPackage->room_categories_limit == 999999 ? __('Unlimited') : ($currentPackage->room_categories_limit - $roomcategoryCount < 0 ? 0 : $currentPackage->room_categories_limit - $roomcategoryCount) }}</span>
                            </div>
                            @if ($roomcategoryCount - $currentPackage->room_categories_limit == 0)
                                <p class="text-warning m-0">
                                    {{ __('Your room category limit has been reached. You cannot add more room categories.') }}
                                </p>
                            @endif

                            @if ($roomcategoryCount > $currentPackage->room_categories_limit)
                                <p class="text-warning m-0">{{ __('Limit has been crossed, you have to delete') }}
                                    {{ abs($currentPackage->room_categories_limit - $roomcategoryCount) }}
                                    {{ abs($currentPackage->room_categories_limit - $roomcategoryCount) == 1 ? __('room category') : __('room categories') }}
                                </p>
                            @endif
                        </li>
                        <li class="list-group-item border">
                            <div class="d-flex  justify-content-between">
                                <span>
                                    @if ($roomBookingCount > $currentPackage->room_booking_limit)
                                        <img src="{{ asset('assets/tenant/img/error.png') }}" width="15"
                                            class="errorIcon ">
                                    @endif {{ __('Room Bookings Left') }} :
                                </span>

                                <span
                                    class="badge badge-primary badge-sm">{{ $currentPackage->room_booking_limit == 999999 ? __('Unlimited') : ($currentPackage->room_booking_limit - $roomBookingCount < 0 ? 0 : $currentPackage->room_booking_limit - $roomBookingCount) }}</span>
                            </div>
                            @if ($roomBookingCount - $currentPackage->room_booking_limit == 0)
                                <p class="text-warning m-0">
                                    {{ __('Your room booking limit has been reached. You cannot add more room bookings.') }}
                                </p>
                            @endif
                            @if ($roomBookingCount > $currentPackage->room_booking_limit)
                                <p class="text-warning m-0">{{ __('Limit has been crossed, you have to delete') }}
                                    {{ abs($currentPackage->room_booking_limit - $roomBookingCount) }}
                                    {{ abs($currentPackage->room_booking_limit - $roomBookingCount) == 1 ? __('room booking') : __('room bookings') }}
                                </p>
                            @endif
                        </li>
                        <li class="list-group-item border">
                            <div class="d-flex  justify-content-between">
                                <span>
                                    @if ($languageCount > $currentPackage->language_limit)
                                        <img src="{{ asset('assets/tenant/img/error.png') }}" width="15"
                                            class="errorIcon ">
                                    @endif {{ __('Languages Left') }} :
                                </span>
                                <span
                                    class="badge badge-primary badge-sm">{{ $currentPackage->language_limit == 999999 ? __('Unlimited') : ($currentPackage->language_limit - $languageCount < 0 ? 0 : $currentPackage->language_limit - $languageCount) }}
                                </span>
                            </div>

                            @if ($languageCount - $currentPackage->language_limit == 0)
                                <p class="text-warning m-0">
                                    {{ __('Your language limit has been reached. You cannot add more languages.') }}
                                </p>
                            @endif
                            @if ($languageCount > $currentPackage->language_limit)
                                <p class="text-warning m-0">{{ __('Limit has been crossed, you have to delete') }}
                                    {{ abs($currentPackage->language_limit - $languageCount) }}
                                    {{ abs($currentPackage->language_limit - $languageCount) == 1 ? __('language') : __('languages') }}
                                </p>
                            @endif
                        </li>


                        <li class="list-group-item border">
                            <div class="d-flex  justify-content-between">
                                <span>
                                    @if ($whatsappNumberCount > $currentPackage->whatsapp_limit)
                                        <img src="{{ asset('assets/tenant/img/error.png') }}" width="15"
                                            class="errorIcon ">
                                    @endif {{ __('Whatsapp Number Left') }} :
                                </span>
                                <span
                                    class="badge badge-primary badge-sm">{{ $currentPackage->whatsapp_limit == 999999 ? __('Unlimited') : ($currentPackage->whatsapp_limit - $whatsappNumberCount < 0 ? 0 : $currentPackage->whatsapp_limit - $whatsappNumberCount) }}
                                </span>
                            </div>

                            @if ($whatsappNumberCount - $currentPackage->whatsapp_limit == 0)
                                <p class="text-warning m-0">
                                    {{ __('Your language limit has been reached. You cannot add more languages.') }}
                                </p>
                            @endif
                            @if ($whatsappNumberCount > $currentPackage->whatsapp_limit)
                                <p class="text-warning m-0">{{ __('Limit has been crossed, you have to delete') }}
                                    {{ abs($currentPackage->whatsapp_limit - $whatsappNumberCount) }}
                                    {{ abs($currentPackage->whatsapp_limit - $whatsappNumberCount) == 1 ? __('language') : __('languages') }}
                                </p>
                            @endif
                        </li>

                        <li class="list-group-item border">
                            <div class="d-flex  justify-content-between">
                                <span>
                                    @if ($whatsappNumberCount > $currentPackage->whatsapp_limit)
                                        <img src="{{ asset('assets/tenant/img/error.png') }}" width="15"
                                            class="errorIcon ">
                                    @endif {{ __('Available AI Credits') }} :
                                </span>
                                <span
                                    class="badge badge-success badge-sm">
                                    {{ human_number($availableToken) }}
                                </span>
                            </div>
                        </li>

                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
