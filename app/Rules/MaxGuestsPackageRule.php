<?php

namespace App\Rules;

use App\Models\User\Package;
use Illuminate\Contracts\Validation\Rule;

class MaxGuestsPackageRule implements Rule
{
    protected $packageId;

    /**
     * Create a new rule instance.
     *
     * @param  int  $packageId
     * @return void
     */
    public function __construct($packageId)
    {
        $this->packageId = $packageId;
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

        $package = Package::find($this->packageId);
        if ($package->max_persons == null) {
            return true;
        }

        return $package && $value <= $package->max_persons;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The number of guests exceeds the maximum allowed for this package.';
    }
}
