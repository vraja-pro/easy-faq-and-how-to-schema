<?php
/**
 * Plugin Name: Easy FAQ and HowTo Schema
 * Plugin URI: https://github.com/vraja-pro/easy-faq-howto-schema
 * Description: Adds FAQ and HowTo structured data via metabox, shortcode, and Yoast SEO integration
 * Version: 1.0.0
 * Author: Vraja Das
 * Author URI: https://github.com/vraja-pro
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: easy-faq-howto-schema
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'EASY_FAQ_HOWTO_VERSION', '1.0.0' );
define( 'EASY_FAQ_HOWTO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EASY_FAQ_HOWTO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
class Easy_FAQ_HowTo_Schema {

    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin
     */
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Return an instance of this class.
     *
     * @return object A single instance of this class.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize plugin functionality
     */
    public function init() {
        // Load plugin text domain
        load_plugin_textdomain( 'easy-faq-howto-schema', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        // Include required files
        require_once EASY_FAQ_HOWTO_PLUGIN_DIR . 'includes/class-metabox.php';
        require_once EASY_FAQ_HOWTO_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once EASY_FAQ_HOWTO_PLUGIN_DIR . 'includes/class-yoast-integration.php';

        // Initialize components
        Easy_FAQ_HowTo_Metabox::get_instance();
        Easy_FAQ_HowTo_Shortcodes::get_instance();
        Easy_FAQ_HowTo_Yoast_Integration::get_instance();
    }
}

// Initialize the plugin
Easy_FAQ_HowTo_Schema::get_instance();
