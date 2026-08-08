@php
    use App\Http\Helpers\StaffAuthHelper;
    use App\Http\Helpers\UserPermissionHelper;
    use App\Models\User\BasicSetting;
    use App\Models\User\Language;
    use Illuminate\Support\Facades\Auth;

    $user = Auth::guard('web')->user();
    $isStaff = StaffAuthHelper::isStaff();
    $displayName = StaffAuthHelper::displayName();
    $displayUsername = StaffAuthHelper::displayUsername();
    $displayEmail = StaffAuthHelper::displayEmail();

    $default = Language::where('is_default', 1)->where('user_id', $user->id)->first();
    $package = UserPermissionHelper::currentPackage($user->id);
    if (!empty($user)) {
        $permissions = UserPermissionHelper::packagePermission($user->id);
        $permissions = json_decode($permissions, true);
        $userBs = BasicSetting::where('user_id', $user->id)->first();
    }

    $canDashboard = StaffAuthHelper::hasPermission('Dashboard');
    $canCreditHistory = StaffAuthHelper::hasPermission('AI Credit Recharge History');
    $canWhatsapp = StaffAuthHelper::hasPermission('Connect With Whatsapp');
    $canAiKnowledgeBase = StaffAuthHelper::hasPermission('Train AI Assistant');
    $canFailedMessages = StaffAuthHelper::hasPermission('Failed Messages');
    $canRoomsManagement = StaffAuthHelper::hasPermission('Rooms Management');
    $canRoomBookings = StaffAuthHelper::hasPermission('Room Bookings');
    $canStaffRoles = StaffAuthHelper::hasPermission('Roles & Permissions');
    $canStaffs = StaffAuthHelper::hasPermission('Staffs');
    $canSupportTickets =
        !empty($permissions) &&
        in_array('Support Ticket', $permissions) &&
        StaffAuthHelper::hasPermission('Support Tickets');
    $canMembership = StaffAuthHelper::hasPermission('Membership');
    $canQrCodes =
        !empty($permissions) && in_array('QR Builder', $permissions) && StaffAuthHelper::hasPermission('QR Codes');
    $canGeneralSettings = StaffAuthHelper::hasPermission('General Settings');
    $canPaymentGateways = StaffAuthHelper::hasPermission('Payment Gateways');
    $canLanguages = StaffAuthHelper::hasPermission('Languages');
    $canEmailSettings = StaffAuthHelper::hasPermission('Email Settings');
    $canProfile = StaffAuthHelper::hasPermission('Profile');
    $canChangePassword = StaffAuthHelper::hasPermission('Change Password');
    $canSettings = StaffAuthHelper::hasAnyPermission([
        'General Settings',
        'Payment Gateways',
        'Languages',
        'Email Settings',
        'Train AI Assistant',
        'Profile',
        'Change Password',
    ]);
@endphp
<div class="sidebar sidebar-style-2" @if (request()->cookie('user-theme') == 'dark') data-background-color="dark2" @endif>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <div class="user">
                <div class="avatar-sm float-left mr-2">
                    @if (!$isStaff && !empty($user->photo))
                        <img src="{{ asset('assets/tenant/img/users/' . $user->photo) }}" alt="..."
                            class="avatar-img rounded">
                    @else
                        <img src="{{ asset('assets/admin/img/propics/blank_user.jpg') }}" alt="..."
                            class="avatar-img rounded">
                    @endif
                </div>
                <div class="info">
                    <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                        <span>
                            {{ $displayName }}
                            <span class="user-level">{{ $displayUsername }}</span>
                            <span class="caret"></span>
                        </span>
                    </a>
                    <div class="clearfix"></div>
                    <div class="collapse in" id="collapseExample">
                        <ul class="nav">
                            @if (!is_null($package) && $canProfile)
                                <li>
                                    <a href="{{ route('user.profile_edit') }}">
                                        <span class="link-collapse">{{ __('Edit Profile') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if (!is_null($package) && $canChangePassword)
                                <li>
                                    <a href="{{ route('user.changePass') }}">
                                        <span class="link-collapse">{{ __('Change Password') }}</span>
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a href="{{ route('user-logout') }}">
                                    <span class="link-collapse">{{ __('Logout') }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <ul class="nav nav-primary">
                <div class="row mb-2">
                    <div class="col-12">
                        <form action="">
                            <div class="form-group py-0">
                                <input name="term" type="text" class="form-control sidebar-search" value=""
                                    placeholder="{{ __('Search Menu Here') }}..."
                                    style="direction: {{ $dashboard_language->rtl == 1 ? 'rtl' : 'ltr' }} !important">
                            </div>
                        </form>
                    </div>
                </div>
                @if ($canDashboard)
                    <li class="nav-item
                         @if (request()->path() == 'user/dashboard') active @endif">
                        <a href="{{ route('user-dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <p>{{ __('Dashboard') }}</p>
                        </a>
                    </li>
                @endif

                @if (!is_null($package))
                    {{-- Start Rooms Management --}}
                    @if ($canRoomsManagement)
                        <li
                            class="nav-item
                            @if (request()->routeIs('tenant.rooms_management.settings')) active
                            @elseif (request()->routeIs('tenant.rooms_management.edit_category')) active
                            @elseif (request()->routeIs('tenant.rooms_management.settings')) active
                            @elseif (request()->routeIs('tenant.rooms_management.amenities')) active
                            @elseif (request()->routeIs('tenant.rooms_management.edit_amenity')) active
                            @elseif (request()->routeIs('tenant.rooms_management.coupons')) active
                            @elseif (request()->routeIs('tenant.rooms_management.categories')) active
                            @elseif (request()->routeIs('tenant.rooms_management.create_category')) active
                            @elseif (request()->routeIs('tenant.rooms_management.rooms')) active
                            @elseif (request()->routeIs('tenant.rooms_management.tax_fee')) active @endif">
                            <a data-toggle="collapse" href="#rooms">
                                <i class="fas fa-hotel"></i>
                                <p>{{ __('Rooms Management') }}</p>
                                <span class="caret"></span>
                            </a>
                            <div id="rooms"
                                class="collapse
                                @if (request()->routeIs('tenant.rooms_management.settings')) show
                                @elseif (request()->routeIs('tenant.rooms_management.edit_category')) show
                                @elseif (request()->routeIs('tenant.rooms_management.settings')) show
                                @elseif (request()->routeIs('tenant.rooms_management.amenities')) show
                                @elseif (request()->routeIs('tenant.rooms_management.edit_amenity')) show
                                @elseif (request()->routeIs('tenant.rooms_management.coupons')) show
                                @elseif (request()->routeIs('tenant.rooms_management.categories')) show
                                @elseif (request()->routeIs('tenant.rooms_management.create_category')) show
                                @elseif (request()->routeIs('tenant.rooms_management.rooms')) show
                                @elseif (request()->routeIs('tenant.rooms_management.tax_fee')) show @endif">
                                <ul class="nav nav-collapse">
                                    <li
                                        class="submenu  @if (request()->routeIs('tenant.rooms_management.settings')) selected
                                      @elseif (request()->routeIs('tenant.rooms_management.coupons')) selected
                                      @elseif (request()->routeIs('tenant.rooms_management.amenities')) selected
                                      @elseif(request()->routeIs('tenant.rooms_management.edit_amenity'))  selected
                                      @elseif (request()->routeIs('tenant.rooms_management.tax_fee')) selected @endif">
                                        <a data-toggle="collapse" href="#roomSettings">
                                            <span class="sub-item">{{ __('Settings') }}</span>
                                            <span class="caret"></span>
                                        </a>
                                        <div class="collapse
                                      @if (request()->routeIs('tenant.rooms_management.settings')) show
                                      @elseif (request()->routeIs('tenant.rooms_management.coupons')) show
                                      @elseif (request()->routeIs('tenant.rooms_management.amenities')) show
                                      @elseif(request()->routeIs('tenant.rooms_management.edit_amenity'))  show
                                      @elseif (request()->routeIs('tenant.rooms_management.tax_fee')) show @endif"
                                            id="roomSettings">
                                            <ul class="nav nav-collapse subnav">
                                                <li
                                                    class="
                                                @if (request()->routeIs('tenant.rooms_management.settings')) active @endif">
                                                    <a
                                                        href="{{ route('tenant.rooms_management.settings', ['language' => $default->code]) }}">
                                                        <span class="sub-item">{{ __('Preferences') }}</span>
                                                    </a>
                                                </li>
                                                <li
                                                    class="
                                               @if (request()->routeIs('tenant.rooms_management.coupons')) active @endif">
                                                    <a
                                                        href="{{ route('tenant.rooms_management.coupons', ['language' => $default->code]) }}">
                                                        <span class="sub-item">{{ __('Coupons') }}</span>
                                                    </a>
                                                </li>
                                                <li class="@if (request()->routeIs('tenant.rooms_management.tax_fee')) active @endif">
                                                    <a
                                                        href="{{ route('tenant.rooms_management.tax_fee', ['language' => $default->code]) }}">
                                                        <span class="sub-item">{{ __('Fees - Tax , Fee') }}</span>
                                                    </a>
                                                </li>
                                                <li
                                                    class="
                                                    @if (request()->routeIs('tenant.rooms_management.amenities')) active
                                                    @elseif(request()->routeIs('tenant.rooms_management.edit_amenity'))  active @endif">
                                                    <a
                                                        href="{{ route('tenant.rooms_management.amenities', ['language' => $default->code]) }}">
                                                        <span class="sub-item">{{ __('Amenities') }}</span>
                                                    </a>
                                                </li>

                                            </ul>
                                        </div>
                                    </li>
                                    <li class="@if (request()->routeIs('tenant.rooms_management.create_category')) active @endif">
                                        <a
                                            href="{{ route('tenant.rooms_management.create_category', ['language' => $default->code]) }}">
                                            <span class="sub-item"> {{ __('Add Category') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="@if (request()->routeIs('tenant.rooms_management.categories')) active
                                          @elseif (request()->routeIs('tenant.rooms_management.edit_category')) active @endif">
                                        <a
                                            href="{{ route('tenant.rooms_management.categories', ['language' => $default->code]) }}">
                                            <span class="sub-item"> {{ __('Categories') }}</span>
                                        </a>
                                    </li>
                                    <li class="@if (request()->routeIs('tenant.rooms_management.rooms')) active @endif">
                                        <a
                                            href="{{ route('tenant.rooms_management.rooms', ['language' => $default->code]) }}">
                                            <span class="sub-item"> {{ __('Rooms') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- End Room Management --}}

                    {{-- Start Room Bookings --}}
                    @if ($canRoomBookings)
                        <li
                            class="nav-item @if (request()->routeIs('tenant.room_bookings.all_bookings')) active
                        @elseif (request()->routeIs('tenant.room_bookings.approved_bookings')) active
                        @elseif (request()->routeIs('tenant.room_bookings.pending_bookings')) active
                        @elseif (request()->routeIs('tenant.room_bookings.canceled_bookings')) active
                        @elseif (request()->routeIs('tenant.room_bookings.active_bookings')) active
                        @elseif (request()->routeIs('tenant.room_bookings.refunds')) active
                        @elseif (request()->routeIs('tenant.room_bookings.check_ins.delayed')) active
                        @elseif (request()->routeIs('tenant.room_bookings.check_outs.delayed')) active
                        @elseif (request()->routeIs('tenant.room_bookings.check_ins.upcoming')) active
                        @elseif (request()->routeIs('tenant.room_bookings.check_outs.upcoming')) active
                        @elseif (request()->routeIs('tenant.room_bookings.todays_booked')) active
                        @elseif (request()->routeIs('tenant.room_bookings.booking_edit')) active
                        @elseif (request()->routeIs('tenant.room_bookings.booking_details_and_edit')) active
                        @elseif (request()->routeIs('tenant.rooms_management.report')) active
                        @elseif (request()->routeIs('tenant.room_bookings.booking_form')) active
                        @elseif (request()->routeIs('tenant.room_bookings.booking_details')) active @endif">
                            <a data-toggle="collapse" href="#roomBookings">
                                <i class="fas fa-bookmark"></i>
                                <p class="pr-2">{{ __('Room Bookings') }}</p>
                                <span class="caret"></span>
                            </a>
                            <div id="roomBookings"
                                class="collapse
                            @if (request()->routeIs('tenant.room_bookings.all_bookings')) show
                            @elseif (request()->routeIs('tenant.room_bookings.approved_bookings')) show
                            @elseif (request()->routeIs('tenant.room_bookings.pending_bookings')) show
                            @elseif (request()->routeIs('tenant.room_bookings.canceled_bookings')) show
                            @elseif (request()->routeIs('tenant.room_bookings.active_bookings')) show
                            @elseif (request()->routeIs('tenant.room_bookings.refunds')) show
                            @elseif (request()->routeIs('tenant.room_bookings.check_ins.delayed')) show
                            @elseif (request()->routeIs('tenant.room_bookings.check_outs.delayed')) show
                            @elseif (request()->routeIs('tenant.room_bookings.check_ins.upcoming')) show
                            @elseif (request()->routeIs('tenant.room_bookings.check_outs.upcoming')) show
                            @elseif (request()->routeIs('tenant.room_bookings.todays_booked')) show
                            @elseif (request()->routeIs('tenant.room_bookings.booking_edit')) show
                            @elseif (request()->routeIs('tenant.rooms_management.report')) show
                            @elseif (request()->routeIs('tenant.room_bookings.booking_form')) show
                            @elseif (request()->routeIs('tenant.room_bookings.booking_details')) show @endif">
                                <ul class="nav nav-collapse">
                                    <li
                                        class="{{ request()->routeIs('tenant.room_bookings.all_bookings') ? 'active' : '' }}">
                                        <a href="{{ route('tenant.room_bookings.all_bookings', ['language' => $default->code]) }}">
                                            <span class="sub-item">{{ __('All') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="{{ request()->routeIs('tenant.room_bookings.approved_bookings') ? 'active' : '' }}">
                                        <a href="{{ route('tenant.room_bookings.approved_bookings', ['language' => $default->code]) }}">
                                            <span class="sub-item">{{ __('Approved') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="{{ request()->routeIs('tenant.room_bookings.pending_bookings') ? 'active' : '' }}">
                                        <a href="{{ route('tenant.room_bookings.pending_bookings', ['language' => $default->code]) }}">
                                            <span class="sub-item">{{ __('Pending') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="{{ request()->routeIs('tenant.room_bookings.canceled_bookings') ? 'active' : '' }}">
                                        <a href="{{ route('tenant.room_bookings.canceled_bookings', ['language' => $default->code]) }}">
                                            <span class="sub-item">{{ __('Canceled') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="{{ request()->routeIs('tenant.room_bookings.active_bookings') ? 'active' : '' }}">
                                        <a href="{{ route('tenant.room_bookings.active_bookings', ['language' => $default->code]) }}">
                                            <span class="sub-item">{{ __('Active/Running') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="submenu  @if (request()->routeIs('tenant.room_bookings.check_ins.delayed')) selected
                                      @elseif(request()->routeIs('tenant.room_bookings.check_ins.upcoming')) selected @endif">
                                        <a data-toggle="collapse" href="#checkIns">
                                            <span class="sub-item">{{ __('Check-Ins') }}</span>
                                            <span class="caret"></span>
                                        </a>
                                        <div class="collapse
                                      @if (request()->routeIs('tenant.room_bookings.check_ins.delayed')) show
                                      @elseif(request()->routeIs('tenant.room_bookings.check_ins.upcoming')) show @endif"
                                            id="checkIns">
                                            <ul class="nav nav-collapse subnav">
                                                <li
                                                    class="
                                                @if (request()->routeIs('tenant.room_bookings.check_ins.delayed')) active @endif">
                                                    <a
                                                        href="{{ route('tenant.room_bookings.check_ins.delayed', ['language' => $default->code]) }}">
                                                        <span class="sub-item">{{ __('Delayed') }}</span>
                                                    </a>
                                                </li>
                                                <li
                                                    class="
                                                @if (request()->routeIs('tenant.room_bookings.check_ins.upcoming')) active @endif">
                                                    <a
                                                        href="{{ route('tenant.room_bookings.check_ins.upcoming', ['language' => $default->code]) }}">
                                                        <span class="sub-item">{{ __('Upcoming') }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    <li
                                        class="submenu  @if (request()->routeIs('tenant.room_bookings.check_outs.delayed')) selected
                                      @elseif(request()->routeIs('tenant.room_bookings.check_outs.upcoming')) selected @endif">
                                        <a data-toggle="collapse" href="#checkOuts">
                                            <span class="sub-item">{{ __('Check-Outs') }}</span>
                                            <span class="caret"></span>
                                        </a>
                                        <div class="collapse
                                      @if (request()->routeIs('tenant.room_bookings.check_outs.delayed')) show
                                      @elseif(request()->routeIs('tenant.room_bookings.check_outs.upcoming')) show @endif"
                                            id="checkOuts">
                                            <ul class="nav nav-collapse subnav">
                                                <li
                                                    class="
                                                @if (request()->routeIs('tenant.room_bookings.check_outs.delayed')) active @endif">
                                                    <a
                                                        href="{{ route('tenant.room_bookings.check_outs.delayed', ['language' => $default->code]) }}">
                                                        <span class="sub-item">{{ __('Delayed') }}</span>
                                                    </a>
                                                </li>
                                                <li
                                                    class="
                                                @if (request()->routeIs('tenant.room_bookings.check_outs.upcoming')) active @endif">
                                                    <a
                                                        href="{{ route('tenant.room_bookings.check_outs.upcoming', ['language' => $default->code]) }}">
                                                        <span class="sub-item">{{ __('Upcoming') }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>

                                    <li
                                        class="{{ request()->routeIs('tenant.room_bookings.todays_booked') ? 'active' : '' }}">
                                        <a href="{{ route('tenant.room_bookings.todays_booked') }}">
                                            <span class="sub-item">{{ __('Today\'s Booked') }}</span>
                                        </a>
                                    </li>
                                    <li class="@if (request()->routeIs('tenant.rooms_management.report')) active @endif">
                                        <a href="{{ route('tenant.rooms_management.report') }}">
                                            <span class="sub-item">{{ __('Report') }}</span>
                                        </a>
                                    </li>
                                    <li
                                        class="{{ request()->routeIs('tenant.room_bookings.refunds') ? 'active' : '' }}">
                                        <a href="{{ route('tenant.room_bookings.refunds') }}">
                                            <span class="sub-item">{{ __('Refunds') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    @if ($canWhatsapp)
                        <li
                            class="nav-item
                         @if (request()->routeIs('user.whatsapp_list')) active @endif
                         @if (request()->routeIs('user.whatsapp_share_information')) active @endif
                         @if (request()->routeIs('user.configure_booking_fields')) active @endif
                         @if (request()->routeIs('user.whatsapp_template_messages')) active @endif
                         ">
                            <a href="{{ route('user.whatsapp_list') }}">
                                <i class="fab fa-whatsapp"></i>
                                <p>{{ __('Connect With Whatsapp') }}</p>
                            </a>
                        </li>
                    @endif

                    @if ($canAiKnowledgeBase)
                        <li class="nav-item @if (request()->routeIs('user.ai_knowledge_vault.index')) active @endif">
                            <a href="{{ route('user.ai_knowledge_vault.index') }}">
                                <i class="fas fa-brain"></i>
                                <p>{{ __('Train AI Assistant') }}</p>
                            </a>
                        </li>
                    @endif

                    @if ($canFailedMessages)
                        <li
                            class="nav-item
                         @if (request()->routeIs('user.whatsapp_failed_messages')) active @endif
                         @if (request()->routeIs('user.whatsapp_failed_messages.details')) active @endif
                         ">
                            <a href="{{ route('user.whatsapp_failed_messages') }}">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p>{{ __('Failed Messages') }}</p>
                            </a>
                        </li>
                    @endif
                @endif
                {{-- End Room Bookings --}}

                @if ($canStaffRoles || $canStaffs)
                    <li
                        class="nav-item
                        @if (request()->routeIs('tenant.staff_management.roles')) active
                        @elseif (request()->routeIs('tenant.staff_management.role.permissions.manage')) active
                        @elseif (request()->routeIs('tenant.staff_management.staffs')) active @endif">
                        <a data-toggle="collapse" href="#staffManagement">
                            <i class="fas fa-users-cog"></i>
                            <p>{{ __('Staff Management') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div id="staffManagement"
                            class="collapse
                            @if (request()->routeIs('tenant.staff_management.roles')) show
                            @elseif (request()->routeIs('tenant.staff_management.role.permissions.manage')) show
                            @elseif (request()->routeIs('tenant.staff_management.staffs')) show @endif">
                            <ul class="nav nav-collapse">
                                @if ($canStaffRoles)
                                    <li
                                        class="@if (request()->routeIs('tenant.staff_management.roles')) active
                                        @elseif (request()->routeIs('tenant.staff_management.role.permissions.manage')) active @endif">
                                        <a href="{{ route('tenant.staff_management.roles') }}">
                                            <span class="sub-item">{{ __('Roles & Permissions') }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if ($canStaffs)
                                    <li class="@if (request()->routeIs('tenant.staff_management.staffs')) active @endif">
                                        <a href="{{ route('tenant.staff_management.staffs') }}">
                                            <span class="sub-item">{{ __('Staffs') }}</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- Start User Ticket   --}}
                @if (!is_null($package))
                    @if ($canSupportTickets)
                        <li
                            class="nav-item
                            @if (request()->routeIs('tenant.tickets')) active
                            @elseif (request()->routeIs('tenant.ticket.create')) active
                            @elseif (request()->routeIs('tenant.ticket.messages')) active @endif">
                            <a data-toggle="collapse" href="#ticket">
                                <i class="fas fa-ticket-alt"></i>
                                <p class="pr-2"> {{ __('Support Tickets') }}</p>
                                <span class="caret"></span>
                            </a>
                            <div id="ticket"
                                class="collapse
                                @if (request()->routeIs('tenant.tickets')) show
                                @elseif (request()->routeIs('tenant.ticket.create')) show
                                @elseif (request()->routeIs('tenant.ticket.messages')) show @endif">
                                <ul class="nav nav-collapse">
                                    <li
                                        class=" @if (request()->routeIs('tenant.tickets')) active
                                                @elseif(request()->routeIs('tenant.ticket.create')) active
                                                @elseif(request()->routeIs('tenant.ticket.messages')) active @endif">
                                        <a href="{{ route('tenant.tickets') }}">
                                            <span class="sub-item">{{ __('My Tickets') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                @endif

                @if (!is_null($package) && $canSettings)
                    <li
                        class="nav-item
                @if (request()->routeIs('user.site_settings.general_settings')) active
                @elseif(request()->routeIs('user.dasboard_language')) active
                @elseif(request()->is('user/settings/cookie-alert')) active
                @elseif (request()->routeIs('user.mail_templates')) active
                @elseif (request()->routeIs('user.edit_mail_template')) active
                @elseif (request()->routeIs('user.mail.info')) active
                @elseif (request()->routeIs('user.gateway.index')) active
                @elseif(request()->routeIs('user.gateway.offline')) active
                @elseif(request()->routeIs('user-subdomain')) active
                @elseif (request()->path() == 'user/settings/language/all') active
                @elseif(request()->is('user/settings/language/*/edit')) active
                @elseif(request()->is('user/settings/language/*/edit/keyword')) active
                @elseif (request()->routeIs('user-profile')) active
                @elseif (request()->routeIs('user.changePass')) active @endif">
                        <a data-toggle="collapse" href="#basic">
                            <i class="far fa-cog"></i>
                            <p>{{ __('Settings') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                    @if (request()->routeIs('user.site_settings.general_settings')) show
                    @elseif(request()->routeIs('user.dasboard_language')) show
                    @elseif(request()->is('user/settings/cookie-alert')) show
                    @elseif (request()->routeIs('user.mail_templates')) show
                    @elseif (request()->routeIs('user.edit_mail_template')) show
                    @elseif (request()->routeIs('user.mail.info')) show
                    @elseif (request()->routeIs('user.gateway.index')) show
                    @elseif(request()->routeIs('user.gateway.offline')) show
                    @elseif(request()->routeIs('user-subdomain')) show
                    @elseif (request()->path() == 'user/settings/language/all') show
                    @elseif(request()->is('user/settings/language/*/edit')) show
                    @elseif(request()->is('user/settings/language/*/edit/keyword')) show
                    @elseif (request()->routeIs('user-profile')) show
                    @elseif (request()->routeIs('user.changePass')) show @endif"
                            id="basic">
                            <ul class="nav nav-collapse">

                                @if ($canGeneralSettings)
                                    <li class="@if (request()->routeIs('user.site_settings.general_settings')) active @endif">
                                        <a href="{{ route('user.site_settings.general_settings') }}">
                                            <span class="sub-item">{{ __('General Settings') }}</span>
                                        </a>
                                    </li>
                                @endif

                                {{-- ======= Payment Gateways --}}
                                @if ($canPaymentGateways)
                                    <li
                                        class="submenu
                                    @if (request()->routeIs('user.gateway.index')) selected
                                    @elseif(request()->routeIs('user.gateway.offline')) selected @endif">
                                        <a data-toggle="collapse" href="#gateways">

                                            <span class="sub-item">{{ __('Payment Gateways') }}</span>
                                            <span class="caret"></span>
                                        </a>
                                        <div class="collapse
                                        @if (request()->routeIs('user.gateway.index')) show
                                        @elseif(request()->routeIs('user.gateway.offline')) show @endif"
                                            id="gateways">
                                            <ul class="nav nav-collapse subnav">
                                                <li class="@if (request()->routeIs('user.gateway.index')) active @endif">
                                                    <a href="{{ route('user.gateway.index') }}">
                                                        <span class="sub-item">{{ __('Online Gateways') }}</span>
                                                    </a>
                                                </li>
                                                <li class="@if (request()->routeIs('user.gateway.offline')) active @endif">
                                                    <a
                                                        href="{{ route('user.gateway.offline') . '?language=' . $default->code }}">
                                                        <span class="sub-item">{{ __('Offline Gateways') }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                @endif
                                {{-- ======  Langauge ======== --}}

                                @if ($canLanguages)
                                    <li
                                        class="
                                        @if (request()->path() == 'user/settings/language/all') active
                                        @elseif(request()->is('user/settings/language/*/edit')) active
                                        @elseif(request()->is('user/settings/language/*/edit/keyword')) active @endif">
                                        <a href="{{ route('user.language.index') }}">
                                            <span class="sub-item">{{ __('Languages') }}</span>
                                        </a>
                                    </li>
                                @endif

                                {{-- =====  Email Settings   ==== --}}
                                @if ($canEmailSettings)
                                    <li
                                        class="submenu
                                        @if (request()->routeIs('user.mail_templates')) selected
                                        @elseif (request()->routeIs('user.edit_mail_template')) selected
                                        @elseif (request()->routeIs('user.mail.info')) selected @endif">
                                        <a data-toggle="collapse" href="#mail_settings">
                                            <span class="sub-item">{{ __('Email Settings') }}</span>
                                            <span class="caret"></span>
                                        </a>
                                        <div id="mail_settings"
                                            class="collapse
                                    @if (request()->routeIs('user.mail_templates')) show
                                    @elseif (request()->routeIs('user.mail.info')) show
                                    @elseif (request()->routeIs('user.edit_mail_template')) show @endif">
                                            <ul class="nav nav-collapse subnav">
                                                <li class="@if (request()->routeIs('user.mail.info')) active @endif">
                                                    <a href="{{ route('user.mail.info') }}">
                                                        <span class="sub-item">{{ __('Mail Information') }}</span>
                                                    </a>
                                                </li>
                                                <li
                                                    class="@if (request()->routeIs('user.mail_templates')) active
                                            @elseif (request()->routeIs('user.edit_mail_template')) active @endif">
                                                    <a href="{{ route('user.mail_templates') }}">
                                                        <span class="sub-item">{{ __('Mail Templates') }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                @endif

                                @if ($canProfile)
                                    <li class="@if (request()->routeIs('user.profile_edit')) active @endif">
                                        <a href="{{ route('user.profile_edit') }}">
                                            <span class="sub-item">{{ __('Edit Profile') }}</span>
                                        </a>
                                    </li>
                                @endif

                                @if ($canChangePassword)
                                    <li class="@if (request()->routeIs('user.changePass')) active @endif">
                                        <a href="{{ route('user.changePass') }}">
                                            <span class="sub-item">{{ __('Change Password') }}</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif
                @if ($canMembership)
                    <li
                        class="nav-item
                    @if (request()->path() == 'user/membership/payment-log') active
                    @elseif(request()->path() == 'user/membership/package-list') active
                    @elseif(request()->is('user/membership/package/checkout/*')) active
                    @elseif(request()->path() == 'user/membership/package/checkout/*') active @endif">
                        <a data-toggle="collapse" href="#Membership">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <p>{{ __('Membership') }}</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                        @if (request()->path() == 'user/membership/payment-log') show
                        @elseif(request()->path() == 'user/membership/package-list') show
                        @elseif(request()->is('user/membership/package/checkout/*')) show
                        @elseif(request()->path() == 'user/membership/package/checkout/*') show @endif"
                            id="Membership">
                            <ul class="nav nav-collapse">
                                <li
                                    class="
                                  @if (request()->path() == 'user/membership/payment-log') active @endif">
                                    <a href="{{ route('user.payment-log.index') }}">
                                        <span class="sub-item">{{ __('Payment Logs') }}</span>
                                    </a>
                                </li>
                                <li
                                    class="
                                        @if (request()->path() == 'user/membership/package-list') active
                                        @elseif(request()->is('user/membership/package/checkout/*')) active @endif">
                                    <a href="{{ route('user.plan.extend.index') }}">
                                        <span class="sub-item">{{ __('Buy Plan') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </li>
                @endif

                @if (!is_null($package))
                    @if ($canCreditHistory)
                        <li
                            class="nav-item
                         @if (request()->routeIs('user.credit_topup.history')) active @endif">
                            <a href="{{ route('user.credit_topup.history') }}">
                                <i class="fas fa-coins"></i>
                                <p>{{ __('AI Credit Recharge History') }}</p>
                            </a>
                        </li>
                    @endif
                    {{-- QR Builder --}}
                    @if ($canQrCodes)
                        <li
                            class="nav-item
                @if (request()->routeIs('user.qrcode')) active
                @elseif(request()->routeIs('user.qrcode.index')) active @endif">
                            <a data-toggle="collapse" href="#qrcode">
                                <i class="fas fa-qrcode"></i>
                                <p>{{ __('QR Codes') }}</p>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse
                    @if (request()->routeIs('user.qrcode')) show
                    @elseif(request()->routeIs('user.qrcode.index')) show @endif"
                                id="qrcode">
                                <ul class="nav nav-collapse">
                                    <li class="@if (request()->routeIs('user.qrcode')) active @endif">
                                        <a href="{{ route('user.qrcode') }}">
                                            <span class="sub-item">{{ __('Generate QR Code') }}</span>
                                        </a>
                                    </li>
                                    <li class="@if (request()->routeIs('user.qrcode.index')) active @endif">
                                        <a href="{{ route('user.qrcode.index') }}">
                                            <span class="sub-item">{{ __('Saved QR Codes') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                @endif

            </ul>
        </div>
    </div>
</div>
