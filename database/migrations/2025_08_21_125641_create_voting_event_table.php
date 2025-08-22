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
        Schema::create('voting_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tariff_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('title');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->string('question')->nullable();
            $table->integer('status')->default(1); // 1 = active, 0 = inactive
            $table->timestamps();
            $table->foreign('tariff_id')
                ->references('id')
                ->on('tariffs')
                ->onDelete('set null');
            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings')
                ->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_events');
    }
};
