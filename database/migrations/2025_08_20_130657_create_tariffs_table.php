<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->string('note')->nullable();
            $table->json('features')->nullable();
            $table->integer('price_cents');
            $table->string('currency')->default('EUR');
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
 