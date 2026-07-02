<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            SupplierSeeder::class,
        ]);

        // Admin default
        User::create([
            'name' => 'Admin Waroeng',
            'email' => 'admin@waroeng.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Kasir default
        User::create([
            'name' => 'Kasir Waroeng',
            'email' => 'kasir@waroeng.test',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);
    }
}
