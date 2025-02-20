<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Clear existing users (optional)
        // User::truncate();

        // Create a default user
        User::create([
            'name' => 'John Doe',
            'email' => 'johndoe@gmail.com',
            'password' => Hash::make('abc123'),
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);

        // Create multiple users using a factory
        User::factory()->count(10)->create();
    }
}
