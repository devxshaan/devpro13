<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            // ── Identity ───────────────────────────────
            $table->string('plan_key', 8)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // ── Pricing (FIXED & SAFE) ─────────────────
            // NULL = not set, 0 = Free plan, >0 = paid plan
            $table->decimal('price', 10, 2)->default(0);

            // Always required for consistency (no NULL confusion)
            $table->string('currency', 3)->default('USD');

            $table->enum('billing_cycle', [
                'one_time',
                'daily',
                'weekly',
                'monthly',
                'yearly',
                'per_use',
            ])->default('monthly');

            // ── Trial ──────────────────────────────────
            $table->unsignedInteger('trial_days')->default(0);

            // ── Limits ─────────────────────────────────
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_products')->nullable();
            $table->unsignedInteger('max_orders')->nullable();

            // ── Display ────────────────────────────────
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            // ── Flexible Data ──────────────────────────
            $table->json('features')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

            // ── PERFORMANCE INDEXES ────────────────────
            $table->index(['is_active', 'sort_order']);
            $table->index(['billing_cycle']);
            $table->index(['price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};