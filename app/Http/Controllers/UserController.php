<?php

namespace App\Http\Controllers;

use App\Models\OtpCodes;
use App\Models\User;
use App\Notifications\AccountVerified;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseCrudController
{

    public function __construct()
    {
        $this->model = User::class;
        $this->resourceName = "user";
        $this->validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];
        $this->editValidationRules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,',
            'password' => 'sometimes|required|string|min:6|confirmed',
            'current_streak'=>'sometimes|required|integer|min:0',
            'longest_streak'=>'sometimes|required|integer|min:0',
        ];
    }

    public function getUserDetails(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    "status" => "fail",
                    "message" => "user is unauthinticated",
                ], 401);
            }
            return response()->json([
                "status" => "ok",
                "data" => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 'fail',
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    public function editUserDetails(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    "status" => "ok",
                    "message" => "user is not Authinticated",
                ]);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,',
                'password' => 'sometimes|required|string|min:6|confirmed',
            ]);

            if ($request->has('name')) $user->name = $request->name;
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password')) $user->password = Hash::make($request->password);

            $user->save();

            return response()->json([
                "status" => "success",
                "message" => "user updated successfully",
                "data" => $user,
                "currentData" => $request->user(),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "status" => "failed",
                "message" => "non valid data, please check what is wrong with the format",
                "error" => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failed",
                "message" => "failed to update user details",
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyEmailWithOtp(Request $request, $id, $otp)
    {
        $user = User::findOrFail($id);

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        $hashedOtp = OtpCodes::where('user_id', $user->id)
            ->where("is_verified", 0)
            ->where("expires_at", ">", Carbon::now())
            ->first();

        if (!$hashedOtp) {
            return response()->json([
                "status" => "failed",
                "message" => "OTP not found or expired.",
            ], 404);
        }

        if (!Hash::check($otp, $hashedOtp->hashed_otp)) {
            return response()->json([
                "status" => "failed",
                "message" => "you entered wrong otp, please try again!",
            ]);
        }

        // Mark as verified
        $user->markEmailAsVerified();
        $hashedOtp->is_verified = true;
        $hashedOtp->save();
        event(new Verified($user));
        $user->notify(new AccountVerified());
        return response()->json([
            'message' => 'Email verified successfully.',
            "data" => $hashedOtp,
        ], 200);
    }
}
