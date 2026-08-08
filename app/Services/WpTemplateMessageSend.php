<?php

namespace App\Services;

use App\Models\User\RoomBooking;
use Illuminate\Support\Facades\DB;
use App\Models\User\AutoResMessage;
use App\Http\Helpers\WhatsappSender;

class WpTemplateMessageSend
{
    /**
     * When a booking refund is processed for the customer
     */
    public static function refundMessage($booking, $wpTemplateData)
    {
        $message = AutoResMessage::where('wp_id', $booking->wp_id)
            ->where('event_type', 'refund_message')
            ->value('message');

        $status_link = route('frontend.booking_status.view', $booking->id);

        $message = str_replace('{customer_name}', $booking->customer_name, $message);
        $message = str_replace('{invoice_number}', $booking->booking_number, $message);
        $message = str_replace('{refund_amount}', $wpTemplateData['refund_amount'], $message);
        $message = str_replace('{refund_due}', $wpTemplateData['due_amount'], $message);
        $message = str_replace('{status_link}', $status_link, $message);

        WhatsappSender::sendMessage($booking->wp_id, $booking->book_from_number, $message);
    }

    /**
     * When booking price increases after update and extra payment is needed
     */
    public static function priceIncreaseMessage($booking, $amount, $paid_amount)
    {
        $message = AutoResMessage::where('wp_id', $booking->wp_id)
            ->where('event_type', 'price_increased')
            ->value('message');

        $url = route('payment.redirect', ['id' => $booking->id]);

        $extra_amount = currencySymbolPrice($amount, $booking->currency_symbol, $booking->currency_symbol_position);
        $paid_amount = currencySymbolPrice($paid_amount, $booking->currency_symbol, $booking->currency_symbol_position);

        $message = str_replace('{customer_name}', $booking->customer_name, $message);
        $message = str_replace('{invoice_number}', $booking->booking_number, $message);
        $message = str_replace('{payment_link}', $url, $message);
        $message = str_replace('{extra_amount}', $extra_amount, $message);
        $message = str_replace('{paid_amount}', $paid_amount, $message);

        WhatsappSender::sendMessage($booking->wp_id, $booking->book_from_number, $message);
    }

    /**
     * When booking price decreases after update and refund is applicable
     */
    public static function priceDecreaseMessage($booking, $refund_amount, $paid_amount)
    {
        $message = AutoResMessage::where('wp_id', $booking->wp_id)
            ->where('event_type', 'price_decreased')
            ->value('message');

        $message = str_replace('{customer_name}', $booking->customer_name, $message);
        $message = str_replace('{invoice_number}', $booking->booking_number, $message);
        $message = str_replace('{refund_amount}', $refund_amount, $message);
        $message = str_replace('{paid_amount}', $paid_amount, $message);

        WhatsappSender::sendMessage($booking->wp_id, $booking->book_from_number, $message);
    }


    /**
     * When sending payment link to customer for initial payment
     */
    public static function sendPaymentLinkMessage($booking)
    {
        $booking->send_payment_link = 1;
        $booking->save();
        $url = route('payment.redirect', ['id' => $booking->id]);
        $message = AutoResMessage::where('wp_id', $booking->wp_id)
            ->where('event_type', 'send_payment_link')
            ->value('message');

        if ($booking->customer_name) {
            $message = str_replace('{customer_name}', $booking->customer_name, $message);
        }
        if ($booking->booking_number) {
            $message = str_replace('{invoice_number}', $booking->booking_number, $message);
        }
        $message = str_replace('{payment_link}', $url, $message);
        WhatsappSender::sendMessage($booking->wp_id, $booking->book_from_number, $message);
    }

    /**
     * When booking is placed from whatsapp without payment
     */
    public static function paymentCompleteMessage($booking_id)
    {
        $booking = RoomBooking::findOrFail($booking_id);

        if (is_null($booking->reserved_dates_info) || $booking->reserved_dates_info == '[]') {
            $message = AutoResMessage::where('wp_id', $booking->wp_id)
                ->where('event_type', 'payment_complete')
                ->value('message');

            if ($booking->customer_name) {
                $message = str_replace('{customer_name}', $booking->customer_name, $message);
            }
            WhatsappSender::sendMessage($booking->wp_id, $booking->book_from_number, $message);
        } else {
            self::sendRoomAssignMessage($booking_id);
        }
    }

    /**
     * When payment is complete and admin assigns rooms
     */
    public static function sendRoomAssignMessage($booking_id)
    {
        $booking = RoomBooking::findOrFail($booking_id);

        $bs = DB::table('user_basic_settings')
            ->where('user_id', $booking->user_id)
            ->select('base_currency_symbol', 'base_currency_symbol_position')
            ->first();

        $symbol   = $bs?->base_currency_symbol ?? '';
        $position = $bs?->base_currency_symbol_position ?? 'left';

        $line = str_repeat('—', 22);

        $replyText  = "*Booking Confirmation*\n";
        $replyText .= "{$line}\n\n";

        $replyText .= "*Name:* {$booking->customer_name}\n";
        $replyText .= "*Email:* {$booking->customer_email}\n";
        $replyText .= "*Arrival Date:* {$booking->arrival_date}\n";
        $replyText .= "*Departure Date:* {$booking->departure_date}\n";

        if ($booking->due > 0) {
            $replyText .= "*Due:* " . currencyTextPrice($booking->due, $symbol, $position) . "\n";
        } else {
            $replyText .= "*Paid:* " . currencyTextPrice($booking->paid_amount, $symbol, $position) . "\n";
        }

        $replyText .= "\n*Reserved Dates*\n";

        $reserved = collect(json_decode($booking->reserved_dates_info, true) ?? [])
            ->sortBy('date')
            ->map(function ($item) {
                $date = \Carbon\Carbon::parse($item['date'])->format('d M, Y');
                $room = $item['room_number'] ?? $item['room_no'] ?? $item['room_numberno'] ?? 'N/A';
                return "• {$date} — Room: {$room}";
            })
            ->values()
            ->all();

        if (!empty($reserved)) {
            $replyText .= implode("\n", $reserved) . "\n";
        }

        $routelink = route('frontend.booking_status.view', ['id' => $booking->id]);
        $replyText .= "\n*For More Details:* {$routelink}\n";


        WhatsappSender::sendMessage($booking->wp_id, $booking->book_from_number, $replyText);
    }
}
