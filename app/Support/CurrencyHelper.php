<?php

namespace App\Support;

use App\Models\Organization;

class CurrencyHelper
{
    /**
     * Supported currencies with their symbols and locales.
     */
    public const CURRENCIES = [
        'NGN' => ['symbol' => '₦', 'locale' => 'en-NG', 'name' => 'Nigerian Naira'],
        'USD' => ['symbol' => '$', 'locale' => 'en-US', 'name' => 'US Dollar'],
        'GBP' => ['symbol' => '£', 'locale' => 'en-GB', 'name' => 'British Pound'],
        'EUR' => ['symbol' => '€', 'locale' => 'de-DE', 'name' => 'Euro'],
        'GHS' => ['symbol' => 'GH₵', 'locale' => 'en-GH', 'name' => 'Ghanaian Cedi'],
        'KES' => ['symbol' => 'KSh', 'locale' => 'en-KE', 'name' => 'Kenyan Shilling'],
        'ZAR' => ['symbol' => 'R', 'locale' => 'en-ZA', 'name' => 'South African Rand'],
        'INR' => ['symbol' => '₹', 'locale' => 'en-IN', 'name' => 'Indian Rupee'],
        'AED' => ['symbol' => 'AED', 'locale' => 'ar-AE', 'name' => 'UAE Dirham'],
        'CAD' => ['symbol' => 'CA$', 'locale' => 'en-CA', 'name' => 'Canadian Dollar'],
        'AUD' => ['symbol' => 'A$', 'locale' => 'en-AU', 'name' => 'Australian Dollar'],
    ];

    /**
     * Format an amount (in smallest unit, e.g. kobo/cents) to a display string.
     */
    public static function format(int $amountSmallest, string $currencyCode = 'NGN', bool $showCode = false): string
    {
        $info = self::CURRENCIES[$currencyCode] ?? self::CURRENCIES['NGN'];
        $amount = $amountSmallest / 100;

        $formatted = number_format($amount, 0, '.', ',');
        $result = "{$info['symbol']}{$formatted}";

        return $showCode ? "{$result} {$currencyCode}" : $result;
    }

    /**
     * Format using organization's currency.
     */
    public static function formatForOrg(int $amountSmallest, ?Organization $org = null): string
    {
        $code = $org?->currency_code ?? 'NGN';
        return self::format($amountSmallest, $code);
    }

    /**
     * Get a list of currencies for dropdown selects.
     */
    public static function list(): array
    {
        return collect(self::CURRENCIES)->map(fn($info, $code) => [
            'code' => $code,
            'symbol' => $info['symbol'],
            'name' => $info['name'],
            'label' => "{$info['symbol']} - {$info['name']} ({$code})",
        ])->values()->toArray();
    }
}
