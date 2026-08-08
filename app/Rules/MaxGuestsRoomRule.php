<?php

namespace App\Rules;

use App\Models\User\Room;
use Illuminate\Contracts\Validation\Rule;

class MaxGuestsRoomRule implements Rule
{
    protected $roomId;

    /**
     * Create a new rule instance.
     *
     * @param  int  $roomId
     * @return void
     */
    public function __construct($roomId)
    {
        $this->roomId = $roomId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        $room = Room::find($this->roomId);
        if ($room->max_guests == null) {
            return true;
        }

        return $room && $value <= $room->max_guests;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The number of guests exceeds the maximum allowed for this room.');
    }
}
