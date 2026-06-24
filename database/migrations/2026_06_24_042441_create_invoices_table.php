<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');

            // ── Identity ───────────────────────────────
            $table->string('invoice_key', 12)->unique();
            $table->string('invoice_number')->unique(); // human-readable, e.g. INV-2026-000001

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

            
            $table->string('billed_to_name')->nullable();
            $table->string('billed_to_email')->nullable();
            $table->text('billed_to_address')->nullable();

            $table->string('item_description'); // e.g. "Basic Plan (Starter) - Monthly Subscription"
            $table->json('line_items')->nullable(); // future: multiple items support

            // ── Amounts (snapshot — match payment ke time) ──
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('USD');

            // ── Status ────────────────────────────────
            $table->enum('status', ['draft', 'issued', 'void'])
                ->default('issued');

            // ── File ──────────────────────────────────
            $table->string('pdf_path')->nullable();
            $table->boolean('emailed')->default(false);

            // ── Who generated it ─────────────────────
            $table->foreignId('generated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('restrict');

            $table->enum('generation_source', ['auto', 'manual'])
                ->default('auto');

            // ── Timeline ──────────────────────────────
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('voided_at')->nullable();

            // ── Extra ────────────────────────────────
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ────────────────────────────────
            $table->index(['user_id']);
            $table->index(['payment_id']);
            $table->index(['order_id']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};