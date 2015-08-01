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

			add_action( 'admin_init', array( $this, 'wcemails_email_actions_details' ) );

			add_filter( 'woocommerce_email_classes', array( $this, 'wcemails_custom_woocommerce_emails' ) );

			add_filter( 'woocommerce_resend_order_emails_available', array( $this, 'wcemails_change_action_emails' ) );

		}

		function wcemails_settings_menu() {

			add_options_page( __( 'WC Emails', WCEmails_TEXT_DOMAIN ), 'WC Emails', 'manage_options', 'wcemails-settings', array( $this, 'wcemails_settings_callback' ));

		}

		function wcemails_settings_callback(){

			?>
			<div class="wrap">
				<h2><?php _e( 'Woocommerce Custom Emails Settings', WCEmails_TEXT_DOMAIN ); ?></h2>
				<?php
				if ( ! isset ( $_REQUEST[ 'type' ] ) ) {
					$type = 'today';
				} else {
					$type = $_REQUEST[ 'type' ];
				}
				$all_types = array ( 'add-email', 'view-email' );
				if ( ! in_array ( $type, $all_types ) ) {
					$type = 'add-email';
				}
				?>
				<ul class="subsubsub">
					<li class="today"><a class ="<?php echo ($type == 'add-email') ? 'current' : ''; ?>" href="<?php echo add_query_arg ( array ( 'type' => 'add-email' ), admin_url ( 'admin.php?page=wcemails-settings' ) ); ?>"><?php _e( 'Add Custom Emails', WCEmails_TEXT_DOMAIN ); ?></a> |</li>
					<li class="today"><a class ="<?php echo ($type == 'view-email') ? 'current' : ''; ?>" href="<?php echo add_query_arg ( array ( 'type' => 'view-email' ), admin_url ( 'admin.php?page=wcemails-settings' ) ); ?>"><?php _e( 'View Your Custom Emails', WCEmails_TEXT_DOMAIN ); ?></a></li>
				</ul>
				<?php $this->wcemails_render_sections( $type ); ?>
			</div>
			<?php

		}

		function wcemails_render_sections( $type ) {

			if( $type == 'add-email' ) {
				$this->wcemails_render_add_email_section();
			} else if( $type == 'view-email' ) {
				$this->wcemails_render_view_email_section();
			} else {
				$this->wcemails_render_add_email_section();
			}

		}

		function wcemails_render_add_email_section() {

			$wcemails_detail = array();
			if( isset( $_REQUEST['wcemails_edit'] ) ) {
				$wcemails_email_details = get_option( 'wcemails_email_details', array() );
				if( ! empty( $wcemails_email_details ) ) {
					foreach ( $wcemails_email_details as $key => $details ) {
						if( $_REQUEST['wcemails_edit'] == $key ) {
							$wcemails_detail = $details;
						}
					}
				}
			}

			?>
			<form method="post" action="">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><?php _e( 'Title', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_title" id="wcemails_title" type="text" value="<?php echo isset( $wcemails_detail['title'] ) ? $wcemails_detail['title'] : ''; ?>" placeholder="<?php _e( 'Title', WCEmails_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Description', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_description" id="wcemails_description" type="text" value="<?php echo isset( $wcemails_detail['description'] ) ? $wcemails_detail['description'] : ''; ?>" placeholder="<?php _e( 'Description', WCEmails_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Heading', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_heading" id="wcemails_heading" type="text" value="<?php echo isset( $wcemails_detail['heading'] ) ? $wcemails_detail['heading'] : ''; ?>" placeholder="<?php _e( 'Heading', WCEmails_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Hook Or Action Name', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_hook" id="wcemails_hook" type="text" value="<?php echo isset( $wcemails_detail['hook'] ) ? $wcemails_detail['hook'] : ''; ?>" placeholder="<?php _e( 'Hook Or Action Name', WCEmails_TEXT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Template', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<?php
							$settings = array(
								'textarea_name' => 'wcemails_template',
							);
							wp_editor( html_entity_decode( isset( $wcemails_detail['template'] ) ? $wcemails_detail['template'] : '' ), 'ezway_custom_email_new_order', $settings );
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Put It In Order Actions?', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_order_action" id="wcemails_order_action" type="checkbox" <?php echo isset( $wcemails_detail['order_action'] ) && $wcemails_detail['order_action'] == 'on' ? 'checked="checked"' : ''; ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Enable?', WCEmails_TEXT_DOMAIN ); ?></th>
						<td>
							<input name="wcemails_enable" id="wcemails_enable" type="checkbox" <?php echo isset( $wcemails_detail['enable'] ) && $wcemails_detail['enable'] == 'on' ? 'checked="checked"' : ''; ?> />
						</td>
					</tr>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" name="wcemails_submit" id="wcemails_submit" class="button button-primary" value="Save Changes">
				</p>
				<?php
				if( isset( $_REQUEST['wcemails_edit'] ) ) {
					?>
					<input type="hidden" name="wcemails_update" id="wcemails_update" value="<?php echo $_REQUEST['wcemails_edit']; ?>" />
					<?php
				}
				?>
			</form>
			<?php

		}

		function wcemails_render_view_email_section() {

			?>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Title', WCEmails_TEXT_DOMAIN ); ?></th>
					<th><?php _e( 'Description', WCEmails_TEXT_DOMAIN ); ?></th>
					<th><?php _e( 'Heading', WCEmails_TEXT_DOMAIN ); ?></th>
					<th><?php _e( 'Hook', WCEmails_TEXT_DOMAIN ); ?></th>
					<th><?php _e( 'Order Action', WCEmails_TEXT_DOMAIN ); ?></th>
					<th><?php _e( 'Enable', WCEmails_TEXT_DOMAIN ); ?></th>
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
							<td><?php echo $details['order_action'] == 'on' ? 'Yes' : 'No'; ?></td>
							<td><?php echo $details['enable'] == 'on' ? 'Yes' : 'No'; ?></td>
							<td>
								<a href="<?php echo add_query_arg ( array ( 'type' => 'add-email', 'wcemails_edit' => $key ), admin_url ( 'admin.php?page=wcemails-settings' ) ); ?>" data-key="<?php echo $key; ?>"><?php _e( 'Edit', WCEmails_TEXT_DOMAIN ); ?></a>
								<a href="<?php echo add_query_arg ( array ( 'type' => 'view-email', 'wcemails_delete' => $key ), admin_url ( 'admin.php?page=wcemails-settings' ) ); ?>" class="wcemails_delete" data-key="<?php echo $key; ?>"><?php _e( 'Delete', WCEmails_TEXT_DOMAIN ); ?></a>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</table>
			<?php

		}

		function wcemails_email_actions_details() {

			if( isset( $_POST['wcemails_submit'] ) ) {

				$title = filter_input( INPUT_POST, 'wcemails_title',FILTER_SANITIZE_STRING );
				$description = filter_input( INPUT_POST, 'wcemails_description',FILTER_SANITIZE_STRING );
				$heading = filter_input( INPUT_POST, 'wcemails_heading',FILTER_SANITIZE_STRING );
				$hook = filter_input( INPUT_POST, 'wcemails_hook',FILTER_SANITIZE_STRING );
				$template = isset( $_POST['wcemails_template'] ) ? $_POST['wcemails_template'] : '';
				$order_action = filter_input( INPUT_POST, 'wcemails_order_action',FILTER_SANITIZE_STRING );
				$order_action = empty( $order_action ) ? 'off' : $order_action;
				$enable = filter_input( INPUT_POST, 'wcemails_enable',FILTER_SANITIZE_STRING );
				$enable = empty( $enable ) ? 'off' : $enable;

				$wcemails_email_details = get_option( 'wcemails_email_details', array() );

				$data = array(
					'title' => $title,
					'description' => $description,
					'heading' => $heading,
					'hook' => $hook,
					'template' => $template,
					'order_action' => $order_action,
					'enable' => $enable,
				);

				if( isset( $_POST['wcemails_update'] ) && ! empty( $_POST['wcemails_update'] ) ) {
					if( ! empty( $wcemails_email_details ) ) {
						foreach ( $wcemails_email_details as $key => $details ) {
							if( $key == $_POST['wcemails_update'] ) {
								$wcemails_email_details[$key] = $data;
							}
						}
					}
				} else {
					array_push( $wcemails_email_details, $data );
				}

				update_option( 'wcemails_email_details', $wcemails_email_details );

				add_settings_error( 'wcemails-settings', 'error_code', $title.' is saved and if you have enabled it then you can see it in Woocommerce Email Settings Now', 'success' );

			} else if( isset( $_REQUEST['wcemails_delete'] ) && ! empty( $_REQUEST['wcemails_delete'] ) ) {

				$wcemails_email_details = get_option( 'wcemails_email_details', array() );

				$delete_key = $_POST['delete'];

				if( ! empty( $wcemails_email_details ) ) {
					foreach ( $wcemails_email_details as $key => $details ) {
						if( $key == $delete_key ) {
							unset( $wcemails_email_details[$key] );
						}
					}
				}

				update_option( 'wcemails_email_details', $wcemails_email_details );

				add_settings_error( 'wcemails-settings', 'error_code', 'Email settings deleted!', 'success' );

			}

		}

		function wcemails_custom_woocommerce_emails( $email_classes ) {

			$wcemails_email_details = get_option( 'wcemails_email_details', array() );

			if( ! empty( $wcemails_email_details ) ) {

				foreach ( $wcemails_email_details as $key => $details ) {

					$enable = $details['enable'];

					if( $enable == 'on' ) {

						$title          = $details['title'];
						$description    = $details['description'];
						$heading        = $details['heading'];
						$hook           = $details['hook'];
						$html_template  = $details['html_template'];
						$plain_template = $details['plain_template'];

						$title = str_replace( ' ', '_', $title );

						eval("
						if ( ! class_exists( 'WCustom_Emails_".$title."_Email' ) ) {

							class WCustom_Emails_".$title."_Email extends WC_Email {

								protected static \$_instance = null;

								public static function instance() {
									if ( is_null( self::\$_instance ) ) {
										self::\$_instance = new self();
									}
									return self::\$_instance;
								}

								public function __construct() {

									\$this->id          = 'wcustom_emails_".$title."';
									\$this->title       = __( '".str_replace( '_', ' ', $title )."', WCEmails_TEXT_DOMAIN );
									\$this->description = __( '".$description."', WCEmails_TEXT_DOMAIN );

									\$this->heading = __( '".$heading."', WCEmails_TEXT_DOMAIN );
									\$this->subject = __( '', WCEmails_TEXT_DOMAIN );

									\$this->template_html  = 'emails/".$html_template."';
									\$this->template_plain = 'emails/plain/".$plain_template."';

									// Triggers for this email
									add_action( '".$hook."', array( \$this, 'trigger' ) );

									// Call parent constructor
									parent::__construct();

									// Other settings
									\$this->recipient = \$this->get_option( 'recipient' );

									if ( ! \$this->recipient ) {
										\$this->recipient = get_option( 'admin_email' );
									}
								}

								function trigger( \$order_id ) {

									if ( \$order_id ) {
										\$this->object 		= wc_get_order( \$order_id );

										\$this->find['order-date']      = '{order_date}';
										\$this->find['order-number']    = '{order_number}';

										\$this->replace['order-date']   = date_i18n( wc_date_format(), strtotime( \$this->object->order_date ) );
										\$this->replace['order-number'] = \$this->object->get_order_number();
									}

									if ( ! \$this->is_enabled() || ! \$this->get_recipient() ) {
										return;
									}

									\$this->send( \$this->get_recipient(), \$this->get_subject(), \$this->get_content(), \$this->get_headers(), \$this->get_attachments() );
								}

								function get_content_html() {
									ob_start();
									wc_get_template( \$this->template_html, array(
										'order' 		=> \$this->object,
										'email_heading' => \$this->get_heading(),
										'sent_to_admin' => true,
										'plain_text'    => false
									) );
									return ob_get_clean();
								}

								function get_content_plain() {
									ob_start();
									wc_get_template( \$this->template_plain, array(
										'order' 		=> \$this->object,
										'email_heading' => \$this->get_heading(),
										'sent_to_admin' => true,
										'plain_text'    => true
									) );
									return ob_get_clean();
								}

								function init_form_fields() {
									\$this->form_fields = array(
										'enabled' => array(
											'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
											'type' 			=> 'checkbox',
											'label' 		=> __( 'Enable this email notification', 'woocommerce' ),
											'default' 		=> 'yes'
										),
										'recipient' => array(
											'title' 		=> __( 'Recipient(s)', 'woocommerce' ),
											'type' 			=> 'text',
											'description' 	=> sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'woocommerce' ), esc_attr( get_option('admin_email') ) ),
											'placeholder' 	=> '',
											'default' 		=> ''
										),
										'subject' => array(
											'title' 		=> __( 'Subject', 'woocommerce' ),
											'type' 			=> 'text',
											'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), \$this->subject ),
											'placeholder' 	=> '',
											'default' 		=> ''
										),
										'heading' => array(
											'title' 		=> __( 'Email Heading', 'woocommerce' ),
											'type' 			=> 'text',
											'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce' ), \$this->heading ),
											'placeholder' 	=> '',
											'default' 		=> ''
										),
										'email_type' => array(
											'title' 		=> __( 'Email type', 'woocommerce' ),
											'type' 			=> 'select',
											'description' 	=> __( 'Choose which format of email to send.', 'woocommerce' ),
											'default' 		=> 'html',
											'class'			=> 'email_type wc-enhanced-select',
											'options'		=> \$this->get_email_type_options()
										)
									);
								}
							}
						}
						");

						$email_class = 'WCustom_Emails_'.$title.'_Email';

						$email_classes['WCustom_Emails_'.$title.'_Email'] = new $email_class();

					}

				}

			}

			return $email_classes;

		}

		function wcemails_change_action_emails( $emails ) {

			$wcemails_email_details = get_option( 'wcemails_email_details', array() );

			if( ! empty( $wcemails_email_details ) ) {

				foreach ( $wcemails_email_details as $key => $details ) {

					$enable = $details['enable'];
					$order_action = $details['order_action'];

					if( $enable == 'on' && $order_action == 'on' ) {

						$title          = $details['title'];
						$title = str_replace( ' ', '_', $title );

						array_push( $emails, 'wcustom_emails_'.$title );

					}

				}
			}

			return $emails;

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
