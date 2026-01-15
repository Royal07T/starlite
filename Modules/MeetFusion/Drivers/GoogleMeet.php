<?php

namespace Modules\MeetFusion\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_ConferenceData;
use Google_Service_Calendar_CreateConferenceRequest;
use Google_Service_Calendar_ConferenceSolutionKey;

class GoogleMeet
{
    /**
     * Google Meet API credentials
     */
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $accessToken;
    protected $client;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clientId = settings('google_meet_client_id');
        $this->clientSecret = settings('google_meet_client_secret');
        $this->redirectUri = url('google-meet/callback');
        $this->accessToken = settings('google_meet_access_token');
        
        $this->initClient();
    }
    
    /**
     * Initialize Google Client
     */
    protected function initClient()
    {
        $this->client = new Google_Client();
        $this->client->setClientId($this->clientId);
        $this->client->setClientSecret($this->clientSecret);
        $this->client->setRedirectUri($this->redirectUri);
        $this->client->setScopes([
            Google_Service_Calendar::CALENDAR,
            Google_Service_Calendar::CALENDAR_EVENTS
        ]);
        
        if ($this->accessToken) {
            $this->client->setAccessToken(json_decode($this->accessToken, true));
            
            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    settings(['google_meet_access_token' => json_encode($this->client->getAccessToken())]);
                }
            }
        }
    }
    
    /**
     * Get authorization URL
     * 
     * @return string
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }
    
    /**
     * Handle OAuth callback
     * 
     * @param string $code
     * @return bool
     */
    public function handleCallback(string $code): bool
    {
        try {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);
            $this->client->setAccessToken($accessToken);
            
            settings(['google_meet_access_token' => json_encode($accessToken)]);
            return true;
        } catch (\Exception $e) {
            Log::error('Google Meet OAuth error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a meeting
     *
     * @param array $data
     * @return array
     */
    public function createMeeting(array $data): array
    {
        try {
            if (!$this->client->getAccessToken() || $this->client->isAccessTokenExpired()) {
                return [
                    'error' => 'Authentication required',
                    'auth_url' => $this->getAuthUrl()
                ];
            }
            
            $service = new Google_Service_Calendar($this->client);
            
            // Create conference data request
            $conferenceRequest = new Google_Service_Calendar_CreateConferenceRequest();
            $solutionKey = new Google_Service_Calendar_ConferenceSolutionKey();
            $solutionKey->setType('hangoutsMeet');
            $conferenceRequest->setRequestId(Str::random(10));
            $conferenceRequest->setConferenceSolutionKey($solutionKey);
            
            $conferenceData = new Google_Service_Calendar_ConferenceData();
            $conferenceData->setCreateRequest($conferenceRequest);
            
            // Create event with Google Meet
            $event = new Google_Service_Calendar_Event([
                'summary' => $data['topic'] ?? 'New Meeting',
                'description' => $data['agenda'] ?? 'Meeting created via Starlite',
                'start' => [
                    'dateTime' => $data['start_time'] ?? now()->toIso8601String(),
                    'timeZone' => $data['timezone'] ?? config('app.timezone'),
                ],
                'end' => [
                    'dateTime' => $data['end_time'] ?? now()->addHour()->toIso8601String(),
                    'timeZone' => $data['timezone'] ?? config('app.timezone'),
                ],
                'conferenceData' => $conferenceData,
            ]);
            
            $event = $service->events->insert('primary', $event, [
                'conferenceDataVersion' => 1
            ]);
            
            // Extract meeting details
            $meetingId = $event->getId();
            $joinUrl = $event->getHangoutLink();
            
            return [
                'id' => $meetingId,
                'join_url' => $joinUrl,
                'start_url' => $joinUrl, // Same URL for Google Meet
                'password' => '', // Google Meet doesn't use passwords
                'created_at' => now(),
            ];
        } catch (\Exception $e) {
            Log::error('Google Meet create meeting error: ' . $e->getMessage());
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get meeting details
     *
     * @param string $meetingId
     * @return array
     */
    public function getMeeting(string $meetingId): array
    {
        try {
            if (!$this->client->getAccessToken() || $this->client->isAccessTokenExpired()) {
                return [
                    'error' => 'Authentication required',
                    'auth_url' => $this->getAuthUrl()
                ];
            }
            
            $service = new Google_Service_Calendar($this->client);
            $event = $service->events->get('primary', $meetingId);
            
            if (!$event) {
                return ['error' => 'Meeting not found'];
            }
            
            $joinUrl = $event->getHangoutLink();
            
            return [
                'id' => $meetingId,
                'join_url' => $joinUrl,
                'start_url' => $joinUrl,
                'password' => '',
                'created_at' => $event->getCreated(),
                'start_time' => $event->getStart()->getDateTime(),
                'end_time' => $event->getEnd()->getDateTime(),
                'topic' => $event->getSummary(),
                'agenda' => $event->getDescription(),
            ];
        } catch (\Exception $e) {
            Log::error('Google Meet get meeting error: ' . $e->getMessage());
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update meeting details
     *
     * @param string $meetingId
     * @param array $data
     * @return array
     */
    public function updateMeeting(string $meetingId, array $data): array
    {
        try {
            if (!$this->client->getAccessToken() || $this->client->isAccessTokenExpired()) {
                return [
                    'error' => 'Authentication required',
                    'auth_url' => $this->getAuthUrl()
                ];
            }
            
            $service = new Google_Service_Calendar($this->client);
            
            // Get existing event
            $event = $service->events->get('primary', $meetingId);
            
            if (!$event) {
                return ['error' => 'Meeting not found'];
            }
            
            // Update event details
            if (isset($data['topic'])) {
                $event->setSummary($data['topic']);
            }
            
            if (isset($data['agenda'])) {
                $event->setDescription($data['agenda']);
            }
            
            if (isset($data['start_time'])) {
                $start = $event->getStart();
                $start->setDateTime($data['start_time']);
                $event->setStart($start);
            }
            
            if (isset($data['end_time'])) {
                $end = $event->getEnd();
                $end->setDateTime($data['end_time']);
                $event->setEnd($end);
            }
            
            // Update the event
            $updatedEvent = $service->events->update('primary', $meetingId, $event);
            $joinUrl = $updatedEvent->getHangoutLink();
            
            return [
                'id' => $meetingId,
                'join_url' => $joinUrl,
                'start_url' => $joinUrl,
                'password' => '',
                'created_at' => $updatedEvent->getCreated(),
                'updated_at' => $updatedEvent->getUpdated(),
                'start_time' => $updatedEvent->getStart()->getDateTime(),
                'end_time' => $updatedEvent->getEnd()->getDateTime(),
                'topic' => $updatedEvent->getSummary(),
                'agenda' => $updatedEvent->getDescription(),
            ];
        } catch (\Exception $e) {
            Log::error('Google Meet update meeting error: ' . $e->getMessage());
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete a meeting
     *
     * @param string $meetingId
     * @return array
     */
    public function deleteMeeting(string $meetingId): array
    {
        try {
            if (!$this->client->getAccessToken() || $this->client->isAccessTokenExpired()) {
                return [
                    'error' => 'Authentication required',
                    'auth_url' => $this->getAuthUrl()
                ];
            }
            
            $service = new Google_Service_Calendar($this->client);
            $service->events->delete('primary', $meetingId);
            
            return [
                'success' => true,
                'message' => 'Meeting deleted successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Google Meet delete meeting error: ' . $e->getMessage());
            return [
                'error' => $e->getMessage()
            ];
        }
    }
}
