<?php

use App\Models\NotificationTemplate;
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
        // Add bookingassigned notification template for tutors
        NotificationTemplate::firstOrCreate([
            'type' => 'bookingassigned',
            'title' => 'Booking Assignment Notification',
            'role' => 'tutor',
            'status' => 'active',
            'content' => [
                'info' => 'Variables: {tutorName}, {studentName}, {subjectName}, {bookingDate}, {bookingTime}, {bookingType}',
                'title' => 'Booking {bookingType}',
                'content' => 'A booking for {subjectName} has been {bookingType} to you with {studentName} on {bookingDate} at {bookingTime}.',
                'has_link' => true,
                'link_text' => 'View Booking',
                'link_target' => '{bookingUrl}'
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        NotificationTemplate::where('type', 'bookingassigned')->where('role', 'tutor')->delete();
    }
};
