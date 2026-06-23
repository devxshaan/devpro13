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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('dob')->nullable(); 
            $table->string('phone', 20)->nullable();
            $table->string('gender', 50)->nullable();
            $table->text('bio')->nullable();  
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('avatar_url')->nullable();
            $table->foreignId('user_id')
            ->constrained('users')
            ->onDelete('cascade');
            $table->boolean('is_phone_private')->default(true);
            $table->boolean('is_dob_private')->default(true);
            $table->boolean('is_address_private')->default(true);
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
