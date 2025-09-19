<?php

use App\Models\Restaurant_chain;
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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('address', 100)->unique();
            $table->string('type_kitchen', 100)->nullable();
            $table->string('price_range', 20)->nullable();
            $table->time('weekdays_opens_at')->nullable();
            $table->time('weekdays_closes_at')->nullable();
            $table->time('weekend_opens_at')->nullable();
            $table->time('weekend_closes_at')->nullable();
            $table->text('cancellation_policy')->nullable();
            $table->foreignId('restaurant_chain_id')
                ->nullable()
                ->constrained('restaurant_chains', 'id')
                ->nullOnDelete();

            $table->foreignId('status_id')
                ->nullable()
                ->constrained('restaurant_statuses', 'id')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type_kitchen', 'price_range']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
