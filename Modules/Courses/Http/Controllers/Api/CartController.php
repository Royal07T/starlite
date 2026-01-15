<?php

namespace Modules\Courses\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Response;
use Modules\Courses\Services\CourseService;
use App\Http\Controllers\Controller;
use Modules\Courses\Models\Course;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Facades\Cart;
use App\Http\Resources\CartResource;

class CartController extends Controller
{
    use ApiResponser;

    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function store(Request $request)
    {
        $response = isDemoSite();
        if( $response ){
            return $this->error(message:  __('general.demosite_res_title'), code: Response::HTTP_FORBIDDEN);
        }

        if (auth()->check() && auth()->user()->role != 'student') {
            return $this->error(data: null,message: __('courses::courses.unauthorized_access'),code: Response::HTTP_FORBIDDEN);
        }

        $course = $this->courseService->getCourseBySlug($request->slug);

        if (!$course) {
            return $this->error(data: null,message: __('courses::courses.course_not_found'),code: Response::HTTP_NOT_FOUND);
        }
        
        if(auth()->check() && $course?->instructor_id == auth()->user()->id){
            return $this->error(data: null,message: __('courses::courses.unauthorized_access'),code: Response::HTTP_FORBIDDEN);
        }

        $price = $course?->pricing?->final_price ?? 0;
        $quantity = 1;
        $isSessionBased = false;

        // Session Pricing Logic
        if ($course->pricing?->pricing_type == 'session') {
            $minSessions = $course->pricing->min_sessions ?? 2;
            $quantity = (int) $request->input('quantity', $minSessions);
            
            if ($quantity < $minSessions) {
                 return $this->error(data: null, message: "Minimum sessions required: {$minSessions}", code: Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $price = $course->pricing->session_price;
            $isSessionBased = true;
        }

        $data = [
            'id'                => $course?->id ?? null,
            'title'             => $course?->title ?? null,
            'price'             => $price, // Unit price
            'category'          => $course?->category?->name ?? null,
            'sub_category'      => $course?->subCategory?->name ?? null,
            'slug'              => $course?->slug ?? null,
            'image'             => $course?->thumbnail?->path ?? null,
            'type'              => $isSessionBased ? 'session_pack' : 'course', // Helper for checkout
            'total_sessions'    => $isSessionBased ? $quantity : 0, // Store total sessions if session based
        ];

        $cartItem = Cart::add(
            cartableId      : $data['id'],
            cartableType    : Course::class,
            name            : $data['title'],
            qty             : $quantity, // For session packs, qty = sessions. For course, qty = 1.
            price           : $price,
            options         : $data
        );

        return $this->success(data: [
            'cartItem'  => new CartResource($cartItem),
            'total'     => formatAmount(Cart::total()),
            'subtotal'  => formatAmount(Cart::subtotal())
        ], message: __('courses::courses.added_to_cart'));

    }
}
