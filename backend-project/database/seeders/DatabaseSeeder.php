<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@skyhawk.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 2. Create Sample Student 1
        $studentUser1 = User::firstOrCreate(
            ['email' => 'student@skyhawk.com'],
            [
                'name' => 'Mushtaq Ahmad',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ]
        );

        Student::firstOrCreate(
            ['user_id' => $studentUser1->id],
            [
                'admission_number' => 'SH-2026-001',
                'phone' => '+92 300 1234567',
                'date_of_birth' => '2004-05-15',
                'gender' => 'Male',
                'grade_level' => 'Grade 10',
                'section' => 'A',
                'address' => 'University Road, Swat',
                'parent_name' => 'Ahmad Khan',
                'parent_phone' => '+92 300 7654321',
                'status' => 'active',
            ]
        );

        // 3. Create Sample Student 2
        $studentUser2 = User::firstOrCreate(
            ['email' => 'sarah@skyhawk.com'],
            [
                'name' => 'Sarah Jenkins',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ]
        );

        Student::firstOrCreate(
            ['user_id' => $studentUser2->id],
            [
                'admission_number' => 'SH-2026-002',
                'phone' => '+92 301 9876543',
                'date_of_birth' => '2005-08-20',
                'gender' => 'Female',
                'grade_level' => 'Grade 9',
                'section' => 'B',
                'address' => 'Green Park, Swat',
                'parent_name' => 'Robert Jenkins',
                'parent_phone' => '+92 301 3456789',
                'status' => 'active',
            ]
        );
    }
}
