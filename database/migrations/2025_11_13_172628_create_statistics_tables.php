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
        Schema::create('hourly_occupancy_stats', function (Blueprint $table) {
            $table->foreignIdFor(Restaurant::class)->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->tinyInteger('hour');
            $table->unsignedInteger('reservations_count')->default(0);
            $table->unsignedInteger('guests_count')->default(0);
            $table->unsignedTinyInteger('occupancy_percent')->default(0);
            $table->primary(['restaurant_id', 'date', 'hour']);
            $table->timestamps();
        });

        Schema::create('daily_occupancy_stats', function (Blueprint $table) {
            $table->foreignIdFor(Restaurant::class)->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('total_reservations')->default(0);
            $table->unsignedInteger('total_guests')->default(0);
            $table->unsignedTinyInteger('average_occupancy_percent')->default(0);
            $table->time('peak_hour')->nullable();
            $table->time('off_peak_hour')->nullable();
            $table->primary(['restaurant_id', 'date']);
            $table->timestamps();
        });

        Schema::create('monthly_occupancy_stats', function (Blueprint $table) {
            $table->foreignIdFor(Restaurant::class)->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->tinyInteger('month');
            $table->unsignedInteger('total_reservations')->default(0);
            $table->unsignedInteger('total_guests')->default(0);
            $table->unsignedTinyInteger('average_occupancy_percent')->default(0);
            $table->date('peak_day')->nullable();
            $table->date('off_peak_day')->nullable();
            $table->primary(['restaurant_id', 'year', 'month']);
            $table->timestamps();
        });

        Schema::create('yearly_occupancy_stats', function (Blueprint $table) {
            $table->foreignIdFor(Restaurant::class)->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->unsignedInteger('total_reservations')->default(0);
            $table->unsignedInteger('total_guests')->default(0);
            $table->unsignedTinyInteger('average_occupancy_percent')->default(0);
            $table->tinyInteger('peak_month')->nullable();
            $table->tinyInteger('off_peak_month')->nullable();
            $table->primary(['restaurant_id', 'year']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hourly_occupancy_stats');
        Schema::dropIfExists('daily_occupancy_stats');
        Schema::dropIfExists('monthly_occupancy_stats');
        Schema::dropIfExists('yearly_occupancy_stats');
    }
};
