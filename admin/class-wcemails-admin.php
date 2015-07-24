<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists( 'WCEmails_Admin' ) ) {

	/**
	 * Admin WooCommerce Custom Emails Class
	 *
	 * @class WCE_Admin
	 * @version	0.1
	 */
	class WCEmails_Admin {

		/**
		 * @var WCEmails_Admin The single instance of the class
		 * @since 0.1
		 */
		protected static $_instance = null;

		/**
		 * Main WCEmails_Admin Instance
		 *
		 * Ensures only one instance of WCEmails_Admin is loaded or can be loaded.
		 *
		 * @since 0.1
		 * @static
		 * @return WCEmails_Admin - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
		}

	}

}

/**
 * Returns the main instance of WCEmails_Admin to prevent the need to use globals.
 *
 * @since  0.1
 * @return WCEmails_Admin
 */
function WCEmails_Admin() {
	return WCEmails_Admin::instance();
}