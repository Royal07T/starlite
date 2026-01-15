<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-5">
                <div class="card-header">
                    <h3>Request Session for {{ $course->title }}</h3>
                </div>
                <div class="card-body">
                    <p>Sessions Remaining: <strong>{{ $enrollment->sessions_remaining }}</strong></p>
                    
                    @if (session()->has('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="requestBooking">
                        <div class="form-group mb-3">
                            <label>Date</label>
                            <input type="date" wire:model="bookingDate" class="form-control" min="{{ date('Y-m-d') }}">
                            @error('bookingDate') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Time</label>
                            <input type="time" wire:model="bookingTime" class="form-control">
                            @error('bookingTime') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Notes (Optional)</label>
                            <textarea wire:model="description" class="form-control"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Request Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
