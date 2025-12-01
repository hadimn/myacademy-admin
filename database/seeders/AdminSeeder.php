<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin; // Import the Admin Model
use Illuminate\Support\Facades\Hash; // Import the Hash facade

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if an admin with the default email already exists to prevent duplicates
        if (!Admin::where('email', 'admin@example.com')->exists()) {
            Admin::create([
                'name' => 'Super Administrator',
                'email' => 'admin@example.com',
                // HASH the password before storing it
                'password' => Hash::make('password'), 
            ]);
        }
    }
}