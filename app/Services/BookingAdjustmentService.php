<?php

namespace App\Services;

use App\Models\User\RoomBooking;
use App\Models\User\BookingAdjustment;
use App\Services\WpTemplateMessageSend;

class BookingAdjustmentService
{
    /**
     * This function will be use when create a booking for the first time and
     * store the grand total in booking adjustment table with type initial.
     */
    public static function store($booking)
    {
        return BookingAdjustment::create([
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'grand_total' => $booking->grand_total,
            'type' => 'initial'
        ]);
    }

    /**
     * This function will be use when update a booking
     * -It will check if the grand total is changed or not
     * -If changed then it will update the grand total in booking adjustment table.
     */
    public static function update($booking_id)
    {
        $booking = RoomBooking::find($booking_id);
        $adjustment = BookingAdjustment::where('booking_id', $booking_id)->first();
        // if adjustment not found then create a new one
        if (!$adjustment) {
            $adjustment =  self::store($booking);
        }

        if ($booking->grand_total != $adjustment->grand_total && $booking->due == 0) {
            // if grand total is less than the adjustment then it will be a refund
            if ($booking->grand_total < $adjustment->grand_total) {
                $new_adjustment = $adjustment->grand_total - $booking->grand_total;
                $adjustment->amount = $new_adjustment;
                $adjustment->type = "refund";
                $adjustment->save();

                if ($booking->source == 'whatsapp_bot') {
                    WpTemplateMessageSend::priceDecreaseMessage($booking, $new_adjustment, $adjustment->grand_total);
                }
            }

            // if grand total is greater than the adjustment then it will be an extra payment
            if ($booking->grand_total > $adjustment->grand_total) {
                $new_adjustment = $booking->grand_total - $adjustment->grand_total;
                $adjustment->amount = $new_adjustment;
                $adjustment->type = "extra_payment";
                $adjustment->save();


                $booking->due = 0;
                $booking->partial_amount = $booking->paid_amount;
                $booking->payment_status = 2; // partial payment
                $booking->save();

                if ($booking->source == 'whatsapp_bot') {
                    WpTemplateMessageSend::priceIncreaseMessage($booking, $new_adjustment, $adjustment->grand_total);
                }
            }
        } else {
            // if grand total is same as the adjustment then it will be initial
            if ($booking->grand_total == $adjustment->grand_total && $booking->due == 0) {
                $adjustment->amount = 0;
                $adjustment->type = "initial";
                $adjustment->save();

                $booking->partial_amount = 0;
                $booking->payment_status = 1; // partial payment
                $booking->save();
            }
            //If grand total is same as adjustment then send direct payment link to customer
            if ($booking->source == 'whatsapp_bot') {
                WpTemplateMessageSend::sendRoomAssignMessage($booking->id);
            }
        }
        session()->forget('previous_paid_amount');
        return true;
    }

    /**
     * This function will be use when there is an extra payment and the payment is successful then it
     * will update the grand total in booking adjustment table and change the type to initial.
     */
    public static function updateForExtraPayment($booking)
    {
        $adjustment = BookingAdjustment::where('booking_id', $booking->id)->first();
        if ($adjustment && $adjustment->type == 'extra_payment') {
            $adjustment->grand_total = $adjustment->grand_total + $adjustment->amount;
            $adjustment->amount = 0;
            $adjustment->type = 'initial';
            $adjustment->save();
        }
        return true;
    }

    /**
     * This function will be use when there is a refund and the refund is successful then
     * it will update the grand total in booking adjustment table and change the type to initial.
     */
    public static function updateForRefund($booking, $refund_amount)
    {
        $adjustment = BookingAdjustment::where('booking_id', $booking->id)->first();
        $amount = abs($adjustment->amount - $refund_amount);

        if ($adjustment && $adjustment->type == 'refund') {
            $adjustment->amount = $amount;
            $adjustment->grand_total = $adjustment->grand_total - $refund_amount;
            $adjustment->type = $amount > 0 ? 'refund' : 'initial';
            $adjustment->save();
        }

        if ($booking->source == 'whatsapp_bot') {
            $wpTemplateData = [
                'refund_amount' => currencySymbolPrice($refund_amount, $booking->currency_symbol, $booking->currency_symbol_position),
                'due_amount' => currencySymbolPrice($amount, $booking->currency_symbol, $booking->currency_symbol_position)
            ];
            // send refund message to customer on whatsapp
            WpTemplateMessageSend::refundMessage($booking, $wpTemplateData);
        }

        return true;
    }

    /**
     * This function will be use when there is a refund and the refund is failed then
     * it will update the grand total in booking adjustment table and change the type to refund.
     */
    public static function revertRefund($refund_amount, $booking_id)
    {
        $adjustment = BookingAdjustment::where('booking_id', $booking_id)->first();
        $amount = $adjustment->amount + $refund_amount;

        if ($adjustment) {
            $adjustment->amount = $amount;
            $adjustment->type = 'refund';
            $adjustment->save();
        }
        return true;
    }
}
