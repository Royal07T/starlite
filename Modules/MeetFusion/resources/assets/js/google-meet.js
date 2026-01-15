/**
 * Google Meet integration for Starlite
 */
(function($) {
    'use strict';

    // Google Meet settings handler
    const GoogleMeetHandler = {
        /**
         * Initialize Google Meet settings
         */
        init: function() {
            this.bindEvents();
            this.toggleGoogleMeetFields();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            $(document).on('change', 'select[name="active_conference"]', this.handleConferenceChange.bind(this));
        },

        /**
         * Handle conference service change
         */
        handleConferenceChange: function(e) {
            this.toggleGoogleMeetFields();
        },

        /**
         * Toggle Google Meet fields based on selected conference service
         */
        toggleGoogleMeetFields: function() {
            const selectedService = $('select[name="active_conference"]').val();
            
            if (selectedService === 'google_meet') {
                $('.google-meet-fields').show();
                $('.zoom-fields').hide();
            } else if (selectedService === 'zoom') {
                $('.google-meet-fields').hide();
                $('.zoom-fields').show();
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        GoogleMeetHandler.init();
    });

})(jQuery);
