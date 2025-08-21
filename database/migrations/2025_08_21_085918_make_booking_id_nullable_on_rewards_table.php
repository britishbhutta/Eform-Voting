<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {

            try {
                $table->dropForeign(['booking_id']);
            } catch (\Throwable $e) {
                // ignore
            }

            // Drop the column and re-add it nullable with FK (nullOnDelete)
            if (Schema::hasColumn('rewards', 'booking_id')) {
                $table->dropColumn('booking_id');
            }

            // Recreate as nullable
            $table->foreignId('booking_id')
                ->nullable()
                ->after('description')
                ->constrained('bookings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            try {
                $table->dropForeign(['booking_id']);
            } catch (\Throwable $e) {
                // ignore
            }

            if (Schema::hasColumn('rewards', 'booking_id')) {
                $table->dropColumn('booking_id');
            }

            // Recreate as non-nullable (original state) — adjust as needed
            $table->foreignId('booking_id')
                ->after('description')
                ->constrained('bookings')
                ->onDelete('cascade');
        });
    }
};
