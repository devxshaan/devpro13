<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            // ── Identity ───────────────────────────────
            $table->string('refund_key', 12)->unique();

            // ── Relations ──────────────────────────────
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');

            $table->foreignId('payment_id')
                ->constrained('payments')
                ->onDelete('restrict');

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->onDelete('restrict');

            $table->foreignId('requested_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('restrict');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('restrict');

            $table->foreignId('processed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('restrict');

            // ── AMOUNT (FIXED FOR SAAS) ────────────────
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');

            // ── Type & Source ──────────────────────────
            $table->enum('refund_type', ['gateway', 'manual', 'store_credit'])
                ->default('gateway');

            $table->enum('initiated_via', ['user_request', 'admin_manual', 'system_auto'])
                ->default('admin_manual');

            // ── Reason ────────────────────────────────
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();

            // ── Gateway ───────────────────────────────
            $table->string('gateway')->nullable();
            $table->string('gateway_refund_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->char('token', 36)->nullable();

            // ── Status ────────────────────────────────
            $table->enum('status', [
                'requested',
                'pending',
                'approved',
                'processing',
                'completed',
                'rejected',
                'failed',
                'cancelled',
            ])->default('pending');

            // ── Timeline ──────────────────────────────
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // ── Extra ────────────────────────────────
            $table->json('metadata')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // ── INDEXES ────────────────────────────────
            $table->index(['user_id', 'status']);
            $table->index(['payment_id']);
            $table->index(['order_id']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};