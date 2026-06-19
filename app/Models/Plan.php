<?php

namespace App\Models;

use Nexbolt\Core\Traits\GeneratesTokens;
use Nexbolt\Core\Traits\HasConvertedPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Plan extends Model
{
    use SoftDeletes;
    use GeneratesTokens;
    use HasConvertedPrice;

    public function getKeyIdColumnName(): string { return 'plan_key'; }
    public function getKeyIdDigits(): int { return 8; }

    public function getTokenColumnName(): string { return ''; }

    protected $fillable = [
        'plan_key',
        'name',
        'slug',
        'description',

        // base pricing (STORE ORIGINAL ONLY)
        'price',
        'currency',

        'billing_cycle',
        'trial_days',

        'max_users',
        'max_products',
        'max_orders',

        'sort_order',
        'is_featured',
        'is_active',

        'features',
        'metadata',
    ];

    protected $casts = [
        'features'    => 'array',
        'metadata'    => 'array',

        'price'       => 'decimal:2',

        'is_featured' => 'boolean',
        'is_active'   => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Plan $plan) {

            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }

            $currency = strtoupper($plan->currency ?? 'USD');

            $plan->currency = $currency;

            $plan->metadata = array_merge($plan->metadata ?? [], [
                'currency_locked' => true,
                'source' => 'admin_or_seed',
            ]);
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /*
    |--------------------------------------------------------------------------
    | RULE
    |--------------------------------------------------------------------------
    | price = original currency only
    | currency_snapshot = never change
    | conversion = UI layer only
    |--------------------------------------------------------------------------
    */
}