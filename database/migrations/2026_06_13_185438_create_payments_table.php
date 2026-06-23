<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // ── Identity ───────────────────────────────
            $table->string('payment_key', 8)->unique();

            // ── Relations ──────────────────────────────
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');

            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained('subscriptions')
                ->onDelete('restrict');

            // ── Polymorphic ────────────────────────────
            $table->nullableMorphs('payable');

            // ── Gateway ────────────────────────────────
            $table->string('gateway');
            $table->string('gateway_payment_id')->nullable();
            $table->string('gateway_order_id')->nullable();
            $table->json('gateway_response')->nullable();

            // ── AMOUNT (FIXED FOR SAAS) ────────────────
            $table->decimal('amount', 10, 2);

            // Refund safe + always numeric stable
            $table->decimal('amount_refunded', 10, 2)->default(0);

            // FIX: currency should NEVER be nullable in payments
            $table->string('currency', 3)->default('USD');

            // ── Payment Method ─────────────────────────
            $table->string('payment_method')->nullable();

            // ── Status ────────────────────────────────
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
                'refunded',
                'partial_refund'
            ])->default('pending');

            // ── Timeline ──────────────────────────────
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // ── Extra ────────────────────────────────
            $table->text('notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // ── INDEXES (IMPORTANT FIX) ───────────────
            $table->index(['user_id', 'status']);
            $table->index(['subscription_id']);
            $table->index(['gateway']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};