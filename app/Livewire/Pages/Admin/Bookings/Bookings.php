<?php

namespace App\Livewire\Pages\Admin\Bookings;

use App\Models\SlotBooking;
use App\Services\WalletService;
use App\Services\OrderService;
use App\Services\SubjectService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Bookings extends Component
{
    use WithPagination;

    public      $search             = '';
    public      $sortby             = 'desc';
    public      $status             = '';
    public      $user;
    public      $subjects;
    public      $selectedSubject;
    public      $subjectGroups;
    public      $selectedSubGroup;
    
    // Tutor assignment properties
    public      $showAssignModal    = false;
    public      $selectedBooking    = null;
    public      $tutors             = [];
    public      $selectedTutor      = null;
    public      $states             = [];
    public      $cities             = [];
    public      $selectedState      = null;
    public      $selectedCity       = null;
    public      $searchTutor        = '';
    public      $isLoading          = false;
    public      $bookingDetails     = null;

    // Properties for credit tutor modal
    public      $showCreditModal    = false;
    public      $selectedBookingForCredit    = null;
    public      $creditAmount       = null;

    private ?OrderService  $orderService        = null;
    private ?SubjectService  $subjectService    = null;


    public function boot()
    {
        $this->user             = Auth::user();
        $this->orderService     = new OrderService();
        $this->subjectService   = new SubjectService();
    }

    public function mount()
    {

        $this->subjects         = $this->subjectService->getSubjects();
        $this->subjectGroups    = $this->subjectService->getSubjectGroups();

        $this->dispatch('initSelect2', target: '.am-select2' );
    }

    #[Layout('layouts.admin-app')]
    public function render()
    {
        $query = SlotBooking::with(['student.profile', 'tutor.profile', 'orderItem.order']);
        
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->whereHas('student.profile', function ($sq) {
                    $sq->where('first_name', 'like', '%' . $this->search . '%')
                       ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })->orWhereHas('tutor.profile', function ($tq) {
                    $tq->where('first_name', 'like', '%' . $this->search . '%')
                       ->orWhere('last_name', 'like', '%' . $this->search . '%');
                });
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->selectedSubject) {
             // Filter by meta_data or related subject?
             // Unassigned bookings have course info in meta_data.
             // Assigned bookings have slot->subject.
             // This particular filter might be tricky for unassigned. 
             // For now, let's keep it simple or check relationship if exists.
        }

        $bookings = $query->orderBy('id', $this->sortby)->paginate(setting('_general.per_page_opt') ?? 10);
        
        return view('livewire.pages.admin.bookings.bookings', compact('bookings'));
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['status', 'search', 'sortby', 'selectedSubject','selectedSubGroup'])) {
            $this->resetPage();
        }
        
        if ($propertyName === 'selectedState') {
            $this->loadCities();
        }
        
        if (in_array($propertyName, ['selectedState', 'selectedCity', 'searchTutor'])) {
            $this->loadFilteredTutors();
        }
    }
    
    /**
     * Open the tutor assignment modal for a specific booking
     */
    public function openAssignModal($bookingId)
    {
        $this->isLoading = true;
        $this->selectedBooking = $bookingId;
        
        // Load booking details
        $booking = \App\Models\SlotBooking::with(['student.profile', 'subject'])->find($bookingId);
        if ($booking) {
            $this->bookingDetails = $booking;
            
            // If booking already has a tutor, preselect that tutor
            if ($booking->tutor_id) {
                $this->selectedTutor = $booking->tutor_id;
            } else {
                $this->selectedTutor = null;
            }
        }
        
        $this->loadStates();
        $this->loadTutors();
        $this->showAssignModal = true;
        $this->isLoading = false;
    }
    
    /**
     * Load all available states from the database
     */
    public function loadStates()
    {
        $this->states = \App\Models\CountryState::orderBy('name')->get();
    }
    
    /**
     * Load cities based on the selected state
     */
    public function loadCities()
    {
        if ($this->selectedState) {
            // Get unique cities from addresses where state_id matches the selected state
            $this->cities = \App\Models\Address::where('state_id', $this->selectedState)
                ->distinct('city')
                ->whereNotNull('city')
                ->pluck('city')
                ->toArray();
        } else {
            $this->cities = [];
        }
        
        $this->selectedCity = null;
    }
    
    /**
     * Load all tutors (users with the tutor role)
     */
    public function loadTutors()
    {
        $query = \App\Models\User::role('tutor')
            ->with(['profile', 'address.state', 'address.country'])
            ->whereHas('profile');
            
        $this->tutors = $query->get();
        $this->loadFilteredTutors();
    }
    
    /**
     * Filter tutors based on selected state, city, and search term
     */
    public function loadFilteredTutors()
    {
        $query = \App\Models\User::role('tutor')
            ->with(['profile', 'address.state', 'address.country'])
            ->whereHas('profile');
        
        // Filter by state if selected
        if ($this->selectedState) {
            $query->whereHas('address', function($q) {
                $q->where('state_id', $this->selectedState);
            });
        }
        
        // Filter by city if selected
        if ($this->selectedCity) {
            $query->whereHas('address', function($q) {
                $q->where('city', $this->selectedCity);
            });
        }
        
        // Filter by search term if provided
        if ($this->searchTutor) {
            $query->whereHas('profile', function($q) {
                $q->where('first_name', 'like', '%' . $this->searchTutor . '%')
                  ->orWhere('last_name', 'like', '%' . $this->searchTutor . '%');
            });
        }
        
        $this->tutors = $query->get();
    }
    
    /**
     * Assign the selected booking to the selected tutor
     */
    public function assignTutor()
    {
        if (!$this->selectedBooking || !$this->selectedTutor) {
            $this->dispatch('showAlert', type: 'error', message: 'Please select a tutor to assign');
            return;
        }
        
        $this->isLoading = true;
        
        try {
            $booking = \App\Models\SlotBooking::with(['student.profile', 'subject'])->find($this->selectedBooking);
            
            if (!$booking) {
                $this->dispatch('showAlert', type: 'error', message: 'Booking not found');
                $this->isLoading = false;
                return;
            }
            
            // Get tutor details for the notification message
            $tutor = \App\Models\User::with('profile')->find($this->selectedTutor);
            $tutorName = $tutor->profile->full_name ?? 'Selected tutor';
            
            // Check if this is a reassignment
            $wasReassigned = $booking->tutor_id && $booking->tutor_id != $this->selectedTutor;
            
            // Update the tutor_id for the booking
            $booking->tutor_id = $this->selectedTutor;
            $booking->save();
            
            // Send notifications to the assigned tutor
            $this->sendTutorNotifications($booking, $tutor, $wasReassigned);
            
            // Prepare success message based on whether this was a new assignment or reassignment
            $successMessage = $wasReassigned 
                ? "Booking reassigned to {$tutorName} successfully" 
                : "Booking assigned to {$tutorName} successfully";
            
            $this->dispatch('showAlert', type: 'success', message: $successMessage);
            $this->closeAssignModal();
        } catch (\Exception $e) {
            $this->dispatch('showAlert', type: 'error', message: 'Error assigning booking: ' . $e->getMessage());
        }
        
        $this->isLoading = false;
    }
    
    /**
     * Send notifications to the tutor when a booking is assigned
     */
    protected function sendTutorNotifications($booking, $tutor, $wasReassigned)
    {
        // Format the booking date and time for notifications
        $bookingDate = \Carbon\Carbon::parse($booking->start_time)->format('F j, Y');
        $startTime = \Carbon\Carbon::parse($booking->start_time)->format('g:i a');
        $endTime = \Carbon\Carbon::parse($booking->end_time)->format('g:i a');
        
        // Prepare data for email notification
        $emailData = [
            'tutorName' => $tutor->profile->full_name,
            'studentName' => $booking->student->profile->full_name ?? 'Student',
            'subjectName' => $booking->subject->name ?? 'Subject',
            'bookingDate' => $bookingDate,
            'bookingTime' => "{$startTime} - {$endTime}",
            'bookingType' => $wasReassigned ? 'reassigned' : 'assigned',
            'bookingUrl' => route('tutor.bookings')
        ];
        
        // Prepare data for in-app notification
        $notifyData = [
            'tutorName' => $tutor->profile->full_name,
            'studentName' => $booking->student->profile->full_name ?? 'Student',
            'subjectName' => $booking->subject->name ?? 'Subject',
            'bookingDate' => $bookingDate,
            'bookingTime' => "{$startTime} - {$endTime}",
            'bookingType' => $wasReassigned ? 'reassigned' : 'assigned',
            'bookingUrl' => route('tutor.bookings')
        ];
        
        // Send email notification
        dispatch(new \App\Jobs\SendNotificationJob('bookingAssigned', $tutor, $emailData));
        
        // Send in-app notification
        dispatch(new \App\Jobs\SendDbNotificationJob('bookingassigned', $tutor, $notifyData));
    }
    
    /**
     * Close the assignment modal and reset related properties
     */
    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->selectedBooking = null;
        $this->selectedTutor = null;
        $this->selectedState = null;
        $this->selectedCity = null;
        $this->searchTutor = '';
        $this->tutors = [];
        $this->cities = [];
        $this->states = [];
        $this->bookingDetails = null;
        $this->isLoading = false;
    }
    
    /**
     * Open the credit tutor modal for a specific booking
     */
    public function openCreditTutorModal($bookingId)
    {
        $this->isLoading = true;
        $this->selectedBookingForCredit = $bookingId;
        $this->showCreditModal = true;
        $this->creditAmount = null;
        
        // Load booking details for display in the modal
        $this->bookingDetails = SlotBooking::with(['student.profile', 'tutor.profile', 'subject', 'orderItem'])
            ->find($bookingId);
            
        $this->isLoading = false;
    }
    
    /**
     * Close the credit tutor modal and reset related properties
     */
    public function closeCreditTutorModal()
    {
        $this->showCreditModal = false;
        $this->selectedBookingForCredit = null;
        $this->creditAmount = null;
        $this->bookingDetails = null;
        $this->isLoading = false;
    }
    
    /**
     * Credit the tutor with the specified amount
     */
    public function creditTutor(WalletService $walletService)
    {
        // Validate the credit amount
        $this->validate([
            'creditAmount' => 'required|numeric|min:0.01',
        ]);
        
        $this->isLoading = true;
        
        try {
            $booking = SlotBooking::with(['tutor', 'orderItem.order'])
                ->find($this->selectedBookingForCredit);
            
            if (!$booking || !$booking->tutor_id) {
                $this->dispatch('showAlert', type: 'error', message: 'Booking or tutor not found');
                $this->isLoading = false;
                return;
            }
            
            // Add the credit amount to the tutor's wallet
            $walletService->addFunds(
                $booking->tutor_id, 
                $this->creditAmount, 
                $booking->orderItem->order_id ?? null
            );
            
            // Send notification to the tutor about the credit
            $this->sendTutorCreditNotification($booking, $this->creditAmount);
            
            $this->dispatch('showAlert', type: 'success', message: 'Tutor credited successfully');
            $this->closeCreditTutorModal();
        } catch (\Exception $e) {
            $this->dispatch('showAlert', type: 'error', message: 'Error crediting tutor: ' . $e->getMessage());
        }
        
        $this->isLoading = false;
    }
    
    /**
     * Send notification to the tutor about the credit
     */
    protected function sendTutorCreditNotification($booking, $amount)
    {
        $tutor = $booking->tutor;
        $formattedAmount = formatAmount($amount);
        
        $emailData = [
            'tutorName' => $tutor->profile->full_name,
            'bookingId' => $booking->id,
            'amount' => $formattedAmount,
            'walletUrl' => route('tutor.wallet')
        ];
        
        $notifyData = $emailData;
        
        // You would need to create these notification templates
        // For now, we'll use generic notifications
        dispatch(new \App\Jobs\SendNotificationJob('general', $tutor, [
            'subject' => 'Payment Credited to Your Wallet',
            'greeting' => 'Hello ' . $tutor->profile->full_name . ',',
            'content' => "<p>An amount of {$formattedAmount} has been credited to your wallet for booking #{$booking->id}.</p><p>You can view your updated wallet balance by visiting your wallet page.</p>"
        ]));
        
        dispatch(new \App\Jobs\SendDbNotificationJob('general', $tutor, [
            'title' => 'Payment Received',
            'content' => "An amount of {$formattedAmount} has been credited to your wallet for booking #{$booking->id}.",
            'has_link' => true,
            'link_text' => 'View Wallet',
            'link_target' => route('tutor.wallet')
        ]));
    }
}
