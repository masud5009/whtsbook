<?php
namespace App\Http\Helpers;

use App\Models\Language;
use Illuminate\Support\Str;
use Illuminate\Mail\Message;
use App\Models\BasicExtended;
use App\Models\EmailTemplate;
use App\Models\User\BasicSetting;
use App\Models\User\MailTemplate;
use App\Traits\MiscellaneousTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class MegaMailer
{
    use MiscellaneousTrait;

    public function mailFromAdmin($data)
    {
        $temp = EmailTemplate::where('email_type', '=', $data['templateType'])->first();

        $body = $temp->email_body;
        if (array_key_exists('username', $data)) {
            $body = preg_replace("/{username}/", $data['username'], $body);
        }

        if (array_key_exists('replaced_package', $data)) {
            $body = preg_replace("/{replaced_package}/", $data['replaced_package'], $body);
        }
        if (array_key_exists('removed_package_title', $data)) {
            $body = preg_replace("/{removed_package_title}/", $data['removed_package_title'], $body);
        }
        if (array_key_exists('package_title', $data)) {
            $body = preg_replace("/{package_title}/", $data['package_title'], $body);
        }
        if (array_key_exists('package_price', $data)) {
            $body = preg_replace("/{package_price}/", $data['package_price'], $body);
        }
        if (array_key_exists('discount', $data)) {
            $body = preg_replace("/{discount}/", $data['discount'], $body);
        }
        if (array_key_exists('total', $data)) {
            $body = preg_replace("/{total}/", $data['total'], $body);
        }
        if (array_key_exists('activation_date', $data)) {
            $body = preg_replace("/{activation_date}/", $data['activation_date'], $body);
        }
        if (array_key_exists('expire_date', $data)) {
            $body = preg_replace("/{expire_date}/", $data['expire_date'], $body);
        }
        if (array_key_exists('requested_domain', $data)) {
            $body = preg_replace("/{requested_domain}/", "<a href='http://" . $data['requested_domain'] . "'>" . $data['requested_domain'] . "</a>", $body);
        }
        if (array_key_exists('previous_domain', $data)) {
            $body = preg_replace("/{previous_domain}/", "<a href='http://" . $data['previous_domain'] . "'>" . $data['previous_domain'] . "</a>", $body);
        }
        if (array_key_exists('current_domain', $data)) {
            $body = preg_replace("/{current_domain}/", "<a href='http://" . $data['current_domain'] . "'>" . $data['current_domain'] . "</a>", $body);
        }
        if (array_key_exists('subdomain', $data)) {
            $body = preg_replace("/{subdomain}/", "<a href='http://" . $data['subdomain'] . "'>" . $data['subdomain'] . "</a>", $body);
        }
        if (array_key_exists('last_day_of_membership', $data)) {
            $body = preg_replace("/{last_day_of_membership}/", $data['last_day_of_membership'], $body);
        }
        if (array_key_exists('login_link', $data)) {
            $body = preg_replace("/{login_link}/", $data['login_link'], $body);
        }
        if (array_key_exists('customer_name', $data)) {
            $body = preg_replace("/{customer_name}/", $data['customer_name'], $body);
        }
        if (array_key_exists('verification_link', $data)) {
            $body = preg_replace("/{verification_link}/", $data['verification_link'], $body);
        }
        if (array_key_exists('website_title', $data)) {
            $body = preg_replace("/{website_title}/", $data['website_title'], $body);
        }

        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $be = $currentLang->basic_extended;
        if ($this->isValidate($be) == false) {
            Session::flash('error', 'SMTP credentials are not set properly.');
            return back();
        }

        $this->setSmtp($be);
        $data['be']       = $be;
        $data['body']     = $body;
        $data['subject']  = $temp->email_subject;
        $data['fromName'] = $be->from_name;
        $data['fromMail'] = $be->from_mail;
        $data['replyMail'] = $be->to_mail;

        try {
            Mail::send([], [], function (Message $message) use ($data) {
                $message->to($data['toMail'])
                    ->from($data['fromMail'], $data['fromName'])
                    ->subject($data['subject'])
                    ->replyTo($data['replyMail'], $data['fromName'])
                    ->html($data['body'], 'text/html');

                if (array_key_exists('membership_invoice', $data)) {
                    $message->attach(public_path('assets/front/invoices/') . $data['membership_invoice']);
                }
            });
        } catch (\Exception $e) {
            Session::flash('warning', 'Mail could not be sent. Mailer Error: ' . Str::limit($e->getMessage(), 30));
            return back();
        }
    }

    public function mailToAdmin($data)
    {
        $be = BasicExtended::query()->first();
        if ($this->isValidate($be) == false) {
            Session::flash('error', 'SMTP credentials are not set properly.');
            return back();
        }
        $this->setSmtp($be);
        try {
            Mail::send([], [], function ($message) use ($data, $be) {
                $fromMail = $be->from_mail;
                $fromName = $be->from_name;
                $message->to($be->to_mail)
                    ->subject($data['subject'])
                    ->from($fromMail, $fromName)
                    ->html($data['body'], 'text/html');
                if (array_key_exists('attachments', $data)) {
                    $message->attach(public_path('assets/front/invoices/') . $data['attachments']); // Add
                }
            });
            Session::flash('success', __('Message Sent Successfully'));
        } catch (\Exception $e) {
            Session::flash('warning', 'Mail could not be sent. Mailer Error: ' . Str::limit($e->getMessage(), 30));
            return back();
        }
    }

    public function mailContactMessage($data)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $be = $currentLang->basic_extended;

        if (Auth::guard('web')->check()) {
            $id = Auth::guard('web')->user()->id;
        }
        $userBs = BasicSetting::where('user_id', $id)->first();

        if ($this->isValidate($be) == false) {
            Session::flash('error', 'SMTP credentials are not set properly.');
            return back();
        }
        $this->setSmtp($be);

        try {
            Mail::send([], [], function (Message $message) use ($data, $be, $userBs) {
                $fromMail = $be->from_mail;
                $fromName = $userBs->from_name;
                $message->to($userBs->email)
                    ->subject($data['subject'])
                    ->from($fromMail, $fromName)
                    ->html($data['body'], 'text/html');
            });
        } catch (\Exception $e) {
            Session::flash('warning', 'Mail could not be sent. Mailer Error: ' . Str::limit($e->getMessage(), 30));
            return back();
        }
    }

    public function mailFromUser($data, $user)
    {

        $temp = MailTemplate::query()
            ->where([
                ['mail_type', '=', $data['templateType']],
                ['user_id', '=', $user->id],
            ])
            ->first();

        $body = $temp->mail_body;

        if (array_key_exists('username', $data)) {
            $body = preg_replace("/{customer_username}/", $data['username'], $body);
        }
        if (array_key_exists('customer_name', $data)) {
            $body = preg_replace("/{customer_name}/", $data['customer_name'], $body);
        }
        if (array_key_exists('booking_number', $data)) {
            $body = preg_replace("/{booking_number}/", $data['booking_number'], $body);
        }
        if (array_key_exists('booking_date', $data)) {
            $body = preg_replace("/{booking_date}/", $data['booking_date'], $body);
        }
        if (array_key_exists('number_of_night', $data)) {
            $body = preg_replace("/{number_of_night}/", $data['number_of_night'], $body);
        }
        if (array_key_exists('check_in_date', $data)) {
            $body = preg_replace("/{check_in_date}/", $data['check_in_date'], $body);
        }
        if (array_key_exists('check_out_date', $data)) {
            $body = preg_replace("/{check_out_date}/", $data['check_out_date'], $body);
        }
        if (array_key_exists('number_of_guests', $data)) {
            $body = preg_replace("/{number_of_guests}/", $data['number_of_guests'], $body);
        }
        if (array_key_exists('room_name', $data)) {
            $body = preg_replace("/{room_name}/", $data['room_name'], $body);
        }
        if (array_key_exists('room_rent', $data)) {
            $body = preg_replace("/{room_rent}/", $data['room_rent'], $body);
        }
        if (array_key_exists('room_type', $data)) {
            $body = preg_replace("/{room_type}/", $data['room_type'], $body);
        }
        if (array_key_exists('room_amenities', $data)) {
            $body = preg_replace("/{room_amenities}/", $data['room_amenities'], $body);
        }
        if (array_key_exists('discount', $data)) {
            $body = preg_replace("/{discount}/", $data['discount'], $body);
        }
        if (array_key_exists('package_name', $data)) {
            $body = preg_replace("/{package_name}/", $data['package_name'], $body);
        }
        if (array_key_exists('package_price', $data)) {
            $body = preg_replace("/{package_price}/", $data['package_price'], $body);
        }
        if (array_key_exists('number_of_visitors', $data)) {
            $body = preg_replace("/{number_of_visitors}/", $data['number_of_visitors'], $body);
        }
        if (array_key_exists('total', $data)) {
            $body = preg_replace("/{total}/", $data['total'], $body);
        }

        if (array_key_exists('login_link', $data)) {
            $body = preg_replace("/{login_link}/", $data['login_link'], $body);
        }

        if (array_key_exists('verification_link', $data)) {
            $body = preg_replace("/{verification_link}/", $data['verification_link'], $body);
        }
        if (array_key_exists('password_reset_link', $data)) {
            $body = preg_replace("/{password_reset_link}/", $data['password_reset_link'], $body);
        }
        if (array_key_exists('website_title', $data)) {
            $body = preg_replace("/{website_title}/", $data['website_title'], $body);
        }

        if (session()->has('lang')) {
            $currentLang = Language::query()->where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::query()->where('is_default', 1)->first();
        }
        $be = $currentLang->basic_extended;

        $userBs = BasicSetting::where('user_id', $user->id)->first();

        if ($this->isValidate($be) == false) {
            Session::flash('error', 'SMTP credentials are not set properly.');
            return back();
        }
        $this->setSmtp($be);

        try {
            Mail::send([], [], function ($message) use ($data, $body, $temp, $be, $userBs) {
                $fromMail = $be->from_mail;
                $fromName = $userBs->from_name;
                $message->to($data['toMail'])
                    ->subject($temp->mail_subject)
                    ->from($fromMail, $fromName)
                    ->replyTo($userBs->reply_to, $userBs->from_name)
                    ->html($body, 'text/html');
                if (array_key_exists('invoice', $data)) {
                    $message->attach($data['invoice']);
                }
            });
        } catch (\Exception $e) {
            Session::flash('warning', 'Mail could not be sent. Mailer Error: ' . Str::limit($e->getMessage(), 30));
            return back();
        }
    }

    public function isValidate($be)
    {
        if (empty($be->smtp_host) || empty($be->smtp_port) || empty($be->encryption) || empty($be->smtp_username) || empty($be->smtp_password)) {
            return false;
        }
        return true;

    }

    public function setSmtp($be)
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
}
