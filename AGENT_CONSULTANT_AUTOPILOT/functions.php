<?php
// functions.php - Register necessary WordPress components

function ai_winlab_setup() {
    // Theme setup
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    
    // Register menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'aiwinlab'),
        'footer' => __('Footer Menu', 'aiwinlab'),
    ));
    
    // Register custom post type for Reports
    register_post_type('aiwinlab_report', array(
        'labels' => array(
            'name' => __('AI Analysis Reports', 'aiwinlab'),
            'singular_name' => __('AI Analysis Report', 'aiwinlab'),
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'),
        'menu_icon' => 'dashicons-analytics',
        'show_in_rest' => true,
    ));
    
    // Register custom taxonomy for Report Categories
    register_taxonomy('report_category', 'aiwinlab_report', array(
        'label' => __('Report Categories', 'aiwinlab'),
        'hierarchical' => true,
        'show_in_rest' => true,
    ));
}
add_action('after_setup_theme', 'ai_winlab_setup');

// Enqueue scripts and styles
function ai_winlab_scripts() {
    // Main stylesheet
    wp_enqueue_style('aiwinlab-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // Tailwind CSS (from CDN for development, would be compiled in production)
    wp_enqueue_style('tailwind-css', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css', array(), '2.2.19');
    
    // Custom fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap', array(), null);
    
    // Animation library
    wp_enqueue_style('animate-css', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', array(), '4.1.1');
    
    // Data visualization libraries
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js', array(), '3.7.1', true);
    wp_enqueue_script('d3-js', 'https://d3js.org/d3.v7.min.js', array(), '7.0.0', true);
    
    // Scroll animation
    wp_enqueue_script('aos-js', 'https://unpkg.com/aos@next/dist/aos.js', array(), null, true);
    wp_enqueue_style('aos-css', 'https://unpkg.com/aos@next/dist/aos.css', array(), null);
    
    // Main application JavaScript
    wp_enqueue_script('aiwinlab-main', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0.0', true);
    
    // AI analysis engine
    wp_enqueue_script('aiwinlab-ai-engine', get_template_directory_uri() . '/js/ai-winlab-agent.js', array('jquery'), '1.0.0', true);
    
    // Visualization components
    wp_enqueue_script('aiwinlab-visualizations', get_template_directory_uri() . '/js/visualizations.js', array('chart-js', 'd3-js'), '1.0.0', true);
    
    // Localize script with WordPress data
    wp_localize_script('aiwinlab-main', 'aiWinLabData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('aiwinlab_nonce'),
        'homeUrl' => home_url(),
        'isLoggedIn' => is_user_logged_in(),
    ));
}
add_action('wp_enqueue_scripts', 'ai_winlab_scripts');

// Add REST API endpoint for AI Analysis
function ai_winlab_register_api_routes() {
    register_rest_route('aiwinlab/v1', '/analyze', array(
        'methods' => 'POST',
        'callback' => 'ai_winlab_analyze_business',
        'permission_callback' => function() {
            return true; // Public endpoint, will use nonce validation
        }
    ));
    
    register_rest_route('aiwinlab/v1', '/reports/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'ai_winlab_get_report',
        'permission_callback' => function() {
            return true; // Public endpoint
        }
    ));
}
add_action('rest_api_init', 'ai_winlab_register_api_routes');

// Analysis processing function
function ai_winlab_analyze_business($request) {
    // Verify nonce
    if (!wp_verify_nonce($request->get_header('X-WP-Nonce'), 'aiwinlab_nonce')) {
        return new WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
    }
    
    // Get parameters
    $params = $request->get_params();
    
    // Initialize analysis processor
    require_once get_template_directory() . '/inc/analysis-processor.php';
    $processor = new AI_WinLab_Analysis_Processor();
    
    // Process the analysis
    $result = $processor->process_analysis($params);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    // Create a report post to store the results
    $report_id = wp_insert_post(array(
        'post_title' => 'AI Analysis for ' . sanitize_text_field($params['businessName']),
        'post_type' => 'aiwinlab_report',
        'post_status' => 'publish',
        'post_content' => wp_json_encode($result),
        'meta_input' => array(
            'business_name' => sanitize_text_field($params['businessName']),
            'business_email' => sanitize_email($params['businessEmail']),
            'website_url' => esc_url_raw($params['websiteUrl']),
            'industry' => sanitize_text_field($params['industry']),
            'analysis_result' => $result,
        ),
    ));
    
    return array(
        'success' => true,
        'report_id' => $report_id,
        'report_url' => get_permalink($report_id),
    );
}