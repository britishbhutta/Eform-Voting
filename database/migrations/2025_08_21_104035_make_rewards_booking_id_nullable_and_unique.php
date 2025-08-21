<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            // Drop FK if exists
            try {
                $table->dropForeign(['booking_id']);
            } catch (\Throwable $e) {
                // ignore when FK missing
            }

            // If column exists and is not nullable, change it.
            if (Schema::hasColumn('rewards', 'booking_id')) {
                // To alter column's nullability you may need doctrine/dbal.
                // We'll try a safe approach: drop and re-add column.
                $table->dropColumn('booking_id');
            }

            // Recreate as nullable foreignId with unique constraint (one-to-one)
            $table->foreignId('booking_id')
                ->nullable()
                ->after('description')
                ->constrained('bookings')
                ->nullOnDelete();

            $table->unique('booking_id');
        });
    }

    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            try {
                $table->dropUnique(['booking_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['booking_id']);
            } catch (\Throwable $e) {
            }
            if (Schema::hasColumn('rewards', 'booking_id')) {
                $table->dropColumn('booking_id');
            }
            // Optionally recreate booking_id non-nullable — skip for safety.
        });
    }
};
