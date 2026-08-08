<?php

namespace App\Services\Payment;

use Illuminate\Http\Request;
use App\Models\User\OfflineGateway;
use App\Models\OfflineGateway as adminOfflineGateway;

class OfflinePaymentInstruction
{

    public static function getInstruction(Request $request)
    {
        if ($request->user_id == null) {
            $offline = adminOfflineGateway::where('id', $request->name)
                ->where('status', 1)
                ->select('short_description', 'instructions', 'is_receipt')
                ->first();
        } else {
            $offline = OfflineGateway::where('id', $request->name)
                ->where('status', 1)
                ->where('user_id', $request->user_id)
                ->select('short_description', 'instructions', 'is_receipt')
                ->first();
        }

        return response()->json([
            'description' => $offline->short_description,
            'instructions' => $offline->instructions,
            'is_receipt' => $offline->is_receipt
        ]);
    }
}
