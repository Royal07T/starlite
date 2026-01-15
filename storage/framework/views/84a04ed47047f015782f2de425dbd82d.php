<main class="tb-main am-dispute-system am-booking-system">
    <div class ="row">
        <div class="col-lg-12 col-md-12">
            <div class="tb-dhb-mainheading">
                <h4> <?php echo e(__('general.all_booking') .' ('. $bookings->total() .')'); ?></h4>
                <div class="tb-sortby">
                    <form class="tb-themeform tb-displistform">
                        <fieldset>
                            <div class="tb-themeform__wrap">
                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select">
                                        <select data-componentid="window.Livewire.find('<?php echo e($_instance->getId()); ?>')" class="am-select2 form-control" data-searchable="false" data-live='true' id="status" data-wiremodel="status" >
                                            <option value="" <?php echo e($status == '' ? 'selected' : ''); ?> ><?php echo e(__('booking.all_bookings')); ?></option>
                                            <option value="pending" <?php echo e($status == 'pending' ? 'selected' : ''); ?> ><?php echo e(__('booking.pending')); ?></option>
                                            <option value="complete" <?php echo e($status == 'complete' ? 'selected' : ''); ?> ><?php echo e(__('booking.complete')); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select">
                                        <select data-componentid="window.Livewire.find('<?php echo e($_instance->getId()); ?>')" class="am-select2" data-live='true' data-searchable="true" id="subject" data-wiremodel="selectedSubject">
                                            <option value=""><?php echo e(__('booking.select_subject')); ?></option>
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($subject->name); ?>" <?php echo e($subject->name == $selectedSubject ? 'selected' : ''); ?>><?php echo e($subject->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        </select>
                                    </div>
                                </div>
                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select">
                                        <select data-componentid="window.Livewire.find('<?php echo e($_instance->getId()); ?>')" class="am-select2" data-live='true' data-searchable="true" id="subject_group" data-wiremodel="selectedSubGroup">
                                            <option value=""><?php echo e(__('booking.select_subject_group')); ?></option>
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $subjectGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subjectGroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($subjectGroup->name); ?>" <?php echo e($subjectGroup->name == $selectedSubGroup ? 'selected' : ''); ?>><?php echo e($subjectGroup->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        </select>
                                    </div>
                                </div>
                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select">
                                        <select data-componentid="window.Livewire.find('<?php echo e($_instance->getId()); ?>')" class="am-select2 form-control" data-searchable="false" data-live='true' id="sort_by" data-wiremodel="sortby" >
                                            <option value="asc" <?php echo e($sortby == 'asc' ? 'selected' : ''); ?> ><?php echo e(__('general.asc')); ?></option>
                                            <option value="desc" <?php echo e($sortby == 'desc' ? 'selected' : ''); ?> ><?php echo e(__('general.desc')); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group tb-inputicon tb-inputheight">
                                    <i class="icon-search"></i>
                                    <input type="text" class="form-control" wire:model.live.debounce.500ms="search"  autocomplete="off" placeholder="<?php echo e(__('general.search')); ?>">
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
            <div class="am-disputelist_wrap">
                <div class="am-disputelist am-custom-scrollbar-y">
                    <!--[if BLOCK]><![endif]--><?php if( !$bookings->isEmpty() ): ?>
                        <table class="tb-table <?php if(setting('_general.table_responsive') == 'yes'): ?> tb-table-responsive <?php endif; ?>">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('booking.id')); ?></th>
                                    <th><?php echo e(__('booking.transaction_id')); ?></th>
                                    <th><?php echo e(__('booking.subject')); ?></th>
                                    <th><?php echo e(__('booking.student_name')); ?></th>
                                    <th><?php echo e(__('booking.tutor_name')); ?></th>
                                    <th><?php echo e(__('booking.amount')); ?></th>
                                    <th><?php echo e(__('booking.tutor_payout')); ?></th>
                                    <th><?php echo e(__('booking.status')); ?></th>
                                    <th>Assigned To</th>
                                    <th>Credit Tutor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
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
                                    ?>
                                    <tr>
                                        <td data-label="<?php echo e(__('booking.id')); ?>"><span><?php echo e($booking->id); ?></span></td>
                                        <td data-label="<?php echo e(__('booking.transaction_id')); ?>"><span><?php echo e($transactionId); ?></span></td>
                                        <td data-label="<?php echo e(__('booking.subject' )); ?>">
                                            <div class="tb-varification_userinfo">
                                                <strong class="tb-adminhead__img">
                                                    <!--[if BLOCK]><![endif]--><?php if(!empty($image) && Storage::disk(getStorageDisk())->exists($image)): ?>
                                                    <img src="<?php echo e(resizedImage($image,34,34)); ?>" alt="<?php echo e($image); ?>" />
                                                    <?php else: ?> 
                                                        <img src="<?php echo e(setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png',34,34)); ?>" alt="<?php echo e($image); ?>" />
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </strong>
                                                <span>
                                                    <?php echo e($subject); ?>

                                                    <a href="javascript:void(0);" class="am-custom-tooltip">
                                                        <i class="icon-alert-circle am-custom-tooltip-icon"></i>
                                                        <span class="am-tooltip-text">
                                                            <?php echo e(\Carbon\Carbon::parse($booking->start_time)->format('F j, Y, g:i a')); ?> - <?php echo e(\Carbon\Carbon::parse($booking->end_time)->format('g:i a')); ?>

                                                        </span>        
                                                    </a>
                                                    <small><?php echo e($subjectGroup); ?></small>
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="<?php echo e(__('booking.student_name' )); ?>">
                                            <div class="tb-varification_userinfo">
                                                <strong class="tb-adminhead__img">
                                                    <!--[if BLOCK]><![endif]--><?php if(!empty($booking->student?->profile->image) && Storage::disk(getStorageDisk())->exists($booking->student?->profile->image)): ?>
                                                    <img src="<?php echo e(resizedImage($booking->student?->profile->image,34,34)); ?>" alt="<?php echo e($booking->student?->profile->image); ?>" />
                                                    <?php else: ?>
                                                        <img src="<?php echo e(setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 34, 34)); ?>" alt="Student" />
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </strong>
                                                <span><?php echo e($booking->student?->profile->full_name ?? 'N/A'); ?></span>
                                            </div>
                                        </td>
                                        <td data-label="<?php echo e(__('booking.tutor_name' )); ?>">
                                            <div class="tb-varification_userinfo">
                                                <strong class="tb-adminhead__img">
                                                    <!--[if BLOCK]><![endif]--><?php if(!empty($booking->tutor?->profile->image) && Storage::disk(getStorageDisk())->exists($booking->tutor?->profile->image)): ?>
                                                    <img src="<?php echo e(resizedImage($booking->tutor?->profile->image,34,34)); ?>" alt="<?php echo e($booking->tutor?->profile->image); ?>" />
                                                    <?php else: ?> 
                                                        <img src="<?php echo e(setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png',34,34)); ?>" alt="Tutor" />
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </strong>
                                                <span><?php echo e($booking->tutor?->profile->full_name ?? 'Unassigned'); ?></span>
                                            </div>
                                        </td>
                                        <td data-label="<?php echo e(__('booking.amount')); ?>">
                                            <span><?php echo formatAmount($price); ?></span>
                                        </td>
                                        <td data-label="<?php echo e(__('booking.tutor_payout')); ?>">
                                            <span><?php echo formatAmount($tutor_payout); ?></span>
                                        </td>
                                        <td data-label="<?php echo e(__('booking.status' )); ?>">
                                            <div class="am-status-tag">
                                                <em class="tk-project-tag <?php echo e($isCompleted ? 'tk-hourly-tag' : 'tk-fixed-tag'); ?>"><?php echo e($booking->status); ?></em>
                                            </div>
                                        </td>
                                        <td data-label="Assigned To">
                                            <div class="am-status-tag">
                                                <!--[if BLOCK]><![endif]--><?php if($booking->tutor_id): ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="me-2"><?php echo e($booking->tutor->profile->full_name); ?></span>
                                                        <button wire:click="openAssignModal('<?php echo e($booking->id); ?>')" class="btn btn-outline-primary btn-sm">Change</button>
                                                    </div>
                                                <?php else: ?>
                                                    <button wire:click="openAssignModal('<?php echo e($booking->id); ?>')" class="btn btn-primary btn-sm">Assign</button>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                        </td>
                                        <td data-label="Credit Tutor">
                                            <div class="am-status-tag">
                                                <button wire:click="openCreditTutorModal('<?php echo e($booking->id); ?>')" class="btn btn-success btn-sm">Credit</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </tbody>
                        </table>
                            <?php echo e($bookings->links('pagination.custom')); ?>

                    <?php else: ?>
                        <?php if (isset($component)) { $__componentOriginal86cd4a276c2978c462f28bbb510e89a0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal86cd4a276c2978c462f28bbb510e89a0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.no-record','data' => ['image' => asset('images/empty.png'),'title' => __('general.no_record_title')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('no-record'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['image' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(asset('images/empty.png')),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('general.no_record_title'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal86cd4a276c2978c462f28bbb510e89a0)): ?>
<?php $attributes = $__attributesOriginal86cd4a276c2978c462f28bbb510e89a0; ?>
<?php unset($__attributesOriginal86cd4a276c2978c462f28bbb510e89a0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal86cd4a276c2978c462f28bbb510e89a0)): ?>
<?php $component = $__componentOriginal86cd4a276c2978c462f28bbb510e89a0; ?>
<?php unset($__componentOriginal86cd4a276c2978c462f28bbb510e89a0); ?>
<?php endif; ?>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Tutor Assignment Modal -->
<!--[if BLOCK]><![endif]--><?php if($showAssignModal): ?>
<div class="am-modal show" id="assign-tutor-modal">
    <div class="am-modal-dialog am-modal-dialog-centered">
        <div class="am-modal-wrapper">
            <div class="am-modal-header">
                <h5><?php echo e(__('Assign Booking to Tutor')); ?></h5>
                <a href="javascript:void(0);" wire:click="closeAssignModal" class="close">
                    <i class="icon-x"></i>
                </a>
            </div>
            <div class="am-modal-body">
                <div class="am-modal-inner-content">
                    <!-- Loading Indicator -->
                    <!--[if BLOCK]><![endif]--><?php if($isLoading): ?>
                    <div class="d-flex justify-content-center align-items-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <?php else: ?>
                    
                    <!-- Booking Details -->
                    <!--[if BLOCK]><![endif]--><?php if($bookingDetails): ?>
                    <div class="booking-details mb-4 p-3 border rounded bg-light">
                        <h6 class="mb-3">Booking Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Student:</strong> <?php echo e($bookingDetails->student->profile->full_name ?? 'N/A'); ?>

                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Subject:</strong> <?php echo e($bookingDetails->subject->name ?? 'N/A'); ?>

                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Date:</strong> <?php echo e(\Carbon\Carbon::parse($bookingDetails->start_time)->format('F j, Y')); ?>

                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Time:</strong> <?php echo e(\Carbon\Carbon::parse($bookingDetails->start_time)->format('g:i a')); ?> - <?php echo e(\Carbon\Carbon::parse($bookingDetails->end_time)->format('g:i a')); ?>

                            </div>
                        </div>
                    </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <!-- Filters -->
                    <div class="row">
                        <!-- State Filter -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo e(__('Filter by State')); ?></label>
                            <select wire:model.live="selectedState" class="form-control">
                                <option value=""><?php echo e(__('All States')); ?></option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($state->id); ?>"><?php echo e($state->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                        </div>
                        
                        <!-- City Filter -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo e(__('Filter by City')); ?></label>
                            <select wire:model.live="selectedCity" class="form-control" <?php if(empty($cities)): ?> disabled <?php endif; ?>>
                                <option value=""><?php echo e(__('All Cities')); ?></option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($city); ?>"><?php echo e($city); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                        </div>
                    </div>
                    
                    <!-- Search Tutor -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label"><?php echo e(__('Search Tutor')); ?></label>
                            <input type="text" wire:model.live="searchTutor" class="form-control" placeholder="<?php echo e(__('Search by name')); ?>">
                        </div>
                    </div>
                    
                    <!-- Tutors List -->
                    <div class="row">
                        <div class="col-12">
                            <div class="am-tutor-list">
                                <!--[if BLOCK]><![endif]--><?php if(count($tutors) > 0): ?>
                                    <div class="list-group">
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $tutors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tutor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="list-group-item d-flex align-items-center justify-content-between p-3 <?php if($selectedTutor == $tutor->id): ?> active <?php endif; ?>" 
                                                wire:click="$set('selectedTutor', '<?php echo e($tutor->id); ?>')" 
                                                style="cursor: pointer;">
                                                <div class="d-flex align-items-center">
                                                    <div class="tb-adminhead__img me-3">
                                                        <!--[if BLOCK]><![endif]--><?php if(!empty($tutor->profile->image)): ?>
                                                            <img src="<?php echo e(resizedImage($tutor->profile->image, 40, 40)); ?>" alt="<?php echo e($tutor->profile->full_name); ?>" />
                                                        <?php else: ?>
                                                            <img src="<?php echo e(setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 40, 40)); ?>" alt="<?php echo e($tutor->profile->full_name); ?>" />
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo e($tutor->profile->full_name); ?></h6>
                                                        <small>
                                                            <!--[if BLOCK]><![endif]--><?php if($tutor->address): ?>
                                                                <?php echo e($tutor->address->city ?? ''); ?>, 
                                                                <?php echo e($tutor->address->state->name ?? ''); ?>

                                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                        </small>
                                                    </div>
                                                </div>
                                                <!--[if BLOCK]><![endif]--><?php if($selectedTutor == $tutor->id): ?>
                                                    <i class="icon-check text-success"></i>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <?php echo e(__('No tutors found matching your criteria.')); ?>

                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
            <div class="am-modal-footer">
                <div class="am-modal-footer_item">
                    <button type="button" wire:click="closeAssignModal" class="btn btn-outline-primary" <?php if($isLoading): ?> disabled <?php endif; ?>><?php echo e(__('Cancel')); ?></button>
                    <button type="button" wire:click="assignTutor" class="btn btn-primary" <?php if(!$selectedTutor || $isLoading): ?> disabled <?php endif; ?>>
                        <!--[if BLOCK]><![endif]--><?php if($isLoading): ?>
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            <?php echo e(__('Processing...')); ?>

                        <?php else: ?>
                            <?php echo e(__('Assign Tutor')); ?>

                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->

<!-- Credit Tutor Modal -->
<!--[if BLOCK]><![endif]--><?php if($showCreditModal): ?>
<div class="am-modal show" id="credit-tutor-modal">
    <div class="am-modal-dialog am-modal-dialog-centered">
        <div class="am-modal-wrapper">
            <div class="am-modal-header">
                <h5><?php echo e(__('Credit Tutor for Booking')); ?></h5>
                <a href="javascript:void(0);" wire:click="closeCreditTutorModal" class="close">
                    <i class="icon-x"></i>
                </a>
            </div>
            <div class="am-modal-body">
                <div class="am-modal-inner-content">
                    <!-- Loading Indicator -->
                    <!--[if BLOCK]><![endif]--><?php if($isLoading): ?>
                    <div class="d-flex justify-content-center align-items-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <?php else: ?>
                    
                    <!-- Booking Details -->
                    <!--[if BLOCK]><![endif]--><?php if($bookingDetails): ?>
                    <div class="booking-details mb-4 p-3 border rounded bg-light">
                        <h6 class="mb-3">Booking Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Student:</strong> <?php echo e($bookingDetails->student->profile->full_name ?? 'N/A'); ?>

                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Tutor:</strong> <?php echo e($bookingDetails->tutor->profile->full_name ?? 'N/A'); ?>

                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Subject:</strong> <?php echo e($bookingDetails->subject->name ?? 'N/A'); ?>

                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Date:</strong> <?php echo e($bookingDetails->start_time ? \Carbon\Carbon::parse($bookingDetails->start_time)->format('F j, Y') : 'N/A'); ?>

                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Time:</strong> 
                                <?php echo e($bookingDetails->start_time ? \Carbon\Carbon::parse($bookingDetails->start_time)->format('g:i a') : 'N/A'); ?> - 
                                <?php echo e($bookingDetails->end_time ? \Carbon\Carbon::parse($bookingDetails->end_time)->format('g:i a') : 'N/A'); ?>

                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Total Amount:</strong> <?php echo formatAmount($bookingDetails->orderItem->price ?? 0); ?>

                            </div>
                        </div>
                    </div>
                    
                    <!-- Credit Amount Form -->
                    <div class="credit-form">
                        <div class="form-group mb-3">
                            <label for="creditAmount" class="form-label">Amount to Credit Tutor</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo e(setting('_general.currency_symbol')); ?></span>
                                <input type="number" id="creditAmount" wire:model="creditAmount" class="form-control" step="0.01" min="0.01" placeholder="Enter amount">
                            </div>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['creditAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
            <div class="am-modal-footer">
                <div class="am-modal-footer_item">
                    <button type="button" wire:click="closeCreditTutorModal" class="btn btn-outline-primary" <?php if($isLoading): ?> disabled <?php endif; ?>><?php echo e(__('Cancel')); ?></button>
                    <button type="button" wire:click="creditTutor" class="btn btn-primary" <?php if(!$creditAmount || $isLoading): ?> disabled <?php endif; ?>>
                        <!--[if BLOCK]><![endif]--><?php if($isLoading): ?>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span>Processing...</span>
                        <?php else: ?>
                        <?php echo e(__('Credit Tutor')); ?>

                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\wamp64\www\starlite\resources\views/livewire/pages/admin/bookings/bookings.blade.php ENDPATH**/ ?>