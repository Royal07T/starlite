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
        Schema::table('courses_pricings', function (Blueprint $table) {
            $table->decimal('session_price', 10, 2)->nullable()->after('price');
            $table->integer('min_sessions')->default(2)->after('session_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses_pricings', function (Blueprint $table) {
            $table->dropColumn(['session_price', 'min_sessions']);
        });
    }
};
