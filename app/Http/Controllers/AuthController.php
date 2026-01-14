<?php

namespace App\Http\Controllers;

use App\Events\UserCreated;
use App\Models\User;
use App\Services\AuthService;
use App\Services\BadgeService;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponseTrait;

    protected $authService;
    protected $badgeService;
    public function __construct(AuthService $authService, BadgeService $badgeService)
    {
        $this->authService = $authService;
        $this->badgeService = $badgeService;
    }

    // isAuthinticated
    public function isAuthinticated()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse(
                    "unauthinticated",
                    Response::HTTP_UNAUTHORIZED,
                );
            }

            return $this->successResponse(
                $user,
                "authinticated",
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "unauthinticated",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()],
            );
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'username' => 'required|string|unique:users',
                'phone' => 'nullable|string|max:255',
                'bio' => 'nullable|string|max:255',
                'password' => 'required|string|min:6|confirmed',
                'profile_image' => 'nullable|jpg,jpeg,png,gif,svg|max:50048',
            ]);

            // handle profile_image field in the uploads in public/storage/uploads
            if ($request->hasFile('profile_image')) {
                $imagePath = $request->file('profile_image')->store('uploads/profile_images', 'public');
                $request->merge(['profile_image' => $imagePath]);
            } else {
                $request->merge(['profile_image' => null]);
            }

            $user = $this->authService->registerUser($request->only('name', 'email','username', 'phone', 'bio', 'profile_image', 'password'));

            if (!$user) {
                return $this->errorResponse(
                    "can't register your account due to some errors!",
                    Response::HTTP_INTERNAL_SERVER_ERROR, // 500
                );
            }

            event(new UserCreated($user));

            return $this->successResponse(
                [
                    'user' => $user,
                    'email' => $user->email,
                    'user_id' => $user->id,
                ],
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

            // 1. Check if the email exists in the database
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->errorResponse(
                    'An unexpected error occurred.',
                    Response::HTTP_NOT_FOUND, // 404
                    ['This email is not registered!'],
                );
            }

            // 2. Check if the password is correct
            if (!Hash::check($request->password, $user->password)) {
                return $this->errorResponse(
                    'An unexpected error occurred.',
                    Response::HTTP_UNAUTHORIZED, // 401
                    ['The password you entered is incorrect.'],
                );
            }

            // 3. Email Verification Check
            if (!$user->hasVerifiedEmail()) {
                // 4. OTP Logic (Same as your original)
                $otp = $user->otp;

                if ($otp && !$otp->isExpired()) {
                    return $this->successResponse(
                        [
                            "type" => "use_old_otp",
                            "user_id" => $user->id,
                        ],
                        "Please use the previous OTP sent to your email.",
                        Response::HTTP_OK,
                        true,
                    );
                }

                $this->authService->generateAndStoreOtp($user);
                $user->sendEmailVerificationNotification();

                return $this->successResponse(
                    [
                        "type" => "new_otp",
                        "user_id" => $user->id,
                        "otp" => $otp->hashed_otp,
                        "otp_is_expired" => $otp->isExpired(),
                    ],
                    "We sent an OTP to your email. Please verify to get access!",
                    Response::HTTP_FORBIDDEN,
                    false
                );
            }

            $token = $user->createToken('auth_token', ['user-access'], Carbon::now()->addDays(1))->plainTextToken;

            return $this->successResponse(
                [
                    "user" => $user,
                    "token" => $token,
                ],
                'User logged in successfully',
                Response::HTTP_OK,
                true
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse([$e->getMessage()]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                "An error occurred during login.",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    public function logout()
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
                Response::HTTP_OK // Best practice for logout
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

    public function resendOtp(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $user = User::find($request->user_id);

            // Check if user is already verified
            if ($user->hasVerifiedEmail()) {
                return $this->successResponse(
                    null,
                    "Email already verified!",
                    Response::HTTP_OK
                );
            }

            // Generate new OTP
            $this->authService->generateAndStoreOtp($user);
            $user->sendEmailVerificationNotification();

            return $this->successResponse(
                [
                    "type" => "new_otp",
                    "user_id" => $user->id,
                ],
                "A new OTP has been sent to your email.",
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Failed to resend OTP",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }
}
