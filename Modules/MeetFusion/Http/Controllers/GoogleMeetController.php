<?php

namespace Modules\MeetFusion\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\MeetFusion\Facades\MeetFusion;

class GoogleMeetController extends Controller
{
    /**
     * Redirect to Google OAuth authorization page
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authorize()
    {
        $authUrl = MeetFusion::google_meet()->getAuthUrl();
        return redirect()->away($authUrl);
    }

    /**
     * Handle OAuth callback from Google
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        $code = $request->get('code');
        
        if (!$code) {
            return redirect()->route('admin.settings.api')->with('error', __('meetfusion::meetfusion.google_meet_auth_failed'));
        }
        
        $result = MeetFusion::google_meet()->handleCallback($code);
        
        if ($result) {
            return redirect()->route('admin.settings.api')->with('success', __('meetfusion::meetfusion.google_meet_auth_success'));
        } else {
            return redirect()->route('admin.settings.api')->with('error', __('meetfusion::meetfusion.google_meet_auth_failed'));
        }
    }
}
