<?php
// inc/shortcodes.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register shortcodes for AI WinLab
 */
function aiwinlab_register_shortcodes() {
    add_shortcode('aiwinlab_analysis_wizard', 'aiwinlab_analysis_wizard_shortcode');
    add_shortcode('aiwinlab_reports', 'aiwinlab_reports_shortcode');
}
add_action('init', 'aiwinlab_register_shortcodes');

/**
 * Analysis wizard shortcode
 */
function aiwinlab_analysis_wizard_shortcode($atts) {
    // Enqueue required scripts and styles
    wp_enqueue_style('aiwinlab-wizard-css');
    wp_enqueue_script('aiwinlab-wizard-js');
    
    // Check if API key is set
    $api_key = get_option('aiwinlab_gemini_api_key');
    if (empty($api_key)) {
        return '<div class="aiwinlab-error-notice">' . __('AI WinLab requires a Gemini API key to function. Please set your API key in the admin settings.', 'aiwinlab') . '</div>';
    }
    
    // Start output buffering
    ob_start();
    
    // Include the wizard template
    include_once(get_template_directory() . '/templates/analysis-wizard.php');
    
    // Return the buffered content
    return ob_get_clean();
}

/**
 * Reports shortcode
 */
function aiwinlab_reports_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(array(
        'limit' => 10,
        'show_filters' => 'true',
    ), $atts);
    
    $limit = intval($atts['limit']);
    $show_filters = filter_var($atts['show_filters'], FILTER_VALIDATE_BOOLEAN);
    
    // Enqueue required scripts and styles
    wp_enqueue_style('aiwinlab-reports-css');
    wp_enqueue_script('aiwinlab-reports-js');
    
    // Start output buffering
    ob_start();
    
    // Get reports
    $args = array(
        'post_type' => 'aiwinlab_report',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    // Check for filters
    if (isset($_GET['industry'])) {
        $args['meta_query'][] = array(
            'key' => 'industry',
            'value' => sanitize_text_field($_GET['industry']),
            'compare' => '=',
        );
    }
    
    if (isset($_GET['category'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'business_category',
            'field' => 'slug',
            'terms' => sanitize_text_field($_GET['category']),
        );
    }
    
    $reports_query = new WP_Query($args);
    
    // Include the reports template
    include_once(get_template_directory() . '/templates/reports-list.php');
    
    // Return the buffered content
    return ob_get_clean();
}