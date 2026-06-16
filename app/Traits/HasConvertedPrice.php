<?php

namespace App\Traits;

use App\Models\Setting;
use App\Services\CurrencyConverter;
use Illuminate\Support\Facades\Cache;

trait HasConvertedPrice
{
    // Cache default currency to avoid repeated DB hits
    protected function getDisplayCurrency(): string
    {
        return Cache::remember('default_currency', 3600, function () {
            return Setting::get('default_currency', 'USD');
        });
    }

    public function formatted(string $field): string
    {
        $amount = (float) ($this->{$field} ?? 0);

        if ($amount <= 0) {
            return '0.00';
        }

        $displayCurrency = $this->getDisplayCurrency();
        $fromCurrency    = strtoupper($this->currency ?? 'USD');

        $converter = app(CurrencyConverter::class);

        $converted = $converter->convert(
            $amount,
            $fromCurrency,
            strtoupper($displayCurrency)
        );

        return $converter->format($converted, $displayCurrency);
    }

    public function getFormattedAmountAttribute(): string
    {
        return $this->formatted('amount');
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->formatted('total');
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->formatted('price');
    }

    public function getFormattedSubscriptionPriceAttribute(): string
    {
        return $this->formatted('price_at_subscription');
    }
}