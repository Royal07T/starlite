<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Course Selector</h4>
                        <div>
                            @if(!empty($selectedSubjects))
                                <button wire:click="addSelectedSubjectsToCart" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="addSelectedSubjectsToCart">Add to Cart ({{ count($selectedSubjects) }})</span>
                                    <span wire:loading wire:target="addSelectedSubjectsToCart">Adding...</span>
                                </button>
                            @endif
                            
                            @if($currentCategoryId !== null)
                                <button wire:click="navigateBack" class="btn btn-outline-secondary" wire:loading.attr="disabled">
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body">
                        @if(session()->has('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        @if(session()->has('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        
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
                                    
                                    @foreach($navigationHistory as $category)
                                        <li class="breadcrumb-item">
                                            <a href="#" wire:click.prevent="navigateToCategory({{ $category['id'] }})">{{ $category['name'] }}</a>
                                        </li>
                                    @endforeach
                                    
                                    @if($currentCategory)
                                        <li class="breadcrumb-item active" aria-current="page">{{ $currentCategory->name }}</li>
                                    @endif
                                </ol>
                            </nav>
                            
                            <!-- Categories view -->
                            @if($currentView === 'categories')
                                <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                                    @forelse($currentSubcategories as $category)
                                        <div class="col">
                                            <div class="card h-100 category-card" wire:click="navigateToCategory({{ $category->id }})" style="cursor: pointer;">
                                                @if($category->image)
                                                    <img src="{{ asset('storage/' . $category->image) }}" class="card-img-top" alt="{{ $category->name }}" style="height: 160px; object-fit: cover;">
                                                @else
                                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                                                        <i class="fas fa-book fa-3x text-muted"></i>
                                                    </div>
                                                @endif
                                                <div class="card-body">
                                                    <h5 class="card-title">{{ $category->name }}</h5>
                                                    @if($category->description)
                                                        <p class="card-text">{{ Str::limit($category->description, 100) }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                No categories found. Please check back later.
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                            
                            <!-- Subjects view -->
                            @if($currentView === 'subjects')
                                <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                                    @forelse($currentSubjects as $subject)
                                        <div class="col">
                                            <div class="card h-100 subject-card {{ in_array($subject->id, $selectedSubjects) ? 'border-primary' : '' }}" 
                                                 wire:click="toggleSubjectSelection({{ $subject->id }})" 
                                                 style="cursor: pointer;">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               {{ in_array($subject->id, $selectedSubjects) ? 'checked' : '' }}
                                                               id="subject-{{ $subject->id }}">
                                                        <label class="form-check-label" for="subject-{{ $subject->id }}">
                                                            <h5 class="card-title">{{ $subject->title }}</h5>
                                                        </label>
                                                    </div>
                                                    @if($subject->description)
                                                        <p class="card-text mt-2">{{ Str::limit($subject->description, 100) }}</p>
                                                    @endif
                                                    <div class="mt-2">
                                                        <strong class="text-primary">
                                                            @php
                                                                $displayPrice = $subject->final_price ?? $subject->price ?? 0;
                                                            @endphp
                                                            {{ formatAmount($displayPrice) }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                No subjects found for this category. Please check back later.
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            @endif
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
