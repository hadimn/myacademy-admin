<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
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
}
