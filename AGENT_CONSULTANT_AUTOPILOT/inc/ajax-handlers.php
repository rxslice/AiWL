<?php
// inc/ajax-handlers.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register AJAX handlers for AI WinLab
 */
function aiwinlab_register_ajax_handlers() {
    add_action('wp_ajax_aiwinlab_analyze', 'aiwinlab_ajax_analyze_business');
    add_action('wp_ajax_nopriv_aiwinlab_analyze', 'aiwinlab_ajax_analyze_business');
    
    add_action('wp_ajax_aiwinlab_get_report', 'aiwinlab_ajax_get_report');
    add_action('wp_ajax_nopriv_aiwinlab_get_report', 'aiwinlab_ajax_get_report');
    
    add_action('wp_ajax_aiwinlab_schedule_consultation', 'aiwinlab_ajax_schedule_consultation');
    add_action('wp_ajax_nopriv_aiwinlab_schedule_consultation', 'aiwinlab_ajax_schedule_consultation');
}
add_action('init', 'aiwinlab_register_ajax_handlers');

/**
 * AJAX handler for business analysis
 */
function aiwinlab_ajax_analyze_business() {
    // Verify nonce
    if (!check_ajax_referer('aiwinlab_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid security token');
    }
    
    // Get analysis data
    $analysis_data = isset($_POST['analysisData']) ? $_POST['analysisData'] : array();
    
    if (empty($analysis_data)) {
        wp_send_json_error('No analysis data provided');
    }
    
    // Process analysis
    require_once get_template_directory() . '/inc/analysis-processor.php';
    $processor = new AI_WinLab_Analysis_Processor();
    
    try {
        $result = $processor->process_analysis($analysis_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        // Create a report post to store the results
        $report_id = wp_insert_post(array(
            'post_title' => 'AI Analysis for ' . sanitize_text_field($analysis_data['businessName']),
            'post_type' => 'aiwinlab_report',
            'post_status' => 'publish',
            'post_content' => wp_json_encode($result),
            'meta_input' => array(
                'business_name' => sanitize_text_field($analysis_data['businessName']),
                'business_email' => sanitize_email($analysis_data['businessEmail']),
                'website_url' => esc_url_raw($analysis_data['websiteUrl']),
                'industry' => sanitize_text_field($analysis_data['industry']),
                'analysis_result' => $result,
            ),
        ));
        
        if (is_wp_error($report_id)) {
            wp_send_json_error('Failed to save report: ' . $report_id->get_error_message());
        }
        
        // Return success response
        wp_send_json_success(array(
            'report_id' => $report_id,
            'report_url' => get_permalink($report_id),
            'data' => $result
        ));
    } catch (Exception $e) {
        wp_send_json_error('Analysis failed: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for getting a report
 */
function aiwinlab_ajax_get_report() {
    // Verify nonce
    if (!check_ajax_referer('aiwinlab_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid security token');
    }
    
    // Get report ID
    $report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
    
    if (empty($report_id)) {
        wp_send_json_error('No report ID provided');
    }
    
    // Get report
    $report = get_post($report_id);
    
    if (!$report || $report->post_type !== 'aiwinlab_report') {
        wp_send_json_error('Report not found');
    }
    
    // Get report data
    $report_data = get_post_meta($report_id, 'analysis_result', true);
    
    if (empty($report_data)) {
        wp_send_json_error('Report data not found');
    }
    
    // Return report data
    wp_send_json_success(array(
        'report' => $report,
        'data' => $report_data
    ));
}

/**
 * AJAX handler for scheduling a consultation
 */
function aiwinlab_ajax_schedule_consultation() {
    // Verify nonce
    if (!check_ajax_referer('aiwinlab_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid security token');
    }
    
    // Get consultation data
    $consultation_data = isset($_POST['consultationData']) ? $_POST['consultationData'] : array();
    
    if (empty($consultation_data)) {
        wp_send_json_error('No consultation data provided');
    }
    
    // Validate required fields
    $required_fields = array('consultName', 'consultEmail', 'consultDate', 'consultTime');
    
    foreach ($required_fields as $field) {
        if (empty($consultation_data[$field])) {
            wp_send_json_error('Required field missing: ' . $field);
        }
    }
    
    // Create consultation post
    $consultation_id = wp_insert_post(array(
        'post_title' => 'Consultation Request from ' . sanitize_text_field($consultation_data['consultName']),
        'post_type' => 'aiwinlab_consultation',
        'post_status' => 'pending',
        'meta_input' => array(
            'client_name' => sanitize_text_field($consultation_data['consultName']),
            'client_email' => sanitize_email($consultation_data['consultEmail']),
            'client_phone' => isset($consultation_data['consultPhone']) ? sanitize_text_field($consultation_data['consultPhone']) : '',
            'preferred_date' => sanitize_text_field($consultation_data['consultDate']),
            'preferred_time' => sanitize_text_field($consultation_data['consultTime']),
            'notes' => isset($consultation_data['consultNotes']) ? sanitize_textarea_field($consultation_data['consultNotes']) : '',
            'report_id' => isset($consultation_data['reportId']) ? intval($consultation_data['reportId']) : 0,
        ),
    ));
    
    if (is_wp_error($consultation_id)) {
        wp_send_json_error('Failed to save consultation request: ' . $consultation_id->get_error_message());
    }
    
    // Send notification email
    $to = get_option('admin_email');
    $subject = 'New AI WinLab Consultation Request';
    $message = "A new consultation request has been submitted:\n\n";
    $message .= "Name: " . sanitize_text_field($consultation_data['consultName']) . "\n";
    $message .= "Email: " . sanitize_email($consultation_data['consultEmail']) . "\n";
    
    if (!empty($consultation_data['consultPhone'])) {
        $message .= "Phone: " . sanitize_text_field($consultation_data['consultPhone']) . "\n";
    }
    
    $message .= "Preferred Date: " . sanitize_text_field($consultation_data['consultDate']) . "\n";
    $message .= "Preferred Time: " . sanitize_text_field($consultation_data['consultTime']) . "\n";
    
    if (!empty($consultation_data['consultNotes'])) {
        $message .= "Notes: " . sanitize_textarea_field($consultation_data['consultNotes']) . "\n";
    }
    
    $message .= "\nPlease log in to the admin panel to view the full details.";
    
    wp_mail($to, $subject, $message);
    
    // Return success response
    wp_send_json_success(array(
        'consultation_id' => $consultation_id,
        'message' => 'Consultation request submitted successfully'
    ));
}