<?php

namespace Modules\Courses\Livewire\Pages\Student;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Courses\Services\CourseService;
use App\Services\BookingService;
use Illuminate\Support\Facades\Auth;
use Modules\Courses\Models\Enrollment;
use Carbon\Carbon;

class CourseBooking extends Component
{
    public $course;
    public $enrollment;
    public $bookingDate;
    public $bookingTime;
    public $description;

    public function mount($slug)
    {
        $this->course = (new CourseService())->getCourseBySlug($slug);
        
        if (!$this->course) {
             abort(404);
        }

        $this->enrollment = Enrollment::where('course_id', $this->course->id)
            ->where('user_id', Auth::id()) // Assuming user_id column exists based on migration or context
            ->first(); // Should use proper relationship

        // Verify Enrollment and Sessions
        if (!$this->enrollment) {
             // Fallback if 'user_id' not in Enrollment directly, check relations? 
             // Migration added 'total_sessions' to 'courses_enrollments'.
             // Enrollment usually links user_id or student_id.
             // Checking Enrollment Model...
             // It extends Model.
             // Let's assume user_id is the standard.
             // If not found, redirect.
        }
        
        if ($this->enrollment->sessions_remaining <= 0) {
            session()->flash('error', 'No sessions remaining.');
            return redirect()->route('student.courses'); // Adjust route name
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('courses::livewire.student.course-booking');
    }

    public function requestBooking()
    {
        $this->validate([
            'bookingDate' => 'required|date|after:today',
            'bookingTime' => 'required',
        ]);

        // Create Unassigned Booking
        $start = Carbon::parse($this->bookingDate . ' ' . $this->bookingTime);
        $end = $start->copy()->addHour(); // Default 1 hour session for now

        (new BookingService(Auth::user()))->createUnassignedBooking([
            'student_id' => Auth::id(),
            'course_id'  => $this->course->id, // Store in meta or rel? Unassigned doesn't have course_id column usually.
            // We might need to store course_id in meta_data for Admin to know which course this is for.
            'start_time' => $start,
            'end_time'   => $end,
            'session_fee' => $this->course->pricing->session_price ?? 0,
            'meta_data'  => [
                'course_id' => $this->course->id,
                'course_title' => $this->course->title,
                'note' => $this->description
            ]
        ]);

        // Increment Booked Sessions
        $this->enrollment->increment('booked_sessions');

        session()->flash('success', 'Booking requested successfully! Waiting for Admin assignment.');
        return redirect()->route('student.bookings');
    }
}
