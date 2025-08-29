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
        Schema::create('voting_event_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('voting_event_option_id')->constrained('voting_event_options')->cascadeOnDelete();
            $table->string('email');
            $table->timestamps();

            $table->unique(['voting_event_id', 'email']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_event_votes');
    }
};


