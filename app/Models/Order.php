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


class Order extends Model
{
    use SoftDeletes;
    use GeneratesTokens;
    use HasConvertedPrice;
    use LogsActivity;

    public function getKeyIdColumnName(): string { return 'order_key'; }
    public function getKeyIdPrefix(): ?string { return 'ORD'; }
    public function getKeyIdDigits(): int { return 8; }
    public function getTokenColumnName(): string { return ''; }

    protected $fillable = [
        'order_key',
        'user_id',
        'orderable_type',
        'orderable_id',

        // money fields (BASE CURRENCY STORE)
        'subtotal',
        'discount',
        'tax',
        'shipping',
        'total',
        'currency',
        // optional coupon
        'coupon_id',
        'coupon_code',

        'status',
        'fulfillment_type',

        'shipping_address',

        'confirmed_at',
        'completed_at',
        'cancelled_at',

        'notes',
        'admin_notes',

        'ip_address',

        // IMPORTANT: conversion + audit trail
        'metadata',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'metadata'         => 'array',

        'subtotal'         => 'decimal:2',
        'discount'         => 'decimal:2',
        'tax'              => 'decimal:2',
        'shipping'         => 'decimal:2',
        'total'            => 'decimal:2',

        'confirmed_at'     => 'datetime',
        'completed_at'     => 'datetime',
        'cancelled_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {

            $baseCurrency = strtoupper($order->currency ?? 'USD');

            $order->currency = $baseCurrency;
            
            $order->metadata = array_merge($order->metadata ?? [], [
                'currency_locked' => true,
            ]);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logOnly([
                'status',
                'total',
                'fulfillment_type',
                'confirmed_at',
                'completed_at',
                'cancelled_at',
            ]);
    }
}