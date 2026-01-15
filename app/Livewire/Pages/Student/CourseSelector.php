<?php

namespace App\Livewire\Pages\Student;

use App\Services\CartService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CourseSelector extends Component
{
    public $currentCategoryId = null;
    public $navigationHistory = [];
    public $currentView = 'categories'; // 'categories' or 'subjects'
    public $selectedSubjects = [];
    public $currentCategory = null;
    public $currentSubcategories = [];
    public $currentSubjects = [];
    public $loadingState = false;
    
    public function mount()
    {
        $this->loadMainCategories();
    }
    
    public function loadMainCategories()
    {
        $this->loadingState = true;
        $this->currentCategoryId = null;
        $this->currentCategory = null;
        $this->navigationHistory = [];
        $this->currentView = 'categories';
        $this->currentSubcategories = DB::table('courses_categories')
            ->select('id', 'name', 'description', 'image', 'parent_id', 'status')
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->get();
        $this->loadingState = false;
    }
    
    public function navigateToCategory($categoryId)
    {
        $this->loadingState = true;
        
        // Add current category to navigation history if we're not at the main level
        if ($this->currentCategoryId !== null) {
            $this->navigationHistory[] = [
                'id' => $this->currentCategoryId,
                'name' => $this->currentCategory->name
            ];
        }
        
        $this->currentCategoryId = $categoryId;
        $this->currentCategory = DB::table('courses_categories')->find($categoryId);
        
        // Check if this category has subcategories
        $subcategories = DB::table('courses_categories')
            ->select('id', 'name', 'description', 'image', 'parent_id', 'status')
            ->where('parent_id', $categoryId)
            ->where('status', 'active')
            ->get();
            
        if ($subcategories->count() > 0) {
            $this->currentSubcategories = $subcategories;
            $this->currentView = 'categories';
        } else {
            // If no subcategories, show subjects for this category
            $this->loadSubjectsForCategory($categoryId);
            $this->currentView = 'subjects';
        }
        
        $this->loadingState = false;
    }
    
    public function loadSubjectsForCategory($categoryId)
    {
        // Get the category
        $category = DB::table('courses_categories')->find($categoryId);
        
        if (!$category) {
            $this->currentSubjects = collect();
            return;
        }
        
        // Load subjects from courses_courses table with matching sub_category_id and join with pricing table
        // Using status = 4 which corresponds to 'active' in Course::STATUSES
        $this->currentSubjects = DB::table('courses_courses')
            ->leftJoin('courses_pricings', 'courses_courses.id', '=', 'courses_pricings.course_id')
            ->select('courses_courses.*', 'courses_pricings.price', 'courses_pricings.discount', 'courses_pricings.final_price')
            ->where('courses_courses.sub_category_id', $categoryId)
            ->where('courses_courses.status', 4) // 4 = active status in Course model
            ->get();
    }
    
    public function navigateBack()
    {
        $this->loadingState = true;
        
        if (empty($this->navigationHistory)) {
            // If history is empty, go back to main categories
            $this->loadMainCategories();
        } else {
            // Pop the last item from history
            $lastCategory = array_pop($this->navigationHistory);
            
            if ($lastCategory) {
                $this->currentCategoryId = $lastCategory['id'];
                $this->currentCategory = DB::table('courses_categories')->find($lastCategory['id']);
                
                // Check if this category has subcategories
                $subcategories = DB::table('courses_categories')
                    ->select('id', 'name', 'description', 'image', 'parent_id', 'status')
                    ->where('parent_id', $lastCategory['id'])
                    ->where('status', 'active')
                    ->get();
                    
                if ($subcategories->count() > 0) {
                    $this->currentSubcategories = $subcategories;
                    $this->currentView = 'categories';
                } else {
                    $this->loadSubjectsForCategory($lastCategory['id']);
                    $this->currentView = 'subjects';
                }
            } else {
                $this->loadMainCategories();
            }
        }
        
        $this->loadingState = false;
    }
    
    public function toggleSubjectSelection($subjectId)
    {
        if (in_array($subjectId, $this->selectedSubjects)) {
            // Remove subject if already selected
            $this->selectedSubjects = array_diff($this->selectedSubjects, [$subjectId]);
        } else {
            // Add subject to selection
            $this->selectedSubjects[] = $subjectId;
        }
    }
    
    public function addSelectedSubjectsToCart()
    {
        $this->loadingState = true;
        
        if (empty($this->selectedSubjects)) {
            session()->flash('error', 'Please select at least one subject.');
            $this->loadingState = false;
            return;
        }
        
        $cartService = new CartService();
        $addedCount = 0;
        
        foreach ($this->selectedSubjects as $subjectId) {
            // Get course with pricing information
            $subject = DB::table('courses_courses')
                ->leftJoin('courses_pricings', 'courses_courses.id', '=', 'courses_pricings.course_id')
                ->select('courses_courses.*', 'courses_pricings.price', 'courses_pricings.discount', 'courses_pricings.final_price')
                ->where('courses_courses.id', $subjectId)
                ->first();
            
            if ($subject) {
                // Determine the correct price to use
                $coursePrice = $subject->final_price ?? $subject->price ?? 0;
                
                // Add to cart using the CartService
                // Parameters: cartableId, cartableType, name, qty, price, options
                $cartService->add(
                    $subject->id,
                    'App\Models\Course',
                    $subject->title,
                    1,
                    $coursePrice, // Use final_price or price from pricing table
                    [
                        'category_id' => $this->currentCategoryId,
                        'subject_id' => $subject->id,
                        'price' => $coursePrice
                    ]
                );
                
                $addedCount++;
            }
        }
        
        if ($addedCount > 0) {
            $this->selectedSubjects = []; // Clear selection
            session()->flash('success', $addedCount . ' subject(s) added to cart.');
            
            // Get updated cart data for the event
            $cartData = \App\Facades\Cart::content();
            $total = formatAmount(\App\Facades\Cart::total(), true);
            $subTotal = formatAmount(\App\Facades\Cart::subtotal(), true);
            $discount = formatAmount(\App\Facades\Cart::discount(), true);
            
            // Dispatch event with complete cart data
            $this->dispatch('cart-updated', [
                'cart_data' => $cartData,
                'total' => $total,
                'subTotal' => $subTotal,
                'discount' => $discount,
                'toggle_cart' => 'open'
            ]);
            
            // Redirect to checkout page
            return redirect()->route('checkout');
        } else {
            session()->flash('error', 'Failed to add subjects to cart.');
        }
        
        $this->loadingState = false;
    }
    
    public function render()
    {
        return view('livewire.pages.student.course-selector')
            ->layout('layouts.app');
    }
}
