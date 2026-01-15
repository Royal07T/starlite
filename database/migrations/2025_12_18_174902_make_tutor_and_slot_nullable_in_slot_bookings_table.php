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
        Schema::table('slot_bookings', function (Blueprint $table) {
            $table->foreignId('tutor_id')->nullable()->change();
            $table->foreignId('user_subject_slot_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slot_bookings', function (Blueprint $table) {
            $table->foreignId('tutor_id')->nullable(false)->change();
            $table->foreignId('user_subject_slot_id')->nullable(false)->change();
        });
    }
};
