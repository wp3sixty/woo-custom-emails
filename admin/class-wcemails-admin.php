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

			add_action('admin_menu', array( $this, 'wcemails_settings_menu' ) );

			add_action( 'wp_ajax_wcemails_save_email_details', array( $this, 'wcemails_save_email_details' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'wcemails_admin_enqueue_scripts' ) );

		}

		function wcemails_settings_menu() {

			add_options_page( __( 'WC Emails', WCEmails_TEXT_DOMAIN ), 'wcemails', 'manage_options', 'wcemails-settings', array( $this, 'wcemails_settings_callback' ));

		}

		function wcemails_admin_enqueue_scripts() {
			wp_register_script( 'wcemails_admin', WCEmails_PLUGIN_URL . 'assets/js/wcemails-admin-scripts.js', array( 'jquery' ) );
		}

		function wcemails_settings_callback(){

			wp_enqueue_script( 'wcemails_admin' );

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
			<div class="wrap">
				<h2><?php _e( 'List', WCEmails_TEXT_DOMAIN ); ?></h2>
				<table class="form-table">
					<tr>
						<th><?php _e( 'Title', WCEmails_TEXT_DOMAIN ); ?></th>
						<th><?php _e( 'Description', WCEmails_TEXT_DOMAIN ); ?></th>
						<th><?php _e( 'Heading', WCEmails_TEXT_DOMAIN ); ?></th>
						<th><?php _e( 'Hook', WCEmails_TEXT_DOMAIN ); ?></th>
						<th><?php _e( 'HTML Template', WCEmails_TEXT_DOMAIN ); ?></th>
						<th><?php _e( 'Plain Template', WCEmails_TEXT_DOMAIN ); ?></th>
						<th><?php _e( 'Action', WCEmails_TEXT_DOMAIN ); ?></th>
					</tr>
					<?php
					$wcemails_email_details = get_option( 'wcemails_email_details', array() );
					if( ! empty( $wcemails_email_details ) ) {
						foreach( $wcemails_email_details as $key=>$details ) {
							?>
							<tr>
								<td><?php echo $details['title']; ?></td>
								<td><?php echo $details['description']; ?></td>
								<td><?php echo $details['heading']; ?></td>
								<td><?php echo $details['hook']; ?></td>
								<td><?php echo $details['html_template']; ?></td>
								<td><?php echo $details['plain_template']; ?></td>
								<td>
									<a href="#"><?php _e( 'Edit', WCEmails_TEXT_DOMAIN ); ?></a>
									<a href="#"><?php _e( 'Delete', WCEmails_TEXT_DOMAIN ); ?></a>
								</td>
							</tr>
							<?php
						}
					}
					?>
				</table>
			</div>
			<?php

		}

		function wcemails_save_email_details() {

			$title = filter_input( INPUT_POST, 'title',FILTER_SANITIZE_STRING );
			$description = filter_input( INPUT_POST, 'description',FILTER_SANITIZE_STRING );
			$heading = filter_input( INPUT_POST, 'heading',FILTER_SANITIZE_STRING );
			$hook = filter_input( INPUT_POST, 'hook',FILTER_SANITIZE_STRING );
			$html_template = filter_input( INPUT_POST, 'html_template',FILTER_SANITIZE_STRING );
			$plain_template = filter_input( INPUT_POST, 'plain_template',FILTER_SANITIZE_STRING );

			$wcemails_email_details = get_option( 'wcemails_email_details', array() );

			$data = array(
				'title' => $title,
				'description' => $description,
				'heading' => $heading,
				'hook' => $hook,
				'html_template' => $html_template,
				'plain_template' => $plain_template,
			);

			array_push( $wcemails_email_details, $data );

			update_option( 'wcemails_email_details', $wcemails_email_details );

			echo '1';

			die();
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
