<?php

namespace App\Http\Controllers;

use App\Models\OtpCodes;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponseTrait;

    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = $this->authService->registerUser($request->only('name', 'email', 'password'));

            if (!$user) {
                return $this->errorResponse(
                    "can't register your account due to some errors!",
                    Response::HTTP_INTERNAL_SERVER_ERROR, // 500
                );
            }

            return $this->successResponse(
                $user,
                "user registered successfuly!",
                Response::HTTP_CREATED, // 201
                true,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                "failed to register due to invalid data!",
                Response::HTTP_UNPROCESSABLE_ENTITY,
                [$e->getMessage()],
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "failed to register due to an error!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|max:255',
                'password' => 'required|string',
            ]);

            $user = $this->authService->authinticate($request->email, $request->password);

            if (!$user) {
                $this->errorResponse(
                    'invalid credentials (email or password).',
                    Response::HTTP_UNAUTHORIZED, // 500
                );
            }

            if ($user->hasVerifiedEmail()) {
                $token = $user->createToken('auth_token', ['user-access'], Carbon::now()->addDays(1))->plainTextToken;

                return $this->successResponse(
                    [
                        "user" => $user,
                        "token" => $token,
                    ],
                    'user logged in successfuly',
                    Response::HTTP_OK, // 201
                    true,
                );
            }

            $otp = $user->otp;

            if ($otp && !$otp->isExpired()) {
                return $this->successResponse(
                    [
                        "type" => "use_old_otp",
                    ],
                    "please, use the previous otp that we send to you before!",
                    Response::HTTP_OK,
                    false,
                );
            }

            $this->authService->generateAndStoreOtp($user);

            return $this->successResponse(
                [
                    "type" => "new_otp",
                ],
                "we send an otp to your email, please verify to get access!",
                Response::HTTP_FORBIDDEN,
                false,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse(
                [$e->getMessage()],
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "failed due to an error!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();

            // User not logged in
            if (!$user) {
                return $this->errorResponse(
                    "Can't logout, you must be logged in first!",
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // No active token found
            $token = $user->currentAccessToken();
            if (!$token) {
                return $this->errorResponse(
                    "There is no active token!",
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Revoke token
            $token->delete();

            return $this->successResponse(
                null,
                "User logged out successfully!",
                Response::HTTP_NO_CONTENT // Best practice for logout
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to log out due to an internal error!",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()] // Optional: helps debugging
            );
        }
    }


    public function deleteMyAccount()
    {
        try {
            $user = Auth::user();

            // Ensure user is authenticated
            if (!$user) {
                return $this->errorResponse(
                    "You must be logged in to delete your account.",
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // Delete all access tokens (logout everywhere)
            $user->tokens()->delete();

            // Delete the user account
            $user->delete();

            return $this->successResponse(
                null,
                "Account deleted successfully.",
                Response::HTTP_NO_CONTENT, // No content needed after deletion
                true
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to delete account.",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()] // Optional debug message
            );
        }
    }
}
