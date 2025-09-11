<?php

use App\Models\Restaurant;
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
        Schema::create('occupancy_stats', function (Blueprint $table) {
            $table->foreignIdFor(Restaurant::class)->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('hour');
            $table->unsignedTinyInteger('occupacity_percent');
            $table->primary(['restaurant_id', 'date', 'hour']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupancy_stats');
    }
};
