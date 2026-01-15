<div class="form-group">
    <label class="form-label">{{ __('settings.google_meet_authorize_button') }}</label>
    <div class="form-group-wrap">
        <a href="{{ route('google-meet.authorize') }}" class="am-btn am-btn-primary">
            @if(settings('google_meet_access_token'))
                {{ __('meetfusion::meetfusion.google_meet_reauthorize') }}
            @else
                {{ __('meetfusion::meetfusion.google_meet_authorize') }}
            @endif
        </a>
        <div class="form-text">{{ __('meetfusion::meetfusion.google_meet_auth_instructions') }}</div>
    </div>
</div>
