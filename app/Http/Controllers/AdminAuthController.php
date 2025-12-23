<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthController extends Controller
{
    use ApiResponseTrait;

    protected $resourceName = 'admin';
    protected $adminGuard = 'admin';
    protected $abilities = ['admin-access']; // Define the token ability for admin

    /**
     * Handle Admin Login and Token Issuance.
     */
    public function login(Request $request)
    {
        try {
            // 1. Validation
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // 2. Attempt Authentication
            $credentials = $request->only('email', 'password');

            if (!Auth::guard($this->adminGuard)->attempt($credentials)) {
                return $this->errorResponse(
                    'Invalid credentials.',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // 3. Retrieve Authenticated Admin
            $admin = Auth::guard($this->adminGuard)->user();

            // 4. Token Issuance
            $token = $admin->createToken('admin-token', $this->abilities)->plainTextToken;

            return $this->successResponse(
                [
                    'admin' => $admin,
                    'token' => $token,
                ],
                'Admin logged in successfully.',
                Response::HTTP_OK
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Login failed due to invalid input.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Login failed: " . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Handle Admin Registration (if needed).
     * This is useful for initial setup or supervised admin creation.
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:admins',
                'password' => 'required|string|min:8', // Add confirmation rule if needed
            ]);

            $admin = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Optionally issue a token immediately after registration
            $token = $admin->createToken('admin-token', $this->abilities)->plainTextToken;

            return $this->successResponse(
                [
                    'admin' => $admin,
                    'token' => $token,
                ],
                'Admin registered successfully.',
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse(
                $e->errors(),
                'Registration failed due to invalid input.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                "Registration failed: " . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Handle Admin Logout (Revoke the current token).
     */
    public function logout(Request $request)
    {
        // $request->user() here is the currently authenticated admin via Sanctum
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(
            null,
            'Admin logged out successfully.',
            Response::HTTP_OK
        );
    }

    /**
     * Update the authenticated admin's profile information.
     */
    public function updateProfile(Request $request)
    {
        try {
            $admin = $request->user();

            $validatedData = $request->validate([
                'name'  => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            ]);

            $admin->update($validatedData);

            return $this->successResponse(
                $admin,
                'Profile updated successfully.',
                Response::HTTP_OK
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 'Update failed.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Change the authenticated admin's password.
     */
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'old_password' => 'required',
                'password'     => 'required|string|min:8|confirmed',
            ]);

            $admin = $request->user();

            // Check if the old password matches
            if (!Hash::check($request->old_password, $admin->password)) {
                return $this->errorResponse(
                    'The provided old password does not match our records.',
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            // Update the password
            $admin->update([
                'password' => Hash::make($request->password),
            ]);

            return $this->successResponse(
                null,
                'Password changed successfully.',
                Response::HTTP_OK
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 'Password change failed.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
