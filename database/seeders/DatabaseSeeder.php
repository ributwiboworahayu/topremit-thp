<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ]);

        User::create([
            'name' => 'Company',
            'email' => 'company@gmail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ]);

        User::create([
            'name' => 'Ninja',
            'email' => 'ninjaturtul@gmail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ]);

        User::factory(10)->create();

        AppSetting::create([
            'key' => 'exchange_fee',
            'name' => '(IDR) Exchange Fee',
            'value' => 10000,
            'description' => '(IDR) This is the fee for exchange transaction'
        ]);

        AppSetting::create([
            'key' => 'api_key',
            'name' => 'API Key',
            'value' => '123456',
            'description' => 'This is the API Key for the payment gateway'
        ]);

        Voucher::create([
            'name' => 'Diskon 10%',
            'code' => 'DISKON10',
            'point_required' => 100,
            'discount_percentage' => 10,
            'stock' => 100,
            'description' => 'Voucher diskon 10% untuk transfer saldo',
            'start_date' => now(), // '2024-04-30 02:20:39
            'expired_date' => now()->addMonths(6),
        ]);

        Voucher::create([
            'name' => 'Diskon 20%',
            'code' => 'DISKON20',
            'point_required' => 200,
            'discount_percentage' => 20,
            'stock' => 50,
            'description' => 'Voucher diskon 20% untuk transfer saldo',
            'start_date' => now(), // '2024-04-30 02:20:39
            'expired_date' => now()->addMonths(6),
        ]);
    }
}
