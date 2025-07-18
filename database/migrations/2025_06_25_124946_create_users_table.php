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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 50)->unique();
            $table->string('password');
            $table->string('name', 100)->nullable();
            $table->string('phone', 20)->nullable()->unique();
            $table->string('email_verified_at')->nullable();
            $table->string('phone_verified_at')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
