<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nexbolt\Core\Traits\GeneratesTokens;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Subscription extends Model
{
    use SoftDeletes;
    use GeneratesTokens;
    use LogsActivity;

    public function getKeyIdColumnName(): string { return 'subscription_key'; }
    public function getKeyIdDigits(): int { return 8; }
    public function getTokenColumnName(): string { return ''; }

    protected $fillable = [
        'subscription_key',
        'user_id',
        'plan_id',

        // billing snapshot (VERY IMPORTANT)
        'price_at_subscription',
        'currency',

        'status',

        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'paused_at',
        'resumed_at',

        'gateway',
        'gateway_subscription_id',

        // audit + billing safety
        'metadata',
    ];

    protected $casts = [
        'metadata'      => 'array',

        'trial_ends_at' => 'datetime',
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
        'cancelled_at'  => 'datetime',
        'paused_at'     => 'datetime',
        'resumed_at'    => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Subscription $subscription) {

            $currency = strtoupper($subscription->currency ?? 'USD');

            $subscription->currency = $currency;

            $subscription->metadata = array_merge($subscription->metadata ?? [], [
                'price_locked' => true,
                'billing_source' => 'plan_snapshot',
                'currency_locked' => true,
            ]);
        });
    }

    // ── Relations ─────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // ── Status Helpers ────────────────────────
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at?->isFuture();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logOnly([
                'status',
                'price_at_subscription',
                'currency',
                'starts_at',
                'ends_at',
                'cancelled_at',
                'paused_at',
                'resumed_at',
            ]);
    }
}