<?php
// inc/template-functions.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template functions for AI WinLab
 */

/**
 * Get report template
 */
function aiwinlab_get_report_template($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    if (get_post_type($post_id) !== 'aiwinlab_report') {
        return '';
    }
    
    ob_start();
    include_once(AIWINLAB_PLUGIN_DIR . 'templates/single-report.php');
    return ob_get_clean();
}

/**
 * Custom template for single report
 */
function aiwinlab_custom_template($template) {
    if (is_singular('aiwinlab_report')) {
        // Check if theme has a template
        $theme_template = locate_template('single-aiwinlab_report.php');
        
        if ($theme_template) {
            return $theme_template;
        }
        
        // Use plugin template
        return AIWINLAB_PLUGIN_DIR . 'templates/page-report.php';
    }
    
    return $template;
}
add_filter('template_include', 'aiwinlab_custom_template');

/**
 * Register Font Awesome
 */
function aiwinlab_register_fontawesome() {
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
        array(),
        '5.15.4'
    );
}
add_action('wp_enqueue_scripts', 'aiwinlab_register_fontawesome');
add_action('admin_enqueue_scripts', 'aiwinlab_register_fontawesome');

/**
 * Enqueue AOS animation library
 */
function aiwinlab_register_aos() {
    wp_enqueue_style(
        'aos-css',
        'https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css',
        array(),
        '2.3.4'
    );
    
    wp_enqueue_script(
        'aos-js',
        'https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js',
        array(),
        '2.3.4',
        true
    );
}
add_action('wp_enqueue_scripts', 'aiwinlab_register_aos');

/**
 * Custom body class for AI WinLab pages
 */
function aiwinlab_body_class($classes) {
    if (is_singular('aiwinlab_report') || is_page(get_option('aiwinlab_wizard_page_id')) || is_page(get_option('aiwinlab_reports_page_id'))) {
        $classes[] = 'aiwinlab-page';
    }
    
    return $classes;
}
add_filter('body_class', 'aiwinlab_body_class');

/**
 * Apply custom colors
 */
function aiwinlab_custom_colors() {
    $primary_color = get_option('aiwinlab_primary_color', '#4a6cf7');
    $secondary_color = get_option('aiwinlab_secondary_color', '#8e44ad');
    $accent_color = get_option('aiwinlab_accent_color', '#00c9a7');
    
    ?>
    <style>
        :root {
            --ai-winlab-primary: <?php echo esc_attr($primary_color); ?>;
            --ai-winlab-secondary: <?php echo esc_attr($secondary_color); ?>;
            --ai-winlab-accent: <?php echo esc_attr($accent_color); ?>;
        }
    </style>
    <?php
}
add_action('wp_head', 'aiwinlab_custom_colors');
add_action('admin_head', 'aiwinlab_custom_colors');