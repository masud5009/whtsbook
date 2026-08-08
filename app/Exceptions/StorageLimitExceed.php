<?php
namespace App\Exceptions;

use Exception;

class StorageLimitExceed extends Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report(): void
    {
        //
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request): \Illuminate\Http\JsonResponse
    {
        return response()->json(["exception" => $this->getMessage()],400);
    }
}
