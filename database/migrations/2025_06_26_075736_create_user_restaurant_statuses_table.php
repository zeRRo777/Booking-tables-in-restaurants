<?php

use App\Models\Restaurant;
use App\Models\User;
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
        Schema::create('user_restaurant_statuses', function (Blueprint $table) {
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Restaurant::class)->constrained()->cascadeOnDelete();
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason');
            $table->foreignId('blocked_by')->constrained('users', 'id')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['user_id', 'restaurant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_restaurant_statuses');
    }
};
