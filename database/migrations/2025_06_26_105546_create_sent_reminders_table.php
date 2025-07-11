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
        Schema::create('sent_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Reservation::class)->constrained()->cascadeOnDelete();
            $table->timestampTz('sent_at');
            $table->string('recipient_email', 50);
            $table->foreignIdFor(Reminder_type::class)->constrained();
            $table->string('status', 20);
            $table->text('error_message')->nullable();
            $table->timestampTz('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_reminders');
    }
};
