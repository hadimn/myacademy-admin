<?php

namespace App\Http\Controllers;

use App\Models\OtpCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OtpCodeController extends Controller
{
    public function newOtp($user_id, $hashed_otp, $expires_at)
    {
        try {

            $newOtp = OtpCodes::create([
                    "user_id" => $user_id,
                    "hashed_otp" => $hashed_otp,
                    "expires_at" => $expires_at,
                ]);

            if (!$newOtp) {
                return response()->json([
                    "status" => "failed",
                    "message" => "failed to create a new otp for that user",
                ], 401);
            }

            return response()->json([
                "status" => "success",
                "message" => "otp has been created successfuly, please check you email to verify it.",
                "data" => $newOtp,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "status" => "failed",
                "message" => "invalid inputs.",
                "error" => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failed",
                "message" => "there was an error creating an otp",
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function deletOtp($otpId)
    {
        try {
            $otp = OtpCodes::find($otpId);

            if (!$otp->delete()) {
                return response()->json([
                    "status" => "failed",
                    "message" => "failed to delete the otp with id = $otpId",
                ]);
            }

            return response()->json([
                "status" => "success",
                "message" => "otp deleted successfuly!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failed",
                "message" => "failed to delete otp, check following errors.",
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function getOtpByUser($user_id)
    {
        $otp = OtpCodes::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$otp) {
            return null;
        }

        return $otp;
    }
}
