@extends('layouts.master')
@section('title', __('meetfusion::meetfusion.join_google_meet'))
@section('content')
    <main class="tk-main-bg">
        <section class="tk-main-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="tk-meeting-section">
                            <div class="tk-meeting-info">
                                <h3>{{ $meeting['title'] }}</h3>
                                <ul class="tk-meeting-detail">
                                    <li>
                                        <span>{{ __('meetfusion::meetfusion.meeting_id') }}:</span>
                                        <span>{{ $meeting['id'] }}</span>
                                    </li>
                                    @if(!empty($meeting['password']))
                                        <li>
                                            <span>{{ __('meetfusion::meetfusion.meeting_password') }}:</span>
                                            <span>{{ $meeting['password'] }}</span>
                                        </li>
                                    @endif
                                    <li>
                                        <span>{{ __('meetfusion::meetfusion.meeting_start_time') }}:</span>
                                        <span>{{ $meeting['start_time'] }}</span>
                                    </li>
                                    <li>
                                        <span>{{ __('meetfusion::meetfusion.meeting_duration') }}:</span>
                                        <span>{{ $meeting['duration'] }} {{ __('common.minutes') }}</span>
                                    </li>
                                    <li>
                                        <span>{{ __('meetfusion::meetfusion.meeting_timezone') }}:</span>
                                        <span>{{ $meeting['timezone'] }}</span>
                                    </li>
                                </ul>
                                <div class="tk-meeting-btn">
                                    <a href="{{ $meeting['join_url'] }}" target="_blank" class="tk-btn-solid-lg">
                                        {{ __('meetfusion::meetfusion.join_meeting') }}
                                        <i class="icon-video-camera"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
