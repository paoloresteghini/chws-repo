<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Paolo Resteghini',
            'email' => 'hello@paolor.co.uk',
            'password' => Hash::make('123123123'),
            'email_verified_at' => now(),
        ]);
    }
}
