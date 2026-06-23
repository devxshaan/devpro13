<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // ── Identity ───────────────────────────────
            $table->string('order_key', 12)->unique();

            // ── Relations ──────────────────────────────
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');

            // ── Polymorphic ────────────────────────────
            $table->nullableMorphs('orderable');

            // ── Pricing ────────────────────────────────
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->string('currency', 3)->default('USD');

            // ── Coupon ────────────────────────────────
            $table->string('coupon_code')->nullable();

            // ── Status ────────────────────────────────
            $table->enum('status', [
                'draft',
                'pending',
                'confirmed',
                'processing',
                'completed',
                'cancelled',
                'refunded',
                'failed',
            ])->default('draft');

            // ── Fulfillment ───────────────────────────
            $table->enum('fulfillment_type', [
                'digital',
                'physical',
                'service',
                'pickup',
            ])->nullable();

            // ── Address ───────────────────────────────
            $table->json('shipping_address')->nullable();

            // ── Timeline ──────────────────────────────
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // ── Extra ────────────────────────────────
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // ── SAFE INDEXES ──────────────────────────
            $table->index(['user_id', 'status']);
            $table->index(['created_at']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};