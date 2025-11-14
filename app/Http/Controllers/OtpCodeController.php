<?php

namespace App\Http\Controllers;

use App\Models\OtpCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OtpCodeController extends Controller
{
    public function newOtp(Request $request)
    {
        try {
            $request->validate([
                "user_id" => "required|integer|min:100000|max:999999",
                "hashed_otp" => "required|string|unique:otp_codes",
                "expires_at" => "required|date|after:now",
            ]);

            $newOtp = OtpCodes::create([
                "user_id" => $request->user_id,
                "hashed_otp" => Hash::make($request->hashed_otp),
                "expires_at" => $request->expires_at,
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
}
