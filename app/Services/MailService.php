<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\User\Staff;
use App\Constants\Constant;
use App\Models\User\Refund;
use Illuminate\Support\Str;
use App\Models\User\Language;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User\RoomBooking;
use App\Models\User\BasicSetting;
use App\Models\User\MailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\User\BookingAdjustment;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class MailService
{
    public static function sendNewBookingNotificationToStaff(RoomBooking $booking, ?array $bookings = null): void
    {
        $be = DB::table('basic_extendeds')
            ->select(
                'is_smtp',
                'from_mail',
                'smtp_host',
                'smtp_port',
                'encryption',
                'smtp_username',
                'smtp_password',
            )
            ->first();

        if (!$be) {
            return;
        }

        if ((int) $be->is_smtp === 0) {
            return;
        }

        if (self::isValidate($be) === false) {
            return;
        }

        $recipientStaffs = self::roomBookingNotificationRecipients($booking->user_id);

        if ($recipientStaffs->isEmpty()) {
            return;
        }

        $currentLang = self::userDashboardLanguage($booking->user_id);

        if (!$currentLang) {

            $currentLang = Language::query()
                ->where('user_id', $booking->user_id)
                ->first();
        }

        $bs = BasicSetting::where('user_id', $booking->user_id)
            ->select('from_name', 'reply_to', 'website_title')
            ->first();

        if (!$currentLang) {
            return;
        }

        if (!$bs) {
            return;
        }

        $bookingLinksHtml = null;
        if (is_array($bookings) && count($bookings) > 1) {
            $links = [];

            foreach ($bookings as $index => $item) {
                if (!$item || !isset($item->id)) {
                    continue;
                }

                $url = route('tenant.room_bookings.booking_edit', ['id' => $item->id]);

                $links[] = '<a href="' . $url . '">Booking ' . ($index + 1) . '</a>';
            }

            if (!empty($links)) {
                $bookingLinksHtml = implode('<br>', $links);
            }
        }

        try {
            self::setSmtp($be);
        } catch (\Throwable $e) {
            return;
        }

        foreach ($recipientStaffs as $recipientStaff) {
            if (empty($recipientStaff->email)) {
                continue;
            }

            $mailData = self::buildAndSendTemplateMail(
                $booking,
                $booking->user_id,
                'room_booking_payment_received_for_staff',
                $currentLang->id,
                $bs,
                $bookingLinksHtml,
                $recipientStaff->name
            );

            if (empty($mailData['template'])) {
                continue;
            }

            try {
                Mail::send([], [], function ($message) use ($mailData, $be, $bs, $recipientStaff) {
                    $message->to($recipientStaff->email, $recipientStaff->name)
                        ->subject($mailData['template']->mail_subject)
                        ->from($be->from_mail, $bs->from_name)
                        ->replyTo($bs->reply_to, $bs->from_name)
                        ->html($mailData['body'], 'text/html');
                });

            } catch (\Throwable $e) {
                continue;
            }
        }
    }
    /**
     * Send Mail
     */
    public static function sendBookingMail($booking, $mail_type, $bookings = null)
    {
        $be = DB::table('basic_extendeds')
            ->select(
                'is_smtp',
                'from_mail',
                'smtp_host',
                'smtp_port',
                'encryption',
                'smtp_username',
                'smtp_password',
            )
            ->first();

        if (!$be || (int) $be->is_smtp === 0 || self::isValidate($be) === false) {
            return;
        }


        $user_id = $booking->user_id;
        $user = User::find($user_id);
        if (!$user) return;

        $currentLang = self::userDashboardLanguage($user_id);
        $bs = BasicSetting::where('user_id', $user->id)->select('from_name', 'reply_to', 'website_title')->first();

        $bookingLinksHtml = null;
        if (is_array($bookings) && count($bookings) > 1) {
            $links = [];
            foreach ($bookings as $index => $item) {
                if (!$item || !isset($item->id)) {
                    continue;
                }
                $links[] = '<a href="' . route('tenant.room_bookings.booking_edit', ['id' => $item->id]) . '">Booking ' . ($index + 1) . '</a>';
            }
            if (!empty($links)) {
                $bookingLinksHtml = implode('<br>', $links);
            }
        }

        $mailData = self::buildAndSendTemplateMail($booking, $user_id, $mail_type, $currentLang->id, $bs, $bookingLinksHtml);


        // Check SMTP credentials
        if (self::isValidate($be) == false) {
            Session::flash('error', 'SMTP credentials are not set properly.');
            return back();
        }

        // Set SMTP config
        self::setSmtp($be);

        // Send mail

        try {
            Mail::send([], [], function ($message) use ($mailData, $be, $bs, $booking) {
                $fromMail = $be->from_mail;
                $fromName = $bs->from_name;
                $message->to($booking->customer_email)
                    ->subject($mailData['template']->mail_subject)
                    ->from($fromMail, $fromName)
                    ->replyTo($bs->reply_to, $bs->from_name)
                    ->html($mailData['body'], 'text/html');

                // Attach invoice if it exists
                if ($booking->invoice && file_exists($booking->invoice)) {
                    $message->attach($booking->invoice);
                }
            });
        } catch (\Exception $e) {
            Session::flash('warning', 'Mail could not be sent. Mailer Error: ' . Str::limit($e->getMessage(), 30));
            return back();
        }
    }


    /**
     * Build mail body using template and send mail to customer
     */
    public static function buildAndSendTemplateMail($booking, $user_id, $mail_type, $languageId, $bs, $bookingLinksHtml = null, ?string $staffName = null)
    {
        $template = MailTemplate::where([
            ['mail_type', '=', $mail_type],
            ['user_id', '=', $user_id],
        ])
            ->first();

        if (!$template) {
            return [
                'body' => '',
                'template' => null,
            ];
        }

        $body = $template->mail_body;
        $currency = $booking->currency_text;
        $currency_position = $booking->currency_text_position;

        //notify admin about new booking with booking link and pending booking link if any unassigned room is available
        if ($mail_type == 'new_booking_notification' || $mail_type == 'room_booking_payment_received_for_staff') {
            $booking_link = $bookingLinksHtml ?: route('tenant.room_bookings.booking_edit', ['id' => $booking->id]);
            $body = str_replace('{booking_link}', $booking_link, $body);

            $isAnyUnassignedRoomAvailable = RoomBooking::where('user_id', $user_id)
                ->where(function ($query) {
                    $query->whereNull('reserved_dates_info')
                        ->orWhere('reserved_dates_info', '')
                        ->orWhere('reserved_dates_info', '[]');
                })
                ->exists();
            if ($isAnyUnassignedRoomAvailable) {
                $pending_booking_link = route('tenant.room_bookings.all_bookings') . '?unassigned=1';
                $body = str_replace('{pending_booking_link}', $pending_booking_link, $body);
            }
        }

        // Replace placeholders with actual data
        if ($booking->booking_number) {
            $body = str_replace('{booking_number}', $booking->booking_number, $body);
        }

        if ($booking->created_at) {
            $booking_date = date('d M Y', strtotime($booking->created_at));
            $body = str_replace('{booking_date}', $booking_date, $body);
        }

        if ($booking->arrival_date && $booking->departure_date) {
            $start = Carbon::parse($booking->arrival_date);
            $end = Carbon::parse($booking->departure_date)->subDay();
            $interval = $start->diffInDays($end) + 1;
            $body = str_replace('{number_of_night}', $interval, $body);
        }

        if ($booking->arrival_date) {
            $arrival_date = date('d M Y', strtotime($booking->arrival_date));
            $body = str_replace('{check_in_date}', $arrival_date, $body);
        }

        if ($booking->customer_name) {
            $body = str_replace('{customer_name}', $booking->customer_name, $body);
        }

        if ($booking->departure_date) {
            $departure_date = date('d M Y', strtotime($booking->departure_date));
            $body = str_replace('{check_out_date}', $departure_date, $body);
        }

        if ($booking->adult !== null) {
            $guestCount = (int) $booking->adult + (int) $booking->child;
            $body = str_replace('{number_of_guests}', $guestCount, $body);
        }

        if ($booking->room_category_id) {
            $roomName = DB::table('user_room_category_contents')
                ->where('room_id', $booking->room_category_id)
                ->where('language_id', $languageId)
                ->value('title');
            $body = str_replace('{room_name}', $roomName, $body);
        }

        if ($booking->grand_total) {
            $grandTotal = currencyTextPrice($booking->grand_total, $currency, $currency_position);
            $body = str_replace('{room_rent}', $grandTotal, $body);
        }

        if ($staffName !== null) {
            $body = str_replace('{staff_name}', $staffName, $body);
        }

        $body = str_replace('{website_title}', $bs->website_title, $body);
        return [
            'body' => $body,
            'template' => $template
        ];
    }

    private static function roomBookingNotificationRecipients(int $userId)
    {
        return Staff::query()
            ->where('user_id', $userId)
            ->with('roleInfo:id,permissions')
            ->get()
            ->filter(function (Staff $staff) {
                $permissions = $staff->roleInfo?->permissions;

                if (!is_array($permissions)) {
                    return false;
                }

                foreach ($permissions as $permission) {
                    if (
                        $permission === 'Room Bookings' ||
                        str_starts_with((string) $permission, 'Room Bookings ')
                    ) {
                        return true;
                    }
                }

                return false;
            })
            ->filter(function (Staff $staff) {
                return is_string($staff->email) && filter_var($staff->email, FILTER_VALIDATE_EMAIL);
            })
            ->unique('email')
            ->values()
        ;
    }

    /**
     * Get user dashboard language
     */
    public static function userDashboardLanguage($userId)
    {
        $userDashboardLang = null;
        if (Session::has('user_lang')) {
            $userDashboardLang = Language::where('user_id', $userId)->where('code', Session::get('user_lang'))->first();
        }
        if (is_null($userDashboardLang)) {
            $userDashboardLang = Language::where('user_id', $userId)->where('dashboard_default', 1)->first();
        }
        return $userDashboardLang;
    }

    /**
     * Check if SMTP credentials are set properly
     */
    public static function isValidate($be)
    {
        if (empty($be->smtp_host) || empty($be->smtp_port) || empty($be->encryption) || empty($be->smtp_username) || empty($be->smtp_password)) {
            return false;
        }
        return true;
    }

    /**
     * Set SMTP configuration at runtime
     */
    public static function setSmtp($be)
    {
        if ($be->is_smtp == 1) {
            try {
                $smtp = [
                    'transport'  => 'smtp',
                    'host'       => $be->smtp_host,
                    'port'       => $be->smtp_port,
                    'encryption' => $be->encryption,
                    'username'   => $be->smtp_username,
                    'password'   => $be->smtp_password,
                    'timeout'    => null,
                    'auth_mode'  => null,
                ];
                Config::set('mail.mailers.smtp', $smtp);
            } catch (\Exception $e) {
                Session::flash('warning', Str::limit($e->getMessage(), 100, '...'));
                return back();
            }
        }
    }


    /**
     * Generate booking invoice in pdf format and return the file name
     */
    public static function generateBookingInvocie($bookingInfo)
    {
        $user = User::where('id', $bookingInfo->user_id)->first();

        $bs = DB::table('user_basic_settings')
            ->where('user_id', $user->id)
            ->select('support_email', 'support_contact', 'address')
            ->first();

        $userBs = DB::table('user_basic_settings')
            ->where('user_id', $user->id)
            ->select('primary_color', 'logo', 'room_tax_status', 'room_fee_status')
            ->first();

        $fileName = $bookingInfo->booking_number . '.pdf';
        $directory = public_path(Constant::WEBSITE_ROOM_BOOKING_INVOICE . '/');
        @mkdir($directory, 0777, true);
        $language = Language::query()->where('is_default', '=', 1)->where('user_id', '=', $user->id)->first();
        $fileLocated = $directory . $fileName;
        $keywords = get_keywords($user->id);


        $raw = json_decode($bookingInfo->reserved_dates_info, true) ?? [];
        $reserved_dates_info = collect($raw)
            ->sortBy('date')
            ->groupBy('date')
            ->map(function ($items, $date) {
                return [
                    'date' => \Carbon\Carbon::parse($date)->format('d M, Y'),
                    'rooms' => $items->map(function ($item) {
                        $room = $item['room_number'] ?? $item['room_no'] ?? $item['room_numberno'] ?? 'N/A';
                        return ['room_number' => $room];
                    })->toArray()
                ];
            })
            ->values();

        $bookingAdjustment = BookingAdjustment::where('booking_id', $bookingInfo->id)->first();
        $refund = Refund::where('booking_id', $bookingInfo->id)->first();

        Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'chroot'               => public_path(),
            'logOutputFile'        => storage_path('logs/log.htm'),
            'tempDir'              => storage_path('logs/'),
        ])->loadView('user.pdf.room_booking', compact('bookingInfo', 'language', 'keywords', 'user', 'bs', 'userBs', 'reserved_dates_info', 'bookingAdjustment', 'refund'))
            ->save($fileLocated);

        return $fileName;
    }
}
