<?php

namespace Database\Seeders;

use App\Helpers\TransactionIdGenerator;
use App\Models\Merchant;
use App\Models\Payin;
use App\Models\Payout;
use Illuminate\Database\Seeder;

class PayinPayoutSeeder extends Seeder
{
    public function run(): void
    {
        $merchants = Merchant::all();

        foreach ($merchants as $merchant) {
          
            for ($i = 1; $i <= 2; $i++) {
                Payin::create([
                    'transaction_id' => TransactionIdGenerator::forPayin(),
                    'merchant_id' => $merchant->id,
                    'amount' => fake()->numberBetween(100, 2000),
                    'currency' => 'INR',
                    'status' => 'PENDING',
                    'payload' => ['source' => 'seeder'],
                ]);
            }

           
            Payout::create([
                'transaction_id' => TransactionIdGenerator::forPayout(),
                'merchant_id' => $merchant->id,
                'amount' => fake()->numberBetween(100, 1000),
                'currency' => 'INR',
                'status' => 'PENDING',
                'payload' => ['source' => 'seeder'],
            ]);
        }
    }
}