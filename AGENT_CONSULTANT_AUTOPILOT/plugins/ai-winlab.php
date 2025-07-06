<?php
/**
 * Plugin Name: AI WinLab
 * Plugin URI: https://aiwinlab.com
 * Description: AI-powered business analysis tool for implementing AI agents to maximize efficiency and increase revenue.
 * Version: 1.0.0
 * Author: AI WinLab
 * Author URI: https://aiwinlab.com
 * Text Domain: aiwinlab
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AIWINLAB_VERSION', '1.0.0');
define('AIWINLAB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIWINLAB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIWINLAB_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class AI_WinLab {
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Load plugin files
        $this->load_dependencies();
        
        // Initialize plugin
        $this->init();
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core files
        require_once AIWINLAB_PLUGIN_DIR . 'inc/setup.php';
        require_once AIWINLAB_PLUGIN_DIR . 'inc/shortcodes.php';
        require_once AIWINLAB_PLUGIN_DIR . 'inc/ajax-handlers.php';
        require_once AIWINLAB_PLUGIN_DIR . 'inc/analysis-processor.php';
        
        // Admin files
        if (is_admin()) {
            require_once AIWINLAB_PLUGIN_DIR . 'admin/admin.php';
        }
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Register assets
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('admin_enqueue_scripts', array($this, 'register_admin_assets'));
        
        // Add theme compatibility
        add_action('after_setup_theme', array($this, 'theme_compatibility'));
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('aiwinlab', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Register frontend assets
     */
    public function register_assets() {
        // Styles
        wp_register_style(
            'aiwinlab-wizard-css',
            AIWINLAB_PLUGIN_URL . 'assets/css/wizard.css',
            array(),
            AIWINLAB_VERSION
        );
        
        wp_register_style(
            'aiwinlab-reports-css',
            AIWINLAB_PLUGIN_URL . 'assets/css/reports.css',
            array(),
            AIWINLAB_VERSION
        );
        
        // Scripts
        wp_register_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js',
            array(),
            '3.7.1',
            true
        );
        
        wp_register_script(
            'html2pdf-js',
            'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js',
            array(),
            '0.10.1',
            true
        );
        
        wp_register_script(
            'aiwinlab-visualizations',
            AIWINLAB_PLUGIN_URL . 'assets/js/visualizations.js',
            array('jquery', 'chart-js'),
            AIWINLAB_VERSION,
            true
        );
        
        wp_register_script(
            'aiwinlab-wizard-js',
            AIWINLAB_PLUGIN_URL . 'assets/js/wizard.js',
            array('jquery', 'chart-js', 'aiwinlab-visualizations'),
            AIWINLAB_VERSION,
            true
        );
        
        wp_register_script(
            'aiwinlab-reports-js',
            AIWINLAB_PLUGIN_URL . 'assets/js/reports.js',
            array('jquery', 'chart-js', 'aiwinlab-visualizations', 'html2pdf-js'),
            AIWINLAB_VERSION,
            true
        );
        
        // Localize scripts
        wp_localize_script('aiwinlab-wizard-js', 'aiWinLabData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aiwinlab_nonce'),
            'homeUrl' => home_url(),
            'primaryColor' => get_option('aiwinlab_primary_color', '#4a6cf7'),
            'secondaryColor' => get_option('aiwinlab_secondary_color', '#8e44ad'),
            'messages' => array(
                'analyzing' => __('Analyzing your business data...', 'aiwinlab'),
                'success' => __('Analysis complete!', 'aiwinlab'),
                'error' => __('An error occurred during analysis. Please try again.', 'aiwinlab'),
                'incomplete' => __('Please complete all required fields.', 'aiwinlab'),
            ),
        ));
    }
    
    /**
     * Register admin assets
     */
    public function register_admin_assets($hook) {
        // Only load on plugin admin pages
        if (strpos($hook, 'aiwinlab') === false) {
            return;
        }
        
        // Admin styles
        wp_enqueue_style(
            'aiwinlab-admin-css',
            AIWINLAB_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            AIWINLAB_VERSION
        );
        
        // Color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Admin scripts
        wp_enqueue_script(
            'aiwinlab-admin-js',
            AIWINLAB_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery', 'wp-color-picker'),
            AIWINLAB_VERSION,
            true
        );
    }
    
    /**
     * Ensure theme compatibility
     */
    public function theme_compatibility() {
        // Add theme support for post thumbnails if not already added
        if (!current_theme_supports('post-thumbnails')) {
            add_theme_support('post-thumbnails');
        }
    }
}

// Initialize the plugin
function aiwinlab_init() {
    AI_WinLab::get_instance();
}
add_action('plugins_loaded', 'aiwinlab_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Load setup class before activation
    require_once plugin_dir_path(__FILE__) . 'inc/setup.php';
    AI_WinLab_Setup::activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Load setup class before deactivation
    require_once plugin_dir_path(__FILE__) . 'inc/setup.php';
    AI_WinLab_Setup::deactivate();
});