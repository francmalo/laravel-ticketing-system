<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create an Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => 1, // Admin role
        ]);

        // Create an IT Staff user
        User::create([
            'name' => 'IT Staff User',
            'email' => 'itstaff@example.com',
            'password' => Hash::make('password'),
            'role_id' => 2, // IT Staff role
        ]);

        // Create an Employee user
        User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role_id' => 3, // Employee role
        ]);
    }
}


