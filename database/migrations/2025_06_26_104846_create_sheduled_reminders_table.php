<?php

use App\Models\Reminder_type;
use App\Models\Reservation;
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
        Schema::create('sheduled_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Reservation::class)->constrained()->cascadeOnDelete();
            $table->timestampTz('execute_at');
            $table->foreignIdFor(Reminder_type::class)->constrained();
            $table->smallInteger('attempts')->default(0);
            $table->string('status', 20)->default('pending');
            $table->timestampsTz();

            $table->index('execute_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sheduled_reminders');
    }
};
