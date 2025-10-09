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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tariff_id');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('company_id')->nullable();
            $table->string('tax_vat_no')->nullable();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->string('booking_reference')->nullable();
            $table->string('price')->nullable();
            $table->string('currency')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('booking_status')->nullable();
            $table->enum('is_completed', [0, 1])
            ->default(0)
            ->comment('0 = In Completed, 1 = Completed');
            $table->enum('invoice_issue', [0, 1])
            ->default(0)
            ->comment('0 = Not Issue, 1 = Issued');
            $table->enum('remember_me', [0, 1])
            ->default(0)
            ->comment('0 = Not Remember, 1 = Remembered');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('tariff_id')->references('id')->on('tariffs')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
