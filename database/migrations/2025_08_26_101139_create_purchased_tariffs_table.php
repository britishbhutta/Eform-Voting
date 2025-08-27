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
        Schema::create('purchased_tariffs', function (Blueprint $table) {
            $table->id();

            // relations
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('tariff_id')->nullable()->constrained('tariffs')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // vote counts
            $table->integer('total_votes')->default(0);
            $table->integer('remaining_votes')->default(0);

            // token to map to QR / event
            $table->string('token')->unique();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Optional indexes for faster lookups
            $table->index(['user_id']);
            $table->index(['booking_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchased_tariffs');
    }
};
