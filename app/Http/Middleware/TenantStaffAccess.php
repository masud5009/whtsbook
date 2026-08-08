<?php

namespace App\Http\Middleware;

use App\Http\Helpers\StaffAuthHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantStaffAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('staff')->check()) {
            return $next($request);
        }

        $staff = StaffAuthHelper::staff();

        if (empty($staff) || empty($staff->roleInfo)) {
            Auth::guard('staff')->logout();
            Auth::guard('web')->logout();
            session()->flash('warning', __('The assigned role is no longer available.'));

            return redirect()->route('staff.login');
        }

        if ($this->isAlwaysAllowed($request)) {
            return $next($request);
        }

        $permission = $this->resolvePermission($request);

        if (empty($permission) || !StaffAuthHelper::hasPermission($permission)) {
            session()->flash('warning', __('You do not have permission to access this resource.'));

            return redirect()->route('user-dashboard');
        }

        return $next($request);
    }

    private function isAlwaysAllowed(Request $request): bool
    {
        return $request->routeIs('user-logout')
            || $request->routeIs('user.theme.change')
            || $request->routeIs('user.rtlcheck')
            || $request->routeIs('user.rtlcheck_2')
            || $request->routeIs('user.summernote.upload');
    }

    private function resolvePermission(Request $request): ?string
    {
        $namedRoutes = [
            'user-dashboard' => 'Dashboard',
            'user.credit_topup.history' => 'Credit Recharge History',
            'user.buy-credit' => 'Credit Recharge History',
            'tenant.credit_buy.success' => 'Credit Recharge History',
            'tenant.credit_buy.cancel' => 'Credit Recharge History',
            'tenant.credit_buy.payment_success.view' => 'Credit Recharge History',
            'tenant.credit_buy.payment_cancel.view' => 'Credit Recharge History',
            'tenant.razorpay.success' => 'Credit Recharge History',
            'tenant.paytm.success' => 'Credit Recharge History',
            'tenant.paytabs.success' => 'Credit Recharge History',
            'tenant.iyzico.success' => 'Credit Recharge History',
            'tenant.staff_management.roles' => 'Roles & Permissions',
            'tenant.staff_management.role.store' => 'Roles & Permissions',
            'tenant.staff_management.role.update' => 'Roles & Permissions',
            'tenant.staff_management.role.delete' => 'Roles & Permissions',
            'tenant.staff_management.role.permissions.manage' => 'Roles & Permissions',
            'tenant.staff_management.role.permissions.update' => 'Roles & Permissions',
            'tenant.staff_management.staffs' => 'Staffs',
            'tenant.staff_management.staff.store' => 'Staffs',
            'tenant.staff_management.staff.update' => 'Staffs',
            'tenant.staff_management.staff.delete' => 'Staffs',
            'tenant.room_bookings.send_payment_link' => 'Room Bookings Payment Link',
            'tenant.room_bookings.update_stay_status' => 'Room Bookings Stay Status',
            'tenant.room_bookings.update_payment_status' => 'Room Bookings Payment Status',
            'tenant.room_bookings.update_partial_amount' => 'Room Bookings Payment Status',
            'tenant.room_bookings.update_extra_payment' => 'Room Bookings Payment Status',
            'tenant.room_bookings.update_booking_cancel_refund' => 'Room Bookings Refund Status',
            'tenant.room_bookings.refunds' => 'Room Bookings Refund Status',
            'tenant.room_bookings.refund.delete' => 'Room Bookings Refund Status',
            'tenant.room_bookings.update_booking_status' => 'Room Bookings Booking Status',
            'tenant.room_bookings.booking_details' => 'Room Bookings Details',
            'tenant.room_bookings.booking_edit' => 'Room Bookings Edit',
            'tenant.room_bookings.booking_form' => 'Room Bookings Edit',
            'tenant.room_bookings.make_booking' => 'Room Bookings Edit',
            'tenant.room_bookings.update_booking' => 'Room Bookings Edit',
            'admin.room_bookings.booking_details_and_edit' => 'Room Bookings Edit',
            'admin.room_bookings.update_booking' => 'Room Bookings Edit',
            'tenant.rooms_management.bookings.total_rooms' => 'Room Bookings Edit',
            'tenant.room_bookings.get_booked_dates' => 'Room Bookings Edit',
            'tenant.room_bookings.send_mail' => 'Room Bookings Send Mail',
            'tenant.room_bookings.delete_booking' => 'Room Bookings Delete',
            'tenant.room_bookings.bulk_delete_booking' => 'Room Bookings Delete',
            'tenant.tickets' => 'Support Tickets',
            'tenant.ticket.messages' => 'Support Tickets',
            'tenant.ticket.store' => 'Support Tickets',
            'tenant.ticket.reply' => 'Support Tickets',
            'zip.upload' => 'Support Tickets',
            'user.profile_edit' => 'Profile',
            'user-profile-update' => 'Profile',
            'user.changePass' => 'Change Password',
            'user.updatePassword' => 'Change Password',
            'user.payment-log.index' => 'Membership',
            'user.plan.extend.index' => 'Membership',
            'user.plan.extend.checkout' => 'Membership',
            'user.plan.checkout' => 'Membership',
            'user.qrcode' => 'QR Codes',
            'user.qrcode.index' => 'QR Codes',
            'user.qrcode.delete' => 'QR Codes',
            'user.qrcode.bulk.delete' => 'QR Codes',
            'user.qrcode.generate' => 'QR Codes',
            'user.qrcode.clear' => 'QR Codes',
            'user.qrcode.save' => 'QR Codes',
            'user.qrcode.download' => 'QR Codes',
        ];

        foreach ($namedRoutes as $routeName => $permission) {
            if ($request->routeIs($routeName)) {
                return $permission;
            }
        }

        $routePatterns = [
            'user.whatsapp_failed_messages*' => 'Failed Messages',
            'user.whatsapp_message_status_update' => 'Failed Messages',
            'user.whatsapp_*' => 'Connect With Whatsapp',
            'user.whatsapp_list*' => 'Connect With Whatsapp',
            'user.configure_booking_fields' => 'Connect With Whatsapp',
            'user.whatsapp_configure_booking_fields.update' => 'Connect With Whatsapp',
            'tenant.rooms_management.*' => 'Rooms Management',
            'tenant.room_bookings.*' => 'Room Bookings',
            'user.site_settings.*' => 'General Settings',
            'user.gateway.*' => 'Payment Gateways',
            'user.offline.*' => 'Payment Gateways',
            'user.language.*' => 'Languages',
            'user.mail.*' => 'Email Settings',
            'user.mail_templates' => 'Email Settings',
            'user.edit_mail_template' => 'Email Settings',
            'user.update_mail_template' => 'Email Settings',
        ];

        foreach ($routePatterns as $routePattern => $permission) {
            if ($request->routeIs($routePattern)) {
                return $permission;
            }
        }

        return null;
    }
}
