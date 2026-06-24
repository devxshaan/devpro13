<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nexbolt\Core\Traits\GeneratesTokens;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Invoice extends Model
{
    use SoftDeletes;
    use GeneratesTokens;
    use LogsActivity;

    public function getKeyIdColumnName(): string { return 'invoice_key'; }
    public function getKeyIdPrefix(): ?string { return 'INV'; }
    public function getKeyIdDigits(): int { return 8; }
    public function getTokenColumnName(): string { return ''; }

    protected $fillable = [
        'invoice_key',
        'invoice_number',
        'user_id',
        'payment_id',
        'order_id',

        'billed_to_name',
        'billed_to_email',
        'billed_to_address',

        'item_description',
        'line_items',

        'subtotal',
        'tax',
        'discount',
        'total',
        'currency',

        'status',

        'pdf_path',
        'emailed',

        'generated_by',
        'generation_source',

        'issued_at',
        'voided_at',

        'metadata',
    ];

    protected $casts = [
        'line_items' => 'array',
        'metadata'   => 'array',

        'subtotal'   => 'decimal:2',
        'tax'        => 'decimal:2',
        'discount'   => 'decimal:2',
        'total'      => 'decimal:2',

        'emailed'    => 'boolean',

        'issued_at'  => 'datetime',
        'voided_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            $invoice->currency = strtoupper($invoice->currency ?? 'USD');
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

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // ── Helpers ───────────────────────────────────────────────
    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logOnly([
                'status',
                'total',
                'pdf_path',
                'emailed',
                'issued_at',
                'voided_at',
            ]);
    }
}