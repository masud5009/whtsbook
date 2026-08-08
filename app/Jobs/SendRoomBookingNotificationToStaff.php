<?php

namespace App\Jobs;

use App\Models\User\RoomBooking;
use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRoomBookingNotificationToStaff implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $bookingId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $bookingId)
    {
        $this->bookingId = $bookingId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $booking = RoomBooking::query()->find($this->bookingId);

        if (!$booking) {
            return;
        }

        MailService::sendNewBookingNotificationToStaff($booking);
    }
}
