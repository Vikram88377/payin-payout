<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class MerchantSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $merchant = Merchant::create([
                'name' => fake()->company(),
                'email' => fake()->unique()->companyEmail(),
                'api_key' => bin2hex(random_bytes(16)),
                'status' => 'ACTIVE',
            ]);

            Wallet::create([
                'merchant_id' => $merchant->id,
                'balance' => fake()->numberBetween(1000, 10000),
            ]);
        }
    }
}