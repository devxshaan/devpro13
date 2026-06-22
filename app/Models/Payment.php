<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nexbolt\Core\Traits\GeneratesTokens;
use Nexbolt\Core\Traits\HasConvertedPrice;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;


class Payment extends Model
{
    use SoftDeletes;
    use GeneratesTokens;
    use HasConvertedPrice;
    use LogsActivity;

    public function getKeyIdColumnName(): string { return 'payment_key'; }
    public function getKeyIdPrefix(): ?string { return 'PAY'; }
    public function getKeyIdDigits(): int { return 8; }
    public function getTokenColumnName(): string { return ''; }

    protected $fillable = [
        'payment_key',
        'user_id',
        'subscription_id',
        'payable_id',
        'payable_type',

        'gateway',
        'gateway_payment_id',
        'gateway_order_id',
        'gateway_response',

        // money fields (BASE STORE CURRENCY ONLY)
        'amount',
        'amount_refunded',
        'currency',

        'payment_method',
        'status',

        'paid_at',
        'refunded_at',
        'failed_at',

        'notes',
        'ip_address',

        // audit + conversion tracking
        'metadata',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'metadata'         => 'array',

        'amount'           => 'decimal:2',
        'amount_refunded'  => 'decimal:2',

        'paid_at'          => 'datetime',
        'refunded_at'      => 'datetime',
        'failed_at'        => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {

            $currency = strtoupper($payment->currency ?? 'USD');

            $payment->currency = $currency;

            $payment->metadata = array_merge($payment->metadata ?? [], [
                'currency_locked' => true,
                'source' => 'gateway_or_manual',
            ]);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logOnly([
                'status',
                'amount',
                'amount_refunded',
                'gateway',
                'gateway_payment_id',
                'paid_at',
                'refunded_at',
                'failed_at',
            ]);
    }
}