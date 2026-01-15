<?php

namespace Modules\MeetFusion\Factories;

use Modules\MeetFusion\Drivers\Zoom;
use Modules\MeetFusion\Drivers\GoogleMeet;

class MeetFusionFactory {
     
    /**
      * @return \Modules\MeetFusion\Drivers\Zoom
    */

    public function zoom(): Zoom{
        return new Zoom();
    }

    /**
     * @return \Modules\MeetFusion\Drivers\GoogleMeet
     */
    public function google_meet(): GoogleMeet{
        return new GoogleMeet();
    }

    /**
     * @return array $supportedConferences
     */
     public function supportedConferences() : array{
        return [
            'zoom' => [
                'keys' => [
                    'account_id' => __('meetfusion::meetfusion.join_meeting'),
                    'client_id' => '',
                    'client_secret' => '',
                ],
                'status' => 'off'
            ],
            'google_meet' => [
                'keys' => [
                    'client_id' => '',
                    'client_secret' => '',
                    'redirect_uri' => '',
                ],
                'status' => 'off'
            ],
        ];
    }
}
