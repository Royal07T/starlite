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
        Schema::table('courses_enrollments', function (Blueprint $table) {
            $table->integer('total_sessions')->default(0)->after('course_id');
            $table->integer('booked_sessions')->default(0)->after('total_sessions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses_enrollments', function (Blueprint $table) {
            $table->dropColumn(['total_sessions', 'booked_sessions']);
        });
    }
};
