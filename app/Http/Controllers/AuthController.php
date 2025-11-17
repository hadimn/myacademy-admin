<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            if (!$user) {
                return response()->json([
                    "status" => "failed",
                    "message" => "Account creation failed due to a system error.",
                    "suggestion" => "Please try again in a few moments.",
                ]);
            }

            $otp = rand(100000, 999999);
            Log::debug("my otp is: $otp");
            Cache::put('user_otp', "$otp");
            $hashed_otp = Hash::make($otp);

            $newOtp = new OtpCodeController();
            $newOtp->newOtp($user->id, $hashed_otp, Carbon::now()->addMinute(15));


            event(new Registered($user));

            return response()->json([
                'status' => 'success',
                'message' => 'user registered successfuly',
                'user' => $user,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'error',
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|max:255',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'email is not found try to check!',
                ], 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'password is wrong',
                ], 401);
            }

            // $token = $user->createToken('auth_token')->plainTextToken;
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'user logged in successfuly',
                'user' => $user,
                'token' => $token,
                // 'otp' => Cache::get('user_otp'),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'validation error',
                'errors' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // make sure that there an authinticated user
            if (!$request->user()) {
                return response()->json([
                    "status" => "fail",
                    "message" => "there is no user authinticated",
                ]);
            }

            // make sure there is a token valid or not
            if (!$request->user()->currentAccessToken()) {
                return response()->json([
                    "status" => "fail",
                    "message" => "there is no active token",
                ]);
            }

            $request->user()->currentAccessToken()->delete();

            return response()->json([
                "status" => "success",
                "message" => "user is logged out successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "fail",
                "error" => $e->getMessage(),
            ]);
        }
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
            // $user->tokens()->delete();

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
}
