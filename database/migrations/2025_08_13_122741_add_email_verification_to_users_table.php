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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('remember_token');
            $table->string('email_verification_code')->nullable()->after('is_active');
            $table->timestamp('email_verification_sent_at')->nullable()->after('email_verification_code');
            $table->timestamp('email_verification_expires_at')->nullable()->after('email_verification_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'email_verification_code',
                'email_verification_sent_at',
                'email_verification_expires_at',
            ]);
        });
    }
};
