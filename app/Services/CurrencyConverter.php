<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyConverter
{
    // Cache duration — 1 hour
    protected int $cacheTtl = 3600;

    // Fallback rates agar API down ho
    protected array $fallbackRates = [
        'USD' => ['INR' => 83.50, 'EUR' => 0.92, 'GBP' => 0.79, 'AED' => 3.67, 'PKR' => 278.50],
        'INR' => ['USD' => 0.012, 'EUR' => 0.011, 'GBP' => 0.0095, 'AED' => 0.044, 'PKR' => 3.34],
        'EUR' => ['USD' => 1.09, 'INR' => 90.50, 'GBP' => 0.86, 'AED' => 4.00, 'PKR' => 303.00],
    ];

    // ── Main Convert Method ───────────────────────────────────────
    public function convert(float $amount, string $from, string $to): float
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        if ($from === $to) {
            return round($amount, 2);
        }

        $rate = $this->getRate($from, $to);

        return round($amount * $rate, 2);
    }

    // ── Get Exchange Rate ─────────────────────────────────────────
    public function getRate(string $from, string $to): float
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        if ($from === $to) {
            return 1.0;
        }

        $cacheKey = "exchange_rate_{$from}_{$to}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($from, $to) {
            return $this->fetchRate($from, $to);
        });
    }

    // ── Fetch from API ────────────────────────────────────────────
    protected function fetchRate(string $from, string $to): float
    {
        try {
            $from = strtoupper($from);
            $to   = strtoupper($to);

            $response = Http::timeout(5)
                ->get("https://api.exchangerate-api.com/v4/latest/{$from}");

            if ($response->successful()) {
                $rates = $response->json('rates');

                if (isset($rates[$to])) {
                    return (float) $rates[$to];
                }
            }

            Log::warning("CurrencyConverter: Rate not found for {$from} → {$to}");
            return $this->getFallbackRate($from, $to);

        } catch (\Exception $e) {
            Log::error("CurrencyConverter API failed: " . $e->getMessage());
            return $this->getFallbackRate($from, $to);
        }
    }

    // ── Fallback Rate ─────────────────────────────────────────────
    protected function getFallbackRate(string $from, string $to): float
    {
        $from = strtoupper($from);
        $to   = strtoupper($to);

        if (isset($this->fallbackRates[$from][$to])) {
            return (float) $this->fallbackRates[$from][$to];
        }

        if (isset($this->fallbackRates[$to][$from])) {
            return round(1 / $this->fallbackRates[$to][$from], 6);
        }

        return 1.0;
    }

    // ── Format with Currency ──────────────────────────────────────
    public function format(float $amount, string $currency): string
    {
        try {
            return (new \Akaunting\Money\Money(
                (int) round($amount * 100),
                new \Akaunting\Money\Currency(strtoupper($currency))
            ))->format();
        } catch (\Exception $e) {
            $symbol = $this->getSymbol($currency);
            return $symbol . number_format($amount, 2);
        }
    }

    // ── Convert + Format in one shot ─────────────────────────────
    public function convertAndFormat(float $amount, string $from, string $to): string
    {
        $converted = $this->convert($amount, $from, $to);
        return $this->format($converted, $to);
    }

    // ── Get Symbol ────────────────────────────────────────────────
    public function getSymbol(string $currency): string
    {
        try {
            return (new \Akaunting\Money\Currency(strtoupper($currency)))->getSymbol();
        } catch (\Exception $e) {
            return strtoupper($currency);
        }
    }

    // ── Clear Cache ───────────────────────────────────────────────
    public function clearRateCache(string $from = null, string $to = null): void
    {
        if ($from && $to) {
            Cache::forget(
                "exchange_rate_" . strtoupper($from) . "_" . strtoupper($to)
            );
        }
    }
}