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

			add_action('admin_menu', array( $this, 'my_plugin_menu' ) );

		}

		function my_plugin_menu() {

			add_options_page( __( 'WC Emails', WCEmails_TEXT_DOMAIN ), 'wcemails', 'manage_options', 'wcemails-settings', array( $this, 'wcemails_settings_callback' ));

		}

		function wcemails_settings_callback(){

			?>
			<div class="wrap">
				<h2><?php _e( 'Woocommerce Custom Emails Settings', WCEmails_TEXT_DOMAIN ); ?></h2>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><?php _e( 'Title', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_title" id="wcemails_title" type="text" value="" placeholder="<?php _e( 'Title', WCEmails_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Description', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_description" id="wcemails_description" type="text" value="" placeholder="<?php _e( 'Description', WCEmails_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Heading', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_heading" id="wcemails_heading" type="text" value="" placeholder="<?php _e( 'Heading', WCEmails_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Hook Or Action Name', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_hook" id="wcemails_hook" type="text" value="" placeholder="<?php _e( 'Hook Or Action Name', WCEmails_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Template HTML File Name', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_html_template" id="wcemails_html_template" type="text" value="" placeholder="<?php _e( 'Template HTML File Name', WCEmails_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Template Plain File Name', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_plain_template" id="wcemails_plain_template" type="text" value="" placeholder="<?php _e( 'Template Plain File Name', WCEmails_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					</tbody>
				</table>
				<p class="submit">
					<input type="button" name="wcemails_submit" id="wcemails_submit" class="button button-primary" value="Save Changes">
				</p>
			</div>
			<?php

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
WCEmails_Admin();