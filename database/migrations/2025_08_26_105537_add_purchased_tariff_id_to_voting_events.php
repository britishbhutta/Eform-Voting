<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voting_events', function (Blueprint $table) {
            // ensure column doesn't already exist
            if (! Schema::hasColumn('voting_events', 'purchased_tariff_id')) {
                $table->foreignId('purchased_tariff_id')
                    ->nullable()
                    ->constrained('purchased_tariffs')
                    ->nullOnDelete()
                    ->after('booking_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('voting_events', function (Blueprint $table) {
            if (Schema::hasColumn('voting_events', 'purchased_tariff_id')) {
                $table->dropForeign(['purchased_tariff_id']);
                $table->dropColumn('purchased_tariff_id');
            }
        });
    }
};
