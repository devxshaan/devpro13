<?php

namespace App\Models;

use App\Traits\GeneratesTokens;
use App\Traits\HasConvertedPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use SoftDeletes;
    use GeneratesTokens;
    use HasConvertedPrice;

    public function getKeyIdColumnName(): string { return 'refund_key'; }
    public function getKeyIdPrefix(): ?string { return 'REF'; }
    public function getKeyIdDigits(): int { return 8; }

    protected $fillable = [
        'refund_key',
        'user_id',
        'payment_id',
        'order_id',
        'processed_by',

        // money
        'amount',
        'currency',
        'status',

        'gateway_response',

        'reason',

        'processed_at',
        'failed_at',

        'metadata',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'metadata'         => 'array',

        'amount'           => 'decimal:2',

        'processed_at'     => 'datetime',
        'failed_at'        => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Refund $refund) {

            $currency = strtoupper($refund->currency ?? 'USD');

            $refund->currency = $currency;

            $refund->metadata = array_merge($refund->metadata ?? [], [
                'currency_locked' => true,
                'source' => 'payment_refund_flow',
            ]);
        });
    }

    // ── Relations ─────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ── Helpers ───────────────────────────────────────────────
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /*
    |--------------------------------------------------------------------------
    | RULE
    |--------------------------------------------------------------------------
    | amount = original refund currency
    | currency_snapshot = never change
    | conversion = UI only
    |--------------------------------------------------------------------------
    */
}