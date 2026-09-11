<?php

namespace App\Helpers;

use App\Models\Payin;
use App\Models\Payout;
use Illuminate\Support\Str;



class TransactionIdGenerator
{
    private const PAYIN_PREFIX = 'PIN';
    private const PAYOUT_PREFIX = 'POT';
    private const MAX_ATTEMPTS = 5;

    public static function forPayin(): string
    {
        return self::generateUnique(self::PAYIN_PREFIX, function (string $id) {
            return Payin::where('transaction_id', $id)->exists();
        });
    }

    public static function forPayout(): string
    {
        return self::generateUnique(self::PAYOUT_PREFIX, function (string $id) {
            return Payout::where('transaction_id', $id)->exists();
        });
    }

    private static function generateUnique(string $prefix, callable $existsCheck): string
    {
        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $candidate = self::build($prefix);

            if (! $existsCheck($candidate)) {
                return $candidate;
            }
        }


        return $prefix . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(12));
    }

    private static function build(string $prefix): string
    {
        return sprintf(
            '%s-%s-%s',
            $prefix,
            now()->format('Ymd'),
            Str::upper(Str::random(8))
        );
    }
}