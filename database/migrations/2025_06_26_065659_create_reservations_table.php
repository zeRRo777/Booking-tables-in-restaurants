<?php

use App\Models\Reminder_type;
use App\Models\ReminderType;
use App\Models\Restaurant;
use App\Models\Table;
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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Table::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Restaurant::class)->constrained()->cascadeOnDelete();
            $table->integer('count_people');
            $table->string('special_wish')->nullable();
            $table->foreignId('status_id')->constrained('reservation_statuses', 'id');
            $table->timestamp('date_start');
            $table->timestamp('date_end');
            $table->foreignIdFor(ReminderType::class)->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['restaurant_id', 'date_start']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
