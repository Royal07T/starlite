<main class="tb-main am-dispute-system am-booking-system">
    <div class ="row">
        <div class="col-lg-12 col-md-12">
            <div class="tb-dhb-mainheading">
                <h4> {{ __('general.all_booking') .' ('. $bookings->total() .')'}}</h4>
                <div class="tb-sortby">
                    <form class="tb-themeform tb-displistform">
                        <fieldset>
                            <div class="tb-themeform__wrap">
                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select">
                                        <select data-componentid="@this" class="am-select2 form-control" data-searchable="false" data-live='true' id="status" data-wiremodel="status" >
                                            <option value="" {{ $status == '' ? 'selected' : '' }} >{{ __('booking.all_bookings')  }}</option>
                                            <option value="pending" {{ $status == 'pending' ? 'selected' : '' }} >{{ __('booking.pending')  }}</option>
                                            <option value="complete" {{ $status == 'complete' ? 'selected' : '' }} >{{ __('booking.complete')  }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select">
                                        <select data-componentid="@this" class="am-select2" data-live='true' data-searchable="true" id="subject" data-wiremodel="selectedSubject">
                                            <option value="">{{ __('booking.select_subject')  }}</option>
                                            @foreach ($subjects as $subject)
                                            <option value="{{ $subject->name }}" {{ $subject->name == $selectedSubject ? 'selected' : '' }}>{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select">
                                        <select data-componentid="@this" class="am-select2" data-live='true' data-searchable="true" id="subject_group" data-wiremodel="selectedSubGroup">
                                            <option value="">{{ __('booking.select_subject_group')  }}</option>
                                            @foreach ($subjectGroups as $subjectGroup)
                                                <option value="{{ $subjectGroup->name }}" {{ $subjectGroup->name == $selectedSubGroup ? 'selected' : '' }}>{{ $subjectGroup->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select">
                                        <select data-componentid="@this" class="am-select2 form-control" data-searchable="false" data-live='true' id="sort_by" data-wiremodel="sortby" >
                                            <option value="asc" {{ $sortby == 'asc' ? 'selected' : '' }} >{{ __('general.asc')  }}</option>
                                            <option value="desc" {{ $sortby == 'desc' ? 'selected' : '' }} >{{ __('general.desc')  }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group tb-inputicon tb-inputheight">
                                    <i class="icon-search"></i>
                                    <input type="text" class="form-control" wire:model.live.debounce.500ms="search"  autocomplete="off" placeholder="{{ __('general.search') }}">
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
            <div class="am-disputelist_wrap">
                <div class="am-disputelist am-custom-scrollbar-y">
                    @if( !$bookings->isEmpty() )
                        <table class="tb-table @if(setting('_general.table_responsive') == 'yes') tb-table-responsive @endif">
                            <thead>
                                <tr>
                                    <th>{{ __('booking.id') }}</th>
                                    <th>{{ __('booking.transaction_id') }}</th>
                                    <th>{{ __('booking.subject') }}</th>
                                    <th>{{ __('booking.student_name') }}</th>
                                    <th>{{ __('booking.tutor_name') }}</th>
                                    <th>{{ __('booking.amount') }}</th>
                                    <th>{{ __('booking.tutor_payout') }}</th>
                                    <th>{{ __('booking.status') }}</th>
                                    <th>Assigned To</th>
                                    <th>Credit Tutor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookings as $booking)
                                    @php
                                        // Booking Details
                                        $orderItem = $booking->orderItem;
                                        $options   = $orderItem?->options ?? [];
                                        $subject   = $booking->meta_data['course_title'] ?? ($options['subject'] ?? ($booking->subject?->name ?? '-'));
                                        $image     = $options['image'] ?? '';
                                        $subjectGroup = $options['subject_group'] ?? '';
                                        
                                        $price = $booking->session_fee;
                                        // Payout calculation (approximate if not stored)
                                        $tutor_payout = $price - getCommission($price);
                                        
                                        $isCompleted = $booking->status == 'completed';
                                        
                                        // Transaction ID from Order if exists
                                        $transactionId = $booking->orderItem?->orders?->transaction_id ?? '-';
                                    @endphp
                                    <tr>
                                        <td data-label="{{ __('booking.id') }}"><span>{{ $booking->id }}</span></td>
                                        <td data-label="{{ __('booking.transaction_id') }}"><span>{{ $transactionId }}</span></td>
                                        <td data-label="{{ __('booking.subject' )}}">
                                            <div class="tb-varification_userinfo">
                                                <strong class="tb-adminhead__img">
                                                    @if (!empty($image) && Storage::disk(getStorageDisk())->exists($image))
                                                    <img src="{{ resizedImage($image,34,34) }}" alt="{{$image}}" />
                                                    @else 
                                                        <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png',34,34) }}" alt="{{ $image }}" />
                                                    @endif
                                                </strong>
                                                <span>
                                                    {{ $subject }}
                                                    <a href="javascript:void(0);" class="am-custom-tooltip">
                                                        <i class="icon-alert-circle am-custom-tooltip-icon"></i>
                                                        <span class="am-tooltip-text">
                                                            {{ \Carbon\Carbon::parse($booking->start_time)->format('F j, Y, g:i a') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i a') }}
                                                        </span>        
                                                    </a>
                                                    <small>{{ $subjectGroup }}</small>
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('booking.student_name' )}}">
                                            <div class="tb-varification_userinfo">
                                                <strong class="tb-adminhead__img">
                                                    @if (!empty($booking->student?->profile->image) && Storage::disk(getStorageDisk())->exists($booking->student?->profile->image))
                                                    <img src="{{ resizedImage($booking->student?->profile->image,34,34) }}" alt="{{$booking->student?->profile->image}}" />
                                                    @else
                                                        <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 34, 34) }}" alt="Student" />
                                                    @endif
                                                </strong>
                                                <span>{{ $booking->student?->profile->full_name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('booking.tutor_name' )}}">
                                            <div class="tb-varification_userinfo">
                                                <strong class="tb-adminhead__img">
                                                    @if (!empty($booking->tutor?->profile->image) && Storage::disk(getStorageDisk())->exists($booking->tutor?->profile->image))
                                                    <img src="{{ resizedImage($booking->tutor?->profile->image,34,34) }}" alt="{{$booking->tutor?->profile->image}}" />
                                                    @else 
                                                        <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png',34,34) }}" alt="Tutor" />
                                                    @endif
                                                </strong>
                                                <span>{{ $booking->tutor?->profile->full_name ?? 'Unassigned' }}</span>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('booking.amount') }}">
                                            <span>{!! formatAmount($price) !!}</span>
                                        </td>
                                        <td data-label="{{ __('booking.tutor_payout') }}">
                                            <span>{!! formatAmount($tutor_payout) !!}</span>
                                        </td>
                                        <td data-label="{{ __('booking.status' )}}">
                                            <div class="am-status-tag">
                                                <em class="tk-project-tag {{ $isCompleted ? 'tk-hourly-tag' : 'tk-fixed-tag' }}">{{ $booking->status }}</em>
                                            </div>
                                        </td>
                                        <td data-label="Assigned To">
                                            <div class="am-status-tag">
                                                @if($booking->tutor_id)
                                                    <div class="d-flex align-items-center">
                                                        <span class="me-2">{{ $booking->tutor->profile->full_name }}</span>
                                                        <button wire:click="openAssignModal('{{ $booking->id }}')" class="btn btn-outline-primary btn-sm">Change</button>
                                                    </div>
                                                @else
                                                    <button wire:click="openAssignModal('{{ $booking->id }}')" class="btn btn-primary btn-sm">Assign</button>
                                                @endif
                                            </div>
                                        </td>
                                        <td data-label="Credit Tutor">
                                            <div class="am-status-tag">
                                                <button wire:click="openCreditTutorModal('{{ $booking->id }}')" class="btn btn-success btn-sm">Credit</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                            {{ $bookings->links('pagination.custom') }}
                    @else
                        <x-no-record :image="asset('images/empty.png')" :title="__('general.no_record_title')" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Tutor Assignment Modal -->
@if($showAssignModal)
<div class="am-modal show" id="assign-tutor-modal">
    <div class="am-modal-dialog am-modal-dialog-centered">
        <div class="am-modal-wrapper">
            <div class="am-modal-header">
                <h5>{{ __('Assign Booking to Tutor') }}</h5>
                <a href="javascript:void(0);" wire:click="closeAssignModal" class="close">
                    <i class="icon-x"></i>
                </a>
            </div>
            <div class="am-modal-body">
                <div class="am-modal-inner-content">
                    <!-- Loading Indicator -->
                    @if($isLoading)
                    <div class="d-flex justify-content-center align-items-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    @else
                    
                    <!-- Booking Details -->
                    @if($bookingDetails)
                    <div class="booking-details mb-4 p-3 border rounded bg-light">
                        <h6 class="mb-3">Booking Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Student:</strong> {{ $bookingDetails->student->profile->full_name ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Subject:</strong> {{ $bookingDetails->subject->name ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Date:</strong> {{ \Carbon\Carbon::parse($bookingDetails->start_time)->format('F j, Y') }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Time:</strong> {{ \Carbon\Carbon::parse($bookingDetails->start_time)->format('g:i a') }} - {{ \Carbon\Carbon::parse($bookingDetails->end_time)->format('g:i a') }}
                            </div>
                        </div>
                    </div>
                    @endif
                    <!-- Filters -->
                    <div class="row">
                        <!-- State Filter -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Filter by State') }}</label>
                            <select wire:model.live="selectedState" class="form-control">
                                <option value="">{{ __('All States') }}</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- City Filter -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Filter by City') }}</label>
                            <select wire:model.live="selectedCity" class="form-control" @if(empty($cities)) disabled @endif>
                                <option value="">{{ __('All Cities') }}</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Search Tutor -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label">{{ __('Search Tutor') }}</label>
                            <input type="text" wire:model.live="searchTutor" class="form-control" placeholder="{{ __('Search by name') }}">
                        </div>
                    </div>
                    
                    <!-- Tutors List -->
                    <div class="row">
                        <div class="col-12">
                            <div class="am-tutor-list">
                                @if(count($tutors) > 0)
                                    <div class="list-group">
                                        @foreach($tutors as $tutor)
                                            <div class="list-group-item d-flex align-items-center justify-content-between p-3 @if($selectedTutor == $tutor->id) active @endif" 
                                                wire:click="$set('selectedTutor', '{{ $tutor->id }}')" 
                                                style="cursor: pointer;">
                                                <div class="d-flex align-items-center">
                                                    <div class="tb-adminhead__img me-3">
                                                        @if (!empty($tutor->profile->image))
                                                            <img src="{{ resizedImage($tutor->profile->image, 40, 40) }}" alt="{{ $tutor->profile->full_name }}" />
                                                        @else
                                                            <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 40, 40) }}" alt="{{ $tutor->profile->full_name }}" />
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $tutor->profile->full_name }}</h6>
                                                        <small>
                                                            @if($tutor->address)
                                                                {{ $tutor->address->city ?? '' }}, 
                                                                {{ $tutor->address->state->name ?? '' }}
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                                @if($selectedTutor == $tutor->id)
                                                    <i class="icon-check text-success"></i>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        {{ __('No tutors found matching your criteria.') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="am-modal-footer">
                <div class="am-modal-footer_item">
                    <button type="button" wire:click="closeAssignModal" class="btn btn-outline-primary" @if($isLoading) disabled @endif>{{ __('Cancel') }}</button>
                    <button type="button" wire:click="assignTutor" class="btn btn-primary" @if(!$selectedTutor || $isLoading) disabled @endif>
                        @if($isLoading)
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            {{ __('Processing...') }}
                        @else
                            {{ __('Assign Tutor') }}
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Credit Tutor Modal -->
@if($showCreditModal)
<div class="am-modal show" id="credit-tutor-modal">
    <div class="am-modal-dialog am-modal-dialog-centered">
        <div class="am-modal-wrapper">
            <div class="am-modal-header">
                <h5>{{ __('Credit Tutor for Booking') }}</h5>
                <a href="javascript:void(0);" wire:click="closeCreditTutorModal" class="close">
                    <i class="icon-x"></i>
                </a>
            </div>
            <div class="am-modal-body">
                <div class="am-modal-inner-content">
                    <!-- Loading Indicator -->
                    @if($isLoading)
                    <div class="d-flex justify-content-center align-items-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    @else
                    
                    <!-- Booking Details -->
                    @if($bookingDetails)
                    <div class="booking-details mb-4 p-3 border rounded bg-light">
                        <h6 class="mb-3">Booking Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Student:</strong> {{ $bookingDetails->student->profile->full_name ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Tutor:</strong> {{ $bookingDetails->tutor->profile->full_name ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Subject:</strong> {{ $bookingDetails->subject->name ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Date:</strong> {{ $bookingDetails->start_time ? \Carbon\Carbon::parse($bookingDetails->start_time)->format('F j, Y') : 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Time:</strong> 
                                {{ $bookingDetails->start_time ? \Carbon\Carbon::parse($bookingDetails->start_time)->format('g:i a') : 'N/A' }} - 
                                {{ $bookingDetails->end_time ? \Carbon\Carbon::parse($bookingDetails->end_time)->format('g:i a') : 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Total Amount:</strong> {!! formatAmount($bookingDetails->orderItem->price ?? 0) !!}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Credit Amount Form -->
                    <div class="credit-form">
                        <div class="form-group mb-3">
                            <label for="creditAmount" class="form-label">Amount to Credit Tutor</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ setting('_general.currency_symbol') }}</span>
                                <input type="number" id="creditAmount" wire:model="creditAmount" class="form-control" step="0.01" min="0.01" placeholder="Enter amount">
                            </div>
                            @error('creditAmount') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
            <div class="am-modal-footer">
                <div class="am-modal-footer_item">
                    <button type="button" wire:click="closeCreditTutorModal" class="btn btn-outline-primary" @if($isLoading) disabled @endif>{{ __('Cancel') }}</button>
                    <button type="button" wire:click="creditTutor" class="btn btn-primary" @if(!$creditAmount || $isLoading) disabled @endif>
                        @if($isLoading)
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span>Processing...</span>
                        @else
                        {{ __('Credit Tutor') }}
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
