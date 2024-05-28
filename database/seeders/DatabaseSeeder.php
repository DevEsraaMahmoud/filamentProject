<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();
        \App\Models\Country::factory(5)->create();
        \App\Models\State::factory(5)->create();
        \App\Models\City::factory(5)->create();
        \App\Models\Department::factory(5)->create();
        \App\Models\Employee::factory(5)->create();

        \App\Models\User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@admin.com',
            'is_admin' => true,
            'password' => Hash::make('password'),
        ]);
    }
}
