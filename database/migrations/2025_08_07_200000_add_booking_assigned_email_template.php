<?php

use App\Models\EmailTemplate;
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
        // Add bookingAssigned email template for tutors
        EmailTemplate::firstOrCreate([
            'type' => 'bookingAssigned',
            'title' => 'Booking Assignment Notification',
            'role' => 'tutor',
            'status' => 'active',
            'content' => [
                'info' => 'Variables: {tutorName}, {studentName}, {subjectName}, {bookingDate}, {bookingTime}, {bookingType}, {bookingUrl}',
                'subject' => 'A booking has been {bookingType} to you',
                'greeting' => 'Hello {tutorName},',
                'content' => '
                    <p>A booking has been {bookingType} to you.</p>
                    <p><strong>Student:</strong> {studentName}<br>
                    <strong>Subject:</strong> {subjectName}<br>
                    <strong>Date:</strong> {bookingDate}<br>
                    <strong>Time:</strong> {bookingTime}</p>
                    <p>Please log in to your account to view the booking details and prepare for the session.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{bookingUrl}" style="background-color: #4CAF50; color: white; padding: 12px 20px; text-decoration: none; border-radius: 4px; font-weight: bold;">View Booking</a>
                    </div>
                    <p>Thank you for being part of our tutoring community!</p>
                '
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        EmailTemplate::where('type', 'bookingAssigned')->where('role', 'tutor')->delete();
    }
};
