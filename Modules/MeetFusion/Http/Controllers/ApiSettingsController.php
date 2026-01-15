<?php

namespace Modules\MeetFusion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Common\AccountSetting\GoogleMeetSettingStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiSettingsController extends Controller
{
    /**
     * Store Google Meet settings
     *
     * @param GoogleMeetSettingStoreRequest $request
     * @return RedirectResponse
     */
    public function storeGoogleMeetSettings(GoogleMeetSettingStoreRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            // Save Google Meet settings
            settings([
                'google_meet_client_id' => $request->google_meet_client_id,
                'google_meet_client_secret' => $request->google_meet_client_secret,
            ]);
            
            // Update active conference if provided
            if ($request->has('active_conference')) {
                settings(['active_conference' => $request->active_conference]);
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', __('common.settings_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Google Meet settings update error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', __('common.something_went_wrong'));
        }
    }
}
