<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Get all students with user details (Admin only).
     */
    public function index(Request $request): JsonResponse
    {
        $students = Student::with('user:id,name,email,role,created_at')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Student list retrieved successfully.',
            'data' => [
                'total' => $students->count(),
                'students' => $students,
            ],
        ], 200);
    }

    /**
     * Get a single student by ID (Admin only).
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $student = Student::with('user:id,name,email,role,created_at')->find($id);

        if (! $student) {
            return response()->json([
                'status' => false,
                'message' => 'Student record not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Student details retrieved successfully.',
            'data' => [
                'student' => $student,
            ],
        ], 200);
    }

    /**
     * Get authenticated student's own profile (Student only).
     */
    public function myProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $student = Student::with('user:id,name,email,role,created_at')
            ->where('user_id', $user->id)
            ->first();

        if (! $student) {
            return response()->json([
                'status' => false,
                'message' => 'Student profile record not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Student profile retrieved successfully.',
            'data' => [
                'student' => $student,
            ],
        ], 200);
    }
}
