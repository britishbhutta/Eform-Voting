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
        Schema::create('voting_event_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voting_event_id');
            $table->string('option_text');
            $table->integer('votes_count')->default(0);
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->foreign('voting_event_id')
                ->references('id')
                ->on('voting_events')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_event_options');
    }
};
