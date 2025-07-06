// admin/js/admin.js

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Initialize color pickers
        $('.aiwinlab-color-field').wpColorPicker();
        
        // Handle API key visibility toggle
        $('.aiwinlab-api-key-toggle').on('click', function(e) {
            e.preventDefault();
            
            const $input = $(this).prev('input');
            const type = $input.attr('type');
            
            if (type === 'password') {
                $input.attr('type', 'text');
                $(this).html('<span class="dashicons dashicons-hidden"></span>');
            } else {
                $input.attr('type', 'password');
                $(this).html('<span class="dashicons dashicons-visibility"></span>');
            }
        });
        
        // Handle test API connection
        $('#aiwinlab-test-api').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $result = $('#aiwinlab-api-test-result');
            const apiKey = $('input[name="aiwinlab_gemini_api_key"]').val();
            
            if (!apiKey) {
                $result.html('<span class="error">Please enter an API key first.</span>');
                return;
            }
            
            $button.prop('disabled', true).text('Testing...');
            $result.html('<span class="testing">Testing connection...</span>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiwinlab_test_api',
                    nonce: $('#_wpnonce').val(),
                    api_key: apiKey
                },
                success: function(response) {
                    if (response.success) {
                        $result.html('<span class="success">Connection successful! API key is valid.</span>');
                    } else {
                        $result.html('<span class="error">Connection failed: ' + response.data + '</span>');
                    }
                },
                error: function() {
                    $result.html('<span class="error">Connection failed. Please try again.</span>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Test Connection');
                }
            });
        });
        
        // Analytics dashboard tabs
        $('.aiwinlab-tab-nav a').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this).data('tab');
            
            $('.aiwinlab-tab-nav a').removeClass('active');
            $(this).addClass('active');
            
            $('.aiwinlab-tab-content').removeClass('active');
            $('#' + target).addClass('active');
        });
    });
})(jQuery);