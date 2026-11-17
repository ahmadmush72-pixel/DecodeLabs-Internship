<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $studentUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin
        $this->adminUser = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@skyhawk.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Create Student
        $this->studentUser = User::create([
            'name' => 'Mushtaq Ahmad',
            'email' => 'student@skyhawk.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
        ]);

        Student::create([
            'user_id' => $this->studentUser->id,
            'admission_number' => 'SH-2026-0001',
            'phone' => '+92 300 1234567',
            'date_of_birth' => '2004-05-15',
            'gender' => 'Male',
            'grade_level' => 'Grade 10',
            'section' => 'A',
            'address' => 'University Road, Swat',
            'parent_name' => 'Ahmad Khan',
            'parent_phone' => '+92 300 7654321',
            'status' => 'active',
        ]);
    }

    public function test_user_can_register_as_student(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'student',
            'phone' => '1234567890',
            'gender' => 'Male',
            'grade_level' => 'Grade 10',
            'section' => 'A',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role', 'student'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
            'role' => 'student',
        ]);

        $this->assertDatabaseHas('students', [
            'phone' => '1234567890',
            'gender' => 'Male',
        ]);
    }

    public function test_registration_validation_fails_with_invalid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@skyhawk.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.user.role', 'admin');
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@skyhawk.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    public function test_admin_can_access_students_list(): void
    {
        $token = $this->adminUser->createToken('test_admin')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/students');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'data' => ['total', 'students'],
            ]);
    }

    public function test_student_cannot_access_admin_students_list(): void
    {
        $token = $this->studentUser->createToken('test_student')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/students');

        $response->assertStatus(403)
            ->assertJsonPath('status', false);
    }

    public function test_student_can_access_own_profile(): void
    {
        $token = $this->studentUser->createToken('test_student_profile')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/student/profile');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.student.user_id', $this->studentUser->id);
    }

    public function test_admin_cannot_access_student_profile_endpoint(): void
    {
        $token = $this->adminUser->createToken('test_admin_profile')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/student/profile');

        $response->assertStatus(403)
            ->assertJsonPath('status', false);
    }
}
