<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Course Selector</h4>
                        <div>
                            <!--[if BLOCK]><![endif]--><?php if(!empty($selectedSubjects)): ?>
                                <button wire:click="addSelectedSubjectsToCart" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="addSelectedSubjectsToCart">Add to Cart (<?php echo e(count($selectedSubjects)); ?>)</span>
                                    <span wire:loading wire:target="addSelectedSubjectsToCart">Adding...</span>
                                </button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            
                            <!--[if BLOCK]><![endif]--><?php if($currentCategoryId !== null): ?>
                                <button wire:click="navigateBack" class="btn btn-outline-secondary" wire:loading.attr="disabled">
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!--[if BLOCK]><![endif]--><?php if(session()->has('success')): ?>
                            <div class="alert alert-success">
                                <?php echo e(session('success')); ?>

                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        
                        <?php if(session()->has('error')): ?>
                            <div class="alert alert-danger">
                                <?php echo e(session('error')); ?>

                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        
                        <div wire:loading wire:target="loadMainCategories, navigateToCategory, navigateBack" class="text-center my-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading...</p>
                        </div>
                        
                        <div wire:loading.remove wire:target="loadMainCategories, navigateToCategory, navigateBack">
                            <!-- Navigation breadcrumb -->
                            <nav aria-label="breadcrumb" class="mb-4">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="#" wire:click.prevent="loadMainCategories">Main Categories</a>
                                    </li>
                                    
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $navigationHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="breadcrumb-item">
                                            <a href="#" wire:click.prevent="navigateToCategory(<?php echo e($category['id']); ?>)"><?php echo e($category['name']); ?></a>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    
                                    <!--[if BLOCK]><![endif]--><?php if($currentCategory): ?>
                                        <li class="breadcrumb-item active" aria-current="page"><?php echo e($currentCategory->name); ?></li>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </ol>
                            </nav>
                            
                            <!-- Categories view -->
                            <!--[if BLOCK]><![endif]--><?php if($currentView === 'categories'): ?>
                                <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $currentSubcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="col">
                                            <div class="card h-100 category-card" wire:click="navigateToCategory(<?php echo e($category->id); ?>)" style="cursor: pointer;">
                                                <!--[if BLOCK]><![endif]--><?php if($category->image): ?>
                                                    <img src="<?php echo e(asset('storage/' . $category->image)); ?>" class="card-img-top" alt="<?php echo e($category->name); ?>" style="height: 160px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                                                        <i class="fas fa-book fa-3x text-muted"></i>
                                                    </div>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                <div class="card-body">
                                                    <h5 class="card-title"><?php echo e($category->name); ?></h5>
                                                    <!--[if BLOCK]><![endif]--><?php if($category->description): ?>
                                                        <p class="card-text"><?php echo e(Str::limit($category->description, 100)); ?></p>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                No categories found. Please check back later.
                                            </div>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            
                            <!-- Subjects view -->
                            <!--[if BLOCK]><![endif]--><?php if($currentView === 'subjects'): ?>
                                <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $currentSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="col">
                                            <div class="card h-100 subject-card <?php echo e(in_array($subject->id, $selectedSubjects) ? 'border-primary' : ''); ?>" 
                                                 wire:click="toggleSubjectSelection(<?php echo e($subject->id); ?>)" 
                                                 style="cursor: pointer;">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               <?php echo e(in_array($subject->id, $selectedSubjects) ? 'checked' : ''); ?>

                                                               id="subject-<?php echo e($subject->id); ?>">
                                                        <label class="form-check-label" for="subject-<?php echo e($subject->id); ?>">
                                                            <h5 class="card-title"><?php echo e($subject->title); ?></h5>
                                                        </label>
                                                    </div>
                                                    <!--[if BLOCK]><![endif]--><?php if($subject->description): ?>
                                                        <p class="card-text mt-2"><?php echo e(Str::limit($subject->description, 100)); ?></p>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    <div class="mt-2">
                                                        <strong class="text-primary">
                                                            <?php
                                                                $displayPrice = $subject->final_price ?? $subject->price ?? 0;
                                                            ?>
                                                            <?php echo e(formatAmount($displayPrice)); ?>

                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                No subjects found for this category. Please check back later.
                                            </div>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .category-card:hover, .subject-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .subject-card.border-primary {
            background-color: rgba(13, 110, 253, 0.05);
        }
    </style>
</div>
<?php /**PATH C:\wamp64\www\starlite\resources\views/livewire/pages/student/course-selector.blade.php ENDPATH**/ ?>