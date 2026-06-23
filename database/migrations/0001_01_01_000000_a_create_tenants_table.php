<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();

            $table->string('tenant_key', 8)->unique();
            $table->string('name');
            $table->string('slug')->unique();

            $table->string('domain')->nullable()->unique();
            $table->string('frontend_url')->nullable();

            $table->enum('status', ['active', 'inactive', 'suspended'])
                ->default('active');

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};