<?php

/**
 * Plugin Name: Woo Custom Emails
 * Plugin URI: https://github.com/mehulkaklotar/woo-custom-emails
 * Description: A woocommerce add on to support customize emails
 * Version: 2.0.7
 * Author: wp3sixty
 * Author URI: http://wp3sixty.com
 * Requires at least: 4.1
 * Tested up to: 4.5
 *
 * Text Domain: woo-custom-emails
 *
 * @package Woo_Custom_Emails
 * @category Core
 * @author mehulkaklotar
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Woo_Custom_Emails' ) ) {

	/**
	 * Main Woo Custom Emails Class
	 *
	 * @class Woo_Custom_Emails
	 * @version	2.0.3
	 */
	final class Woo_Custom_Emails {

		/**
		 * @var string
		 */
		public $version = '2.0.7';
		/**
		 * @var Woo_Custom_Emails The single instance of the class
		 * @since 2.1
		 */
		protected static $_instance = null;

		/**
		 * Main Woo_Custom_Emails Instance
		 *
		 * Ensures only one instance of WooCommerce_Custom_Emails is loaded or can be loaded.
		 *
		 * @since 0.1
		 * @static
		 * @see woo_custom_emails()
		 * @return Woo_Custom_Emails - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 * @since 0.1
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woo-custom-emails' ), '2.0.7' );
		}
		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 2.1
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woo-custom-emails' ), '2.0.7' );
		}

		/**
		 * WooCommerce_Custom_Emails Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->init_hooks();
		}

		/**
		 * Hook into actions and filters
		 * @since  0.1
		 */
		private function init_hooks() {
			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * Define WCE Constants
		 */
		private function define_constants() {
			$this->define( 'WCEmails_PLUGIN_FILE', __FILE__ );
			$this->define( 'WCEmails_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'WCEmails_VERSION', $this->version );
			$this->define( 'WCEmails_TEXT_DOMAIN', 'wcemails' );
			$this->define( 'WCEmails_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'WCEmails_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {
			include_once( 'admin/class-wcemails-admin.php' );
		}

		/**
		 * Init WooCommerce when WordPress Initialises.
		 */
		public function init() {
			$this->includes();
		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

	}

	/**
	 * Returns the main instance of woo_custom_emails to prevent the need to use globals.
	 *
	 * @since  0.1
	 * @return Woo_Custom_Emails
	 */
	function woo_custom_emails() {
		return Woo_Custom_Emails::instance();
	}

	woo_custom_emails();

}



