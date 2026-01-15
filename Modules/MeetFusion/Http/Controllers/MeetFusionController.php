<?php

namespace Modules\MeetFusion\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\MeetFusion\Facades\MeetFusion;
use Illuminate\Support\Facades\Log;

class MeetFusionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('meetfusion::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('meetfusion::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('meetfusion::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('meetfusion::edit');
    }
    
    /**
     * Create a Google Meet meeting
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createGoogleMeeting(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'duration' => 'required|integer|min:1',
                'timezone' => 'required|string',
            ]);
            
            $result = MeetFusion::google_meet()->createMeeting(
                $validated['title'],
                $validated['description'] ?? '',
                $validated['start_time'],
                $validated['duration'],
                $validated['timezone']
            );
            
            if (isset($result['error'])) {
                if (isset($result['auth_url'])) {
                    return response()->json([
                        'success' => false,
                        'message' => __('meetfusion::meetfusion.google_meet_auth_required'),
                        'auth_url' => $result['auth_url']
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'meeting' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Google Meet create meeting error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display Google Meet meeting details
     *
     * @param string $meetingId
     * @return \Illuminate\View\View
     */
    public function showGoogleMeeting($meetingId)
    {
        try {
            $result = MeetFusion::google_meet()->getMeeting($meetingId);
            
            if (isset($result['error'])) {
                return redirect()->back()->with('error', $result['error']);
            }
            
            return view('meetfusion::google-meet-meeting', [
                'meeting' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Google Meet show meeting error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', __('meetfusion::meetfusion.failed_create_google_meet_link'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
