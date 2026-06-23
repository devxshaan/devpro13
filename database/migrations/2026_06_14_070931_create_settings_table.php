<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();  
            $table->text('value')->nullable();      
            $table->string('label');               
            $table->text('description')->nullable(); 
            $table->string('type')->default('text'); 
            $table->json('options')->nullable();    
            $table->string('group')->default('general');
            $table->boolean('is_deletable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
