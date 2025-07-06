<?php
// inc/setup.php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configure AI WinLab on activation
 */
class AI_WinLab_Setup {
    /**
     * Initialize the setup process
     */
    public static function init() {
        // Register activation hook
        register_activation_hook(AIWINLAB_PLUGIN_FILE, array(__CLASS__, 'activate'));
        
        // Register deactivation hook
        register_deactivation_hook(AIWINLAB_PLUGIN_FILE, array(__CLASS__, 'deactivate'));
        
        // Add admin menu
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        
        // Register custom post types
        add_action('init', array(__CLASS__, 'register_post_types'));
        
        // Add admin notices
        add_action('admin_notices', array(__CLASS__, 'admin_notices'));
    }
    
    /**
     * Activation hook
     */
    public static function activate() {
        // Create necessary database tables
        self::create_database_tables();
        
        // Create required pages
        self::create_required_pages();
        
        // Set default options
        self::set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag
        update_option('aiwinlab_activated', true);
    }
    
    /**
     * Deactivation hook
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create required database tables
     */
    private static function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Analysis submissions table
        $table_name = $wpdb->prefix . 'aiwinlab_submissions';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            business_name varchar(255) NOT NULL,
            business_email varchar(255) NOT NULL,
            website_url varchar(255) NOT NULL,
            industry varchar(100) NOT NULL,
            submission_data longtext NOT NULL,
            report_id bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create required pages
     */
    private static function create_required_pages() {
        // Analysis wizard page
        $wizard_page = array(
            'post_title'    => 'AI Implementation Analysis',
            'post_content'  => '[aiwinlab_analysis_wizard]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
        );
        
        $wizard_page_id = wp_insert_post($wizard_page);
        
        if (!is_wp_error($wizard_page_id)) {
            update_option('aiwinlab_wizard_page_id', $wizard_page_id);
        }
        
        // Reports page
        $reports_page = array(
            'post_title'    => 'AI Analysis Reports',
            'post_content'  => '[aiwinlab_reports]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
        );
        
        $reports_page_id = wp_insert_post($reports_page);
        
        if (!is_wp_error($reports_page_id)) {
            update_option('aiwinlab_reports_page_id', $reports_page_id);
        }
    }
    
    /**
     * Set default options
     */
    private static function set_default_options() {
        // Default settings
        $default_settings = array(
            'aiwinlab_gemini_api_key' => '',
            'aiwinlab_email_notifications' => '1',
            'aiwinlab_primary_color' => '#4a6cf7',
            'aiwinlab_secondary_color' => '#8e44ad',
            'aiwinlab_accent_color' => '#00c9a7',
        );
        
        foreach ($default_settings as $key => $value) {
            if (get_option($key) === false) {
                update_option($key, $value);
            }
        }
    }
    
    /**
     * Register custom post types
     */
    public static function register_post_types() {
        // Register Analysis Reports CPT
        register_post_type('aiwinlab_report', array(
            'labels' => array(
                'name' => __('AI Analysis Reports', 'aiwinlab'),
                'singular_name' => __('AI Analysis Report', 'aiwinlab'),
                'menu_name' => __('AI Reports', 'aiwinlab'),
                'all_items' => __('All Reports', 'aiwinlab'),
                'add_new' => __('Add New', 'aiwinlab'),
                'add_new_item' => __('Add New Report', 'aiwinlab'),
                'edit_item' => __('Edit Report', 'aiwinlab'),
                'new_item' => __('New Report', 'aiwinlab'),
                'view_item' => __('View Report', 'aiwinlab'),
                'search_items' => __('Search Reports', 'aiwinlab'),
                'not_found' => __('No reports found', 'aiwinlab'),
                'not_found_in_trash' => __('No reports found in Trash', 'aiwinlab'),
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'),
            'menu_icon' => 'dashicons-analytics',
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'ai-analysis-reports'),
        ));
        
        // Register Consultation Requests CPT
        register_post_type('aiwinlab_consultation', array(
            'labels' => array(
                'name' => __('Consultation Requests', 'aiwinlab'),
                'singular_name' => __('Consultation Request', 'aiwinlab'),
                'menu_name' => __('Consultations', 'aiwinlab'),
                'all_items' => __('All Consultations', 'aiwinlab'),
                'add_new' => __('Add New', 'aiwinlab'),
                'add_new_item' => __('Add New Consultation', 'aiwinlab'),
                'edit_item' => __('Edit Consultation', 'aiwinlab'),
                'new_item' => __('New Consultation', 'aiwinlab'),
                'view_item' => __('View Consultation', 'aiwinlab'),
                'search_items' => __('Search Consultations', 'aiwinlab'),
                'not_found' => __('No consultations found', 'aiwinlab'),
                'not_found_in_trash' => __('No consultations found in Trash', 'aiwinlab'),
            ),
            'public' => false,
            'show_ui' => true,
            'supports' => array('title', 'custom-fields'),
            'menu_icon' => 'dashicons-calendar-alt',
            'show_in_menu' => 'edit.php?post_type=aiwinlab_report',
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ));
        
        // Register Business Categories Taxonomy
        register_taxonomy('business_category', 'aiwinlab_report', array(
            'label' => __('Business Categories', 'aiwinlab'),
            'hierarchical' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'business-category'),
        ));
    }
    
    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('AI WinLab', 'aiwinlab'),
            __('AI WinLab', 'aiwinlab'),
            'manage_options',
            'aiwinlab',
            array(__CLASS__, 'render_settings_page'),
            'dashicons-superhero',
            30
        );
        
        // Settings submenu
        add_submenu_page(
            'aiwinlab',
            __('Settings', 'aiwinlab'),
            __('Settings', 'aiwinlab'),
            'manage_options',
            'aiwinlab',
            array(__CLASS__, 'render_settings_page')
        );
        
        // Analytics submenu
        add_submenu_page(
            'aiwinlab',
            __('Analytics', 'aiwinlab'),
            __('Analytics', 'aiwinlab'),
            'manage_options',
            'aiwinlab-analytics',
            array(__CLASS__, 'render_analytics_page')
        );
        
        // Help submenu
        add_submenu_page(
            'aiwinlab',
            __('Help & Documentation', 'aiwinlab'),
            __('Help & Documentation', 'aiwinlab'),
            'manage_options',
            'aiwinlab-help',
            array(__CLASS__, 'render_help_page')
        );
    }
    
    /**
     * Register settings
     */
    public static function register_settings() {
        // API settings section
        add_settings_section(
            'aiwinlab_api_settings',
            __('API Settings', 'aiwinlab'),
            array(__CLASS__, 'render_api_settings_section'),
            'aiwinlab'
        );
        
        // Gemini API Key
        register_setting('aiwinlab', 'aiwinlab_gemini_api_key');
        add_settings_field(
            'aiwinlab_gemini_api_key',
            __('Gemini API Key', 'aiwinlab'),
            array(__CLASS__, 'render_api_key_field'),
            'aiwinlab',
            'aiwinlab_api_settings'
        );
        
        // Appearance settings section
        add_settings_section(
            'aiwinlab_appearance_settings',
            __('Appearance Settings', 'aiwinlab'),
            array(__CLASS__, 'render_appearance_settings_section'),
            'aiwinlab'
        );
        
        // Primary Color
        register_setting('aiwinlab', 'aiwinlab_primary_color');
        add_settings_field(
            'aiwinlab_primary_color',
            __('Primary Color', 'aiwinlab'),
            array(__CLASS__, 'render_color_field'),
            'aiwinlab',
            'aiwinlab_appearance_settings',
            array('option' => 'aiwinlab_primary_color', 'default' => '#4a6cf7')
        );
        
        // Secondary Color
        register_setting('aiwinlab', 'aiwinlab_secondary_color');
        add_settings_field(
            'aiwinlab_secondary_color',
            __('Secondary Color', 'aiwinlab'),
            array(__CLASS__, 'render_color_field'),
            'aiwinlab',
            'aiwinlab_appearance_settings',
            array('option' => 'aiwinlab_secondary_color', 'default' => '#8e44ad')
        );
        
        // Notification settings section
        add_settings_section(
            'aiwinlab_notification_settings',
            __('Notification Settings', 'aiwinlab'),
            array(__CLASS__, 'render_notification_settings_section'),
            'aiwinlab'
        );
        
        // Email Notifications
        register_setting('aiwinlab', 'aiwinlab_email_notifications');
        add_settings_field(
            'aiwinlab_email_notifications',
            __('Email Notifications', 'aiwinlab'),
            array(__CLASS__, 'render_email_notifications_field'),
            'aiwinlab',
            'aiwinlab_notification_settings'
        );
    }
    
    /**
     * Render API settings section
     */
    public static function render_api_settings_section() {
        echo '<p>' . __('Configure API keys for AI services used by AI WinLab.', 'aiwinlab') . '</p>';
    }
    
    /**
     * Render API key field
     */
    public static function render_api_key_field() {
        $api_key = get_option('aiwinlab_gemini_api_key');
        ?>
        <input type="password" name="aiwinlab_gemini_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
        <p class="description">
            <?php _e('Enter your Gemini API key. <a href="https://ai.google.dev/" target="_blank">Get a key here</a>', 'aiwinlab'); ?>
        </p>
        <?php
    }
    
    /**
     * Render appearance settings section
     */
    public static function render_appearance_settings_section() {
        echo '<p>' . __('Customize the appearance of the AI WinLab interface.', 'aiwinlab') . '</p>';
    }
    
    /**
     * Render color field
     */
    public static function render_color_field($args) {
        $option = $args['option'];
        $default = $args['default'];
        $value = get_option($option, $default);
        ?>
        <input type="text" name="<?php echo esc_attr($option); ?>" value="<?php echo esc_attr($value); ?>" class="aiwinlab-color-field" data-default-color="<?php echo esc_attr($default); ?>" />
        <?php
    }
    
    /**
     * Render notification settings section
     */
    public static function render_notification_settings_section() {
        echo '<p>' . __('Configure notification settings for new analysis submissions and consultation requests.', 'aiwinlab') . '</p>';
    }
    
    /**
     * Render email notifications field
     */
    public static function render_email_notifications_field() {
        $enabled = get_option('aiwinlab_email_notifications', '1');
        ?>
        <label>
            <input type="checkbox" name="aiwinlab_email_notifications" value="1" <?php checked('1', $enabled); ?> />
            <?php _e('Send email notifications for new submissions and consultation requests', 'aiwinlab'); ?>
        </label>
        <?php
    }
    
    /**
     * Render settings page
     */
    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('AI WinLab Settings', 'aiwinlab'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('aiwinlab');
                do_settings_sections('aiwinlab');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render analytics page
     */
    public static function render_analytics_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('AI WinLab Analytics', 'aiwinlab'); ?></h1>
            
            <div class="aiwinlab-analytics-dashboard">
                <?php self::render_analytics_dashboard(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render help page
     */
    public static function render_help_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('AI WinLab Help & Documentation', 'aiwinlab'); ?></h1>
            
            <div class="aiwinlab-help-content">
                <div class="aiwinlab-help-section">
                    <h2><?php _e('Getting Started', 'aiwinlab'); ?></h2>
                    <p><?php _e('AI WinLab helps businesses analyze their operations and identify opportunities for AI implementation to increase efficiency and revenue.', 'aiwinlab'); ?></p>
                    
                    <h3><?php _e('Quick Setup Guide', 'aiwinlab'); ?></h3>
                    <ol>
                        <li><?php _e('Enter your Gemini API key in the Settings page', 'aiwinlab'); ?></li>
                        <li><?php _e('Customize appearance settings if desired', 'aiwinlab'); ?></li>
                        <li><?php _e('The analysis wizard is available at:', 'aiwinlab'); ?> <a href="<?php echo esc_url(get_permalink(get_option('aiwinlab_wizard_page_id'))); ?>" target="_blank"><?php echo esc_url(get_permalink(get_option('aiwinlab_wizard_page_id'))); ?></a></li>
                    </ol>
                </div>
                
                <div class="aiwinlab-help-section">
                    <h2><?php _e('Frequently Asked Questions', 'aiwinlab'); ?></h2>
                    
                    <div class="aiwinlab-faq-item">
                        <h3><?php _e('How does the AI analysis work?', 'aiwinlab'); ?></h3>
                        <p><?php _e('AI WinLab uses the Gemini AI model to analyze business information provided through the wizard interface. It identifies pain points, inefficiencies, and opportunities for AI implementation based on the specific business context.', 'aiwinlab'); ?></p>
                    </div>
                    
                    <div class="aiwinlab-faq-item">
                        <h3><?php _e('How accurate are the recommendations?', 'aiwinlab'); ?></h3>
                        <p><?php _e('The quality of recommendations depends on the detail and accuracy of the information provided during the analysis process. The system uses industry best practices and the latest AI implementation strategies to provide relevant, actionable recommendations.', 'aiwinlab'); ?></p>
                    </div>
                    
                    <div class="aiwinlab-faq-item">
                        <h3><?php _e('How can I customize the interface?', 'aiwinlab'); ?></h3>
                        <p><?php _e('You can customize the primary and secondary colors in the Appearance Settings section. For more advanced customization, you can modify the theme files or use custom CSS.', 'aiwinlab'); ?></p>
                    </div>
                </div>
                
                <div class="aiwinlab-help-section">
                    <h2><?php _e('Need More Help?', 'aiwinlab'); ?></h2>
                    <p><?php _e('For additional assistance, please contact support at support@aiwinlab.com', 'aiwinlab'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render analytics dashboard
     */
    private static function render_analytics_dashboard() {
        global $wpdb;
        
        // Get submission counts
        $total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aiwinlab_submissions");
        $total_reports = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'aiwinlab_report' AND post_status = 'publish'");
        $total_consultations = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'aiwinlab_consultation'");
        
        // Get industry distribution
        $industry_query = "
            SELECT meta_value as industry, COUNT(*) as count
            FROM {$wpdb->postmeta}
            WHERE meta_key = 'industry'
            AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'aiwinlab_report')
            GROUP BY meta_value
            ORDER BY count DESC
            LIMIT 10
        ";
        
        $industry_data = $wpdb->get_results($industry_query);
        
        // Get monthly submissions
        $monthly_query = "
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
            FROM {$wpdb->prefix}aiwinlab_submissions
            GROUP BY month
            ORDER BY month ASC
            LIMIT 12
        ";
        
        $monthly_data = $wpdb->get_results($monthly_query);
        
        ?>
        <div class="aiwinlab-analytics-overview">
            <div class="aiwinlab-analytics-card">
                <div class="card-title"><?php _e('Total Submissions', 'aiwinlab'); ?></div>
                <div class="card-value"><?php echo esc_html($total_submissions); ?></div>
            </div>
            
            <div class="aiwinlab-analytics-card">
                <div class="card-title"><?php _e('Generated Reports', 'aiwinlab'); ?></div>
                <div class="card-value"><?php echo esc_html($total_reports); ?></div>
            </div>
            
            <div class="aiwinlab-analytics-card">
                <div class="card-title"><?php _e('Consultation Requests', 'aiwinlab'); ?></div>
                <div class="card-value"><?php echo esc_html($total_consultations); ?></div>
            </div>
        </div>
        
        <div class="aiwinlab-analytics-charts">
            <div class="aiwinlab-analytics-chart">
                <h3><?php _e('Industry Distribution', 'aiwinlab'); ?></h3>
                <div id="industry-chart" style="height: 300px;"></div>
            </div>
            
            <div class="aiwinlab-analytics-chart">
                <h3><?php _e('Monthly Submissions', 'aiwinlab'); ?></h3>
                <div id="monthly-chart" style="height: 300px;"></div>
            </div>
        </div>
        
        <script>
            jQuery(document).ready(function($) {
                if (typeof Chart !== 'undefined') {
                    // Industry Distribution Chart
                    var industryCtx = document.getElementById('industry-chart').getContext('2d');
                    var industryData = <?php echo json_encode($industry_data); ?>;
                    
                    new Chart(industryCtx, {
                        type: 'horizontalBar',
                        data: {
                            labels: industryData.map(function(item) { return item.industry; }),
                            datasets: [{
                                label: '<?php _e('Number of Submissions', 'aiwinlab'); ?>',
                                data: industryData.map(function(item) { return item.count; }),
                                backgroundColor: '<?php echo esc_js(get_option('aiwinlab_primary_color', '#4a6cf7')); ?>',
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                xAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        precision: 0
                                    }
                                }]
                            }
                        }
                    });
                    
                    // Monthly Submissions Chart
                    var monthlyCtx = document.getElementById('monthly-chart').getContext('2d');
                    var monthlyData = <?php echo json_encode($monthly_data); ?>;
                    
                    new Chart(monthlyCtx, {
                        type: 'line',
                        data: {
                            labels: monthlyData.map(function(item) { return item.month; }),
                            datasets: [{
                                label: '<?php _e('Submissions', 'aiwinlab'); ?>',
                                data: monthlyData.map(function(item) { return item.count; }),
                                borderColor: '<?php echo esc_js(get_option('aiwinlab_secondary_color', '#8e44ad')); ?>',
                                backgroundColor: 'rgba(142, 68, 173, 0.1)',
                                borderWidth: 2,
                                pointRadius: 4,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    precision: 0
                                }
                            }
                        }
                    });
                }
            });
        </script>
        <?php
    }
    
    /**
     * Admin notices
     */
    public static function admin_notices() {
        // Check if API key is set
        if (current_user_can('manage_options') && empty(get_option('aiwinlab_gemini_api_key'))) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <?php 
                    printf(
                        __('AI WinLab requires a Gemini API key to function. Please <a href="%s">set your API key</a>.', 'aiwinlab'), 
                        admin_url('admin.php?page=aiwinlab')
                    ); 
                    ?>
                </p>
            </div>
            <?php
        }
        
        // First-time activation notice
        if (current_user_can('manage_options') && get_option('aiwinlab_activated')) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php _e('AI WinLab has been activated! Get started by configuring your settings and adding the analysis wizard to your site.', 'aiwinlab'); ?>
                </p>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=aiwinlab'); ?>" class="button button-primary">
                        <?php _e('Configure AI WinLab', 'aiwinlab'); ?>
                    </a>
                    <a href="<?php echo esc_url(get_permalink(get_option('aiwinlab_wizard_page_id'))); ?>" class="button" target="_blank">
                        <?php _e('View Analysis Wizard', 'aiwinlab'); ?>
                    </a>
                </p>
            </div>
            <?php
            
            // Delete the activation flag
            delete_option('aiwinlab_activated');
        }
    }
}

// Initialize setup
AI_WinLab_Setup::init();