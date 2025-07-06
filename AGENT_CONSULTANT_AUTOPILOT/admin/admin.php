<?php
// admin/admin.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin functionality for AI WinLab
 */
class AI_WinLab_Admin {
    /**
     * Initialize admin functionality
     */
    public static function init() {
        // Add custom columns to report list
        add_filter('manage_aiwinlab_report_posts_columns', array(__CLASS__, 'add_report_columns'));
        add_action('manage_aiwinlab_report_posts_custom_column', array(__CLASS__, 'render_report_columns'), 10, 2);
        
        // Add custom columns to consultation list
        add_filter('manage_aiwinlab_consultation_posts_columns', array(__CLASS__, 'add_consultation_columns'));
        add_action('manage_aiwinlab_consultation_posts_custom_column', array(__CLASS__, 'render_consultation_columns'), 10, 2);
        
        // Add meta boxes
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        
        // Save meta box data
        add_action('save_post_aiwinlab_report', array(__CLASS__, 'save_report_meta'));
        add_action('save_post_aiwinlab_consultation', array(__CLASS__, 'save_consultation_meta'));
        
        // Register AJAX handlers
        add_action('wp_ajax_aiwinlab_test_api', array(__CLASS__, 'test_api_connection'));
    }
    
    /**
     * Add custom columns to report list
     */
    public static function add_report_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            // Insert custom columns before 'date' column
            if ($key === 'date') {
                $new_columns['business_name'] = __('Business', 'aiwinlab');
                $new_columns['industry'] = __('Industry', 'aiwinlab');
                $new_columns['report_status'] = __('Status', 'aiwinlab');
            }
            
            $new_columns[$key] = $value;
        }
        
        return $new_columns;
    }
    
    /**
     * Render report columns content
     */
    public static function render_report_columns($column, $post_id) {
        switch ($column) {
            case 'business_name':
                $business_name = get_post_meta($post_id, 'business_name', true);
                echo esc_html($business_name ? $business_name : '-');
                break;
                
            case 'industry':
                $industry = get_post_meta($post_id, 'industry', true);
                echo esc_html($industry ? $industry : '-');
                break;
                
            case 'report_status':
                $analysis_result = get_post_meta($post_id, 'analysis_result', true);
                if (!empty($analysis_result)) {
                    echo '<span class="status-complete">' . __('Complete', 'aiwinlab') . '</span>';
                } else {
                    echo '<span class="status-pending">' . __('Pending', 'aiwinlab') . '</span>';
                }
                break;
        }
    }
    
    /**
     * Add custom columns to consultation list
     */
    public static function add_consultation_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            // Insert custom columns after 'title' column
            if ($key === 'title') {
                $new_columns['client_email'] = __('Email', 'aiwinlab');
                $new_columns['preferred_date'] = __('Preferred Date', 'aiwinlab');
                $new_columns['preferred_time'] = __('Preferred Time', 'aiwinlab');
                $new_columns['related_report'] = __('Related Report', 'aiwinlab');
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Render consultation columns content
     */
    public static function render_consultation_columns($column, $post_id) {
        switch ($column) {
            case 'client_email':
                $email = get_post_meta($post_id, 'client_email', true);
                echo esc_html($email ? $email : '-');
                break;
                
            case 'preferred_date':
                $date = get_post_meta($post_id, 'preferred_date', true);
                echo esc_html($date ? $date : '-');
                break;
                
            case 'preferred_time':
                $time = get_post_meta($post_id, 'preferred_time', true);
                if ($time) {
                    $time_labels = array(
                        'morning' => __('Morning (9am - 12pm)', 'aiwinlab'),
                        'afternoon' => __('Afternoon (1pm - 5pm)', 'aiwinlab'),
                        'evening' => __('Evening (6pm - 8pm)', 'aiwinlab')
                    );
                    
                    echo isset($time_labels[$time]) ? esc_html($time_labels[$time]) : esc_html($time);
                } else {
                    echo '-';
                }
                break;
                
            case 'related_report':
                $report_id = get_post_meta($post_id, 'report_id', true);
                if ($report_id) {
                    echo '<a href="' . esc_url(get_edit_post_link($report_id)) . '">' . __('View Report', 'aiwinlab') . '</a>';
                } else {
                    echo '-';
                }
                break;
        }
    }
    
    /**
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        // Report details meta box
        add_meta_box(
            'aiwinlab_report_details',
            __('AI Analysis Report Details', 'aiwinlab'),
            array(__CLASS__, 'render_report_details_meta_box'),
            'aiwinlab_report',
            'normal',
            'high'
        );
        
        // Consultation details meta box
        add_meta_box(
            'aiwinlab_consultation_details',
            __('Consultation Request Details', 'aiwinlab'),
            array(__CLASS__, 'render_consultation_details_meta_box'),
            'aiwinlab_consultation',
            'normal',
            'high'
        );
    }
    
    /**
     * Render report details meta box
     */
    public static function render_report_details_meta_box($post) {
        wp_nonce_field('aiwinlab_report_meta', 'aiwinlab_report_meta_nonce');
        
        $business_name = get_post_meta($post->ID, 'business_name', true);
        $business_email = get_post_meta($post->ID, 'business_email', true);
        $website_url = get_post_meta($post->ID, 'website_url', true);
        $industry = get_post_meta($post->ID, 'industry', true);
        $analysis_result = get_post_meta($post->ID, 'analysis_result', true);
        
        ?>
        <div class="aiwinlab-meta-box">
            <div class="meta-field">
                <label for="business_name"><?php _e('Business Name', 'aiwinlab'); ?></label>
                <input type="text" id="business_name" name="business_name" value="<?php echo esc_attr($business_name); ?>" class="regular-text">
            </div>
            
            <div class="meta-field">
                <label for="business_email"><?php _e('Business Email', 'aiwinlab'); ?></label>
                <input type="email" id="business_email" name="business_email" value="<?php echo esc_attr($business_email); ?>" class="regular-text">
            </div>
            
            <div class="meta-field">
                <label for="website_url"><?php _e('Website URL', 'aiwinlab'); ?></label>
                <input type="url" id="website_url" name="website_url" value="<?php echo esc_attr($website_url); ?>" class="regular-text">
            </div>
            
            <div class="meta-field">
                <label for="industry"><?php _e('Industry', 'aiwinlab'); ?></label>
                <input type="text" id="industry" name="industry" value="<?php echo esc_attr($industry); ?>" class="regular-text">
            </div>
            
            <div class="meta-field">
                <label><?php _e('Analysis Status', 'aiwinlab'); ?></label>
                <div class="analysis-status">
                    <?php
                    if (!empty($analysis_result)) {
                        echo '<span class="status-complete">' . __('Analysis Complete', 'aiwinlab') . '</span>';
                    } else {
                        echo '<span class="status-pending">' . __('Analysis Pending', 'aiwinlab') . '</span>';
                    }
                    ?>
                </div>
            </div>
            
            <?php if (!empty($analysis_result)): ?>
            <div class="meta-field">
                <label><?php _e('View Report', 'aiwinlab'); ?></label>
                <div class="report-actions">
                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" target="_blank" class="button"><?php _e('View Published Report', 'aiwinlab'); ?></a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render consultation details meta box
     */
    public static function render_consultation_details_meta_box($post) {
        wp_nonce_field('aiwinlab_consultation_meta', 'aiwinlab_consultation_meta_nonce');
        
        $client_name = get_post_meta($post->ID, 'client_name', true);
        $client_email = get_post_meta($post->ID, 'client_email', true);
        $client_phone = get_post_meta($post->ID, 'client_phone', true);
        $preferred_date = get_post_meta($post->ID, 'preferred_date', true);
        $preferred_time = get_post_meta($post->ID, 'preferred_time', true);
        $notes = get_post_meta($post->ID, 'notes', true);
        $report_id = get_post_meta($post->ID, 'report_id', true);
        
        $time_options = array(
            'morning' => __('Morning (9am - 12pm)', 'aiwinlab'),
            'afternoon' => __('Afternoon (1pm - 5pm)', 'aiwinlab'),
            'evening' => __('Evening (6pm - 8pm)', 'aiwinlab')
        );
        
        ?>
        <div class="aiwinlab-meta-box">
            <div class="meta-field">
                <label for="client_name"><?php _e('Client Name', 'aiwinlab'); ?></label>
                <input type="text" id="client_name" name="client_name" value="<?php echo esc_attr($client_name); ?>" class="regular-text">
            </div>
            
            <div class="meta-field">
                <label for="client_email"><?php _e('Client Email', 'aiwinlab'); ?></label>
                <input type="email" id="client_email" name="client_email" value="<?php echo esc_attr($client_email); ?>" class="regular-text">
            </div>
            
            <div class="meta-field">
                <label for="client_phone"><?php _e('Client Phone', 'aiwinlab'); ?></label>
                <input type="tel" id="client_phone" name="client_phone" value="<?php echo esc_attr($client_phone); ?>" class="regular-text">
            </div>
            
            <div class="meta-field">
                <label for="preferred_date"><?php _e('Preferred Date', 'aiwinlab'); ?></label>
                <input type="date" id="preferred_date" name="preferred_date" value="<?php echo esc_attr($preferred_date); ?>">
            </div>
            
            <div class="meta-field">
                <label for="preferred_time"><?php _e('Preferred Time', 'aiwinlab'); ?></label>
                <select id="preferred_time" name="preferred_time">
                    <option value=""><?php _e('Select a time', 'aiwinlab'); ?></option>
                    <?php
                    foreach ($time_options as $value => $label) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($value),
                            selected($preferred_time, $value, false),
                            esc_html($label)
                        );
                    }
                    ?>
                </select>
            </div>
            
            <div class="meta-field">
                <label for="notes"><?php _e('Notes', 'aiwinlab'); ?></label>
                <textarea id="notes" name="notes" rows="4" class="large-text"><?php echo esc_textarea($notes); ?></textarea>
            </div>
            
            <div class="meta-field">
                <label for="report_id"><?php _e('Related Report', 'aiwinlab'); ?></label>
                <?php
                if ($report_id) {
                    $report_title = get_the_title($report_id);
                    echo '<div class="related-report">';
                    echo '<div class="related-report">';
                    echo '<a href="' . esc_url(get_edit_post_link($report_id)) . '">' . esc_html($report_title) . '</a>';
                    echo ' <a href="' . esc_url(get_permalink($report_id)) . '" target="_blank"><span class="dashicons dashicons-visibility"></span></a>';
                    echo '</div>';
                } else {
                    echo '<p>' . __('No related report.', 'aiwinlab') . '</p>';
                }
                ?>
            </div>
            
            <div class="meta-field">
                <label for="consultation_status"><?php _e('Consultation Status', 'aiwinlab'); ?></label>
                <select id="consultation_status" name="consultation_status">
                    <option value="pending" <?php selected(get_post_status($post->ID), 'pending'); ?>><?php _e('Pending', 'aiwinlab'); ?></option>
                    <option value="scheduled" <?php selected(get_post_status($post->ID), 'scheduled'); ?>><?php _e('Scheduled', 'aiwinlab'); ?></option>
                    <option value="completed" <?php selected(get_post_status($post->ID), 'completed'); ?>><?php _e('Completed', 'aiwinlab'); ?></option>
                    <option value="cancelled" <?php selected(get_post_status($post->ID), 'cancelled'); ?>><?php _e('Cancelled', 'aiwinlab'); ?></option>
                </select>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save report meta box data
     */
    public static function save_report_meta($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['aiwinlab_report_meta_nonce'])) {
            return;
        }
        
        // Verify the nonce
        if (!wp_verify_nonce($_POST['aiwinlab_report_meta_nonce'], 'aiwinlab_report_meta')) {
            return;
        }
        
        // If this is an autosave, we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Update the meta fields
        if (isset($_POST['business_name'])) {
            update_post_meta($post_id, 'business_name', sanitize_text_field($_POST['business_name']));
        }
        
        if (isset($_POST['business_email'])) {
            update_post_meta($post_id, 'business_email', sanitize_email($_POST['business_email']));
        }
        
        if (isset($_POST['website_url'])) {
            update_post_meta($post_id, 'website_url', esc_url_raw($_POST['website_url']));
        }
        
        if (isset($_POST['industry'])) {
            update_post_meta($post_id, 'industry', sanitize_text_field($_POST['industry']));
        }
    }
    
    /**
     * Save consultation meta box data
     */
    public static function save_consultation_meta($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['aiwinlab_consultation_meta_nonce'])) {
            return;
        }
        
        // Verify the nonce
        if (!wp_verify_nonce($_POST['aiwinlab_consultation_meta_nonce'], 'aiwinlab_consultation_meta')) {
            return;
        }
        
        // If this is an autosave, we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Update the meta fields
        if (isset($_POST['client_name'])) {
            update_post_meta($post_id, 'client_name', sanitize_text_field($_POST['client_name']));
        }
        
        if (isset($_POST['client_email'])) {
            update_post_meta($post_id, 'client_email', sanitize_email($_POST['client_email']));
        }
        
        if (isset($_POST['client_phone'])) {
            update_post_meta($post_id, 'client_phone', sanitize_text_field($_POST['client_phone']));
        }
        
        if (isset($_POST['preferred_date'])) {
            update_post_meta($post_id, 'preferred_date', sanitize_text_field($_POST['preferred_date']));
        }
        
        if (isset($_POST['preferred_time'])) {
            update_post_meta($post_id, 'preferred_time', sanitize_text_field($_POST['preferred_time']));
        }
        
        if (isset($_POST['notes'])) {
            update_post_meta($post_id, 'notes', sanitize_textarea_field($_POST['notes']));
        }
        
        // Update post status if changed
        if (isset($_POST['consultation_status']) && $_POST['consultation_status'] !== get_post_status($post_id)) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_status' => sanitize_text_field($_POST['consultation_status'])
            ));
        }
    }
    
    /**
     * Test API connection
     */
    public static function test_api_connection() {
        // Verify nonce
        check_admin_referer('aiwinlab-options');
        
        // Get API key
        $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
        
        if (empty($api_key)) {
            wp_send_json_error(__('API key is required.', 'aiwinlab'));
        }
        
        // Test the API connection by making a simple request
        $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key;
        
        $request_body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => 'Hello, please respond with "API connection successful" if you receive this message.'
                        )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.1,
                'maxOutputTokens' => 20
            )
        );
        
        $response = wp_remote_post($api_url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode($request_body),
            'timeout' => 15,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $response_body = wp_remote_retrieve_body($response);
            $error_data = json_decode($response_body, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : __('Unknown API error.', 'aiwinlab');
            
            wp_send_json_error($error_message);
        }
        
        wp_send_json_success(__('API connection successful.', 'aiwinlab'));
    }
}

// Initialize admin functionality
AI_WinLab_Admin::init(); 