<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Handle user registration.
     */
    public function register(Request $request): JsonResponse
    {
        // 1. Validate incoming data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|string|in:student',
            // Student specific optional fields
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'grade_level' => 'nullable|string|max:50',
            'section' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error. Please check the fields.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 2. Create the User (Public registration is strictly for students/users)
        $role = 'student';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        // 3. If student, create Student profile record
        if ($role === 'student') {
            $admissionNumber = 'SH-' . date('Y') . '-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);

            Student::create([
                'user_id' => $user->id,
                'admission_number' => $admissionNumber,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'grade_level' => $request->grade_level ?? 'Grade 10',
                'section' => $request->section ?? 'A',
                'address' => $request->address,
                'parent_name' => $request->parent_name,
                'parent_phone' => $request->parent_phone,
                'status' => 'active',
            ]);
        }

        // 4. Generate Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Registration successful! Welcome to Sky Hawk.',
            'data' => [
                'user' => $user->load('student'),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Handle user login.
     */
    public function login(Request $request): JsonResponse
    {
        // 1. Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error. Please enter valid email and password.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 2. Find user & verify password
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password. Please try again.',
            ], 401);
        }

        // 3. Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful. Welcome back, ' . $user->name . '!',
            'data' => [
                'user' => $user->load('student'),
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * Retrieve the authenticated user's profile.
     */
    public function userProfile(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'User profile retrieved successfully.',
            'data' => [
                'user' => $request->user()->load('student'),
            ],
        ], 200);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully.',
        ], 200);
    }
}
