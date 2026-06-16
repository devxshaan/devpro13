<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // ── Identity ───────────────────────────────
            $table->string('subscription_key', 8)->unique();

            // ── Relations ──────────────────────────────
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('plan_id')
                ->constrained('plans')
                ->onDelete('restrict');

            // ── Pricing Snapshot (FIXED) ───────────────
            // IMPORTANT:
            // 0 = free subscription snapshot allowed
            // always store value at time of subscription
            $table->decimal('price_at_subscription', 10, 2)->default(0);

            // FIX: currency should NEVER be nullable in SaaS billing
            $table->string('currency', 3)->default('USD');

            // ── Status ────────────────────────────────
            $table->enum('status', [
                'trial',
                'active',
                'paused',
                'cancelled',
                'expired',
                'past_due',
            ])->default('trial');

            // ── Timeline ──────────────────────────────
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resumed_at')->nullable();

            // ── Gateway ───────────────────────────────
            $table->string('gateway')->nullable();
            $table->string('gateway_subscription_id')->nullable();

            // ── Flexible ──────────────────────────────
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── INDEXES (IMPORTANT FIX) ────────────────
            $table->index(['user_id', 'status']);
            $table->index(['plan_id']);
            $table->index(['ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};