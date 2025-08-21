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
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('tariff_id')->nullable()->after('id');
            $table->enum('is_completed', [0, 1])
            ->default(0)
            ->comment('0-Incomplete, 1-Completed')
            ->after('payment_method');
            $table->string('booking_status')->nullable()->after('is_completed');

            $table->foreign('tariff_id')
                  ->references('id')
                  ->on('tariffs')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            //
        });
    }
};
