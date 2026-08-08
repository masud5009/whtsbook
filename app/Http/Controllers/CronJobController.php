<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Membership;
use App\Models\BasicSetting;
use App\Models\BasicExtended;
use App\Models\PaymentGateway;
use App\Models\User\RoomBooking;
use App\Http\Controllers\Controller;
use App\Jobs\SubscriptionExpiredMail;
use App\Jobs\SubscriptionReminderMail;
use App\Services\WpTemplateMessageSend;
use App\Http\Helpers\UserPermissionHelper;
use App\Http\Controllers\PaymentLinkController;
use App\Models\User\PaymentGateway as UserPaymentGeteway;

class CronJobController extends Controller
{
    /**
     * Handle expired memberships and send reminder emails for upcoming expirations.
     */
    public function expired()
    {
        $bs = BasicSetting::first();
        $be = BasicExtended::first();
        $exMembers = Membership::whereDate('expire_date', Carbon::now()->subDays(1))->get();

        foreach ($exMembers as $key => $exMember) {
            if (!empty($exMember->user)) {
                $user = $exMember->user;
                $currPackage = UserPermissionHelper::userPackage($user->id);

                if (is_null($currPackage)) {
                    SubscriptionExpiredMail::dispatch($user, $bs, $be);
                }
            }
        }
        $rmdMembers = Membership::whereDate('expire_date', Carbon::now()->addDays($be->expiration_reminder))->get();
        foreach ($rmdMembers as $key => $rmdMember) {
            if (!empty($rmdMember->user)) {
                $user = $rmdMember->user;
                $nextPackageCount = Membership::query()->where([
                    ['user_id', $user->id],
                    ['start_date', '>', Carbon::now()->toDateString()]
                ])->where('status', '<>', 2)->count();

                if ($nextPackageCount == 0) {
                    SubscriptionReminderMail::dispatch($user, $bs, $be, $rmdMember->expire_date);
                }
            }
        }

        \Artisan::call("queue:work --stop-when-empty");
    }

    /**
     * Check Iyzico pending payments
     */
    public function check_payment()
    {
        //check iyzico pending payments
        $iyzico_pending_memberships = Membership::where([['status', 0], ['payment_method', 'Iyzico']])->get();
        foreach ($iyzico_pending_memberships as $iyzico_pending_membership) {
            if (!is_null($iyzico_pending_membership->conversation_id)) {
                $result = $this->IyzicoPaymentStatus('admin', null, $iyzico_pending_membership->conversation_id);
                $membership = Membership::where('id', $iyzico_pending_membership->id)->first();
                if ($result == 'success') {
                    $membership->status = 1;
                    $membership->save();
                } else {
                    $membership->status = 0;
                    $membership->save();
                }
            }
        }

        //check iyzico pending payments
        $iyzico_pending_bookings = RoomBooking::where([['iyzico_payment_status', 1], ['payment_method', 'Iyzico']])->get();
        foreach ($iyzico_pending_bookings as $iyzico_pending_booking) {
            if (!is_null($iyzico_pending_booking->conversation_id)) {
                $result = $this->IyzicoPaymentStatus('user', $iyzico_pending_booking->user_id, $iyzico_pending_booking->conversation_id);
                if ($result == 'success') {
                    try {
                        $booking = RoomBooking::where('id', $iyzico_pending_booking->id)->first();

                        if ($booking) {
                            $user  = User::where('id', $booking->user_id)->first();
                            if ($user) {
                                $booking->iyzico_payment_status = 2;
                                $booking->save();

                               WpTemplateMessageSend::paymentCompleteMessage($booking->id);
                            }
                        }
                    } catch (\Exception $th) {
                        //throw $th;
                    }
                } else {
                    try {
                        $booking = RoomBooking::where('id', $iyzico_pending_booking->id)->first();

                        if ($booking) {
                            $user  = User::where('id', $booking->user_id)->first();
                            if ($user) {
                                $booking->iyzico_payment_status = 3; // payment failed
                                $booking->save();
                            }
                        }
                    } catch (\Exception $th) {
                        //throw $th;
                    }
                }
            }
        }
    }

    /**
     * Check Iyzico payment status using the conversation ID and update the booking status accordingly.
     */
    private function IyzicoPaymentStatus($type, $user_id, $conversation_id)
    {
        if ($type == 'admin') {
            $paymentMethod = PaymentGateway::where('keyword', 'iyzico')->first();
            $paydata = $paymentMethod->convertAutoData();
        } else {
            $paymentMethod = UserPaymentGeteway::where([['user_id', $user_id], ['keyword', 'iyzico']])->first();
            $paydata = json_decode($paymentMethod->information, true);
        }

        $options = new \Iyzipay\Options();
        $options->setApiKey($paydata['api_key']);
        $options->setSecretKey($paydata['secret_key']);
        if ($paydata['sandbox_status'] == 1) {
            $options->setBaseUrl("https://sandbox-api.iyzipay.com");
        } else {
            $options->setBaseUrl("https://api.iyzipay.com"); // production mode
        }

        $request = new \Iyzipay\Request\ReportingPaymentDetailRequest();
        $request->setPaymentConversationId($conversation_id);

        $paymentResponse = \Iyzipay\Model\ReportingPaymentDetail::create($request, $options);
        $result = (array) $paymentResponse;

        foreach ($result as $key => $data) {
            $data = json_decode($data, true);
            if ($data['status'] == 'success' && !empty($data['payments'])) {
                if (is_array($data['payments'])) {
                    if ($data['payments'][0]['paymentStatus'] == 1) {
                        return 'success';
                    } else {
                        return 'not found';
                    }
                } else {
                    return 'not found';
                }
            } else {
                return 'not found';
            }
        }
        return 'not found';
    }
}
