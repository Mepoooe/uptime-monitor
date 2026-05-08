<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Domain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name'              => 'Demo User',
            'email'             => 'demo@example.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $domains = [
            ['url' => 'https://google.com',    'name' => 'Google',    'check_interval' => 5,  'check_method' => 'HEAD'],
            ['url' => 'https://github.com',    'name' => 'GitHub',    'check_interval' => 10, 'check_method' => 'HEAD'],
            ['url' => 'https://laravel.com',   'name' => 'Laravel',   'check_interval' => 15, 'check_method' => 'GET'],
            ['url' => 'https://httpstat.us/503', 'name' => 'Test DOWN', 'check_interval' => 5, 'check_method' => 'HEAD'],
        ];

        foreach ($domains as $data) {
            $user->domains()->create($data);
        }
    }
}
