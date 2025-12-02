<?php

namespace App\Http\Controllers;

use App\Models\OtpCodes;
use App\Models\User;
use App\Notifications\AccountVerified;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

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

    public function deleteUserById($id)
    {
        try {
            // Find the user by ID
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    "status" => "failed",
                    "message" => "User not found.",
                ], 404);
            }

            // Optionally revoke all tokens before deleting
            $user->tokens()->delete();

            // Delete the user
            $user->delete();

            return response()->json([
                "status" => "success",
                "message" => "User deleted successfully.",
                "deleted_user_id" => $id,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failed",
                "message" => "Failed to delete user.",
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    public function getUserDetails(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return $this->errorResponse(
                    "unauthinticated",
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
            }
            return $this->successResponse(
                $user,
                "user $user->name details retrieved successfuly!",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "failed to retrieve user's details due to an error",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    public function editUserDetails(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse(
                    "unauthinticated",
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                );
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

            return $this->successResponse(
                $user,
                "$user->name's details has been updated successfuly!",
                Response::HTTP_CREATED,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                [$e->getMessage()],
                "failed due to an invalid inputs!",
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "failed due to an errors!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    public function verifyEmailWithOtp(Request $request, $id, $otp)
    {
        $user = User::findOrFail($id);

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(
                null,
                "Email already verified!",
                Response::HTTP_OK,
            );
        }

        $hashedOtp = OtpCodes::where('user_id', $user->id)
            ->where("is_verified", 0)
            ->where("expires_at", ">", Carbon::now())
            ->first();

        if (!$hashedOtp) {
            return $this->errorResponse(
                "Otp not found or expired!",
            );
        }

        if (!Hash::check($otp, $hashedOtp->hashed_otp)) {
            return $this->errorResponse(
                "You used wrong otp, please check and try again!",
            );
        }

        // Mark as verified
        $user->markEmailAsVerified();
        $hashedOtp->is_verified = true;
        $hashedOtp->save();
        event(new Verified($user));
        $user->notify(new AccountVerified());
        return $this->successResponse(
            $hashedOtp,
            "Email verified successfuly!",
            Response::HTTP_OK,
        );
    }
}
