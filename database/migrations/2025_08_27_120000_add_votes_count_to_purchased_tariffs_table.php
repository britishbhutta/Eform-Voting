<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchased_tariffs', function (Blueprint $table) {
            $table->integer('votes_count')->default(0)->after('remaining_votes');
        });
    }

    public function down(): void
    {
        Schema::table('purchased_tariffs', function (Blueprint $table) {
            $table->dropColumn('votes_count');
        });
    }
};


