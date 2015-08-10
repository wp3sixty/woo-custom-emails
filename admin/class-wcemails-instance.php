<?php

/**
 * Created by PhpStorm.
 * User: kaklo
 * Date: 10/8/15
 * Time: 12:13 PM
 */


if ( ! class_exists( 'WCEmails_Instance' ) ) {

	class WCEmails_Instance extends WC_Email {

		/**
		 * @param $id
		 * @param $title
		 * @param $description
		 * @param $heading
		 * @param $hook
		 * @param $template
		 */
		function __construct( $id, $title, $description, $heading, $hook, $template ) {

			$this->id 				= $id;
			$this->title 			= __( $title, 'woocommerce' );
			$this->description		= __( $description, 'woocommerce' );

			$this->heading 			= __( $heading, 'woocommerce' );
			$this->subject      	= __( '[{site_title}] New customer order ({order_number}) - {order_date}', 'woocommerce' );

			$this->custom_template  = $template;

			if ( ! empty( $hooks ) ) {
				add_action( $hooks, array( $this, 'trigger' ) );
			}

			// Call parent constructor
			parent::__construct();

			// Other settings
			$this->recipient = $this->get_option( 'recipient' );

			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}

		}

		/**
		 * trigger function.
		 *
		 * @access public
		 * @return void
		 */
		function trigger( $order_id ) {

			if ( $order_id ) {
				$this->object 		= wc_get_order( $order_id );

				$this->find['order-date']      = '{order_date}';
				$this->find['order-number']    = '{order_number}';

				$this->replace['order-date']   = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );
				$this->replace['order-number'] = $this->object->get_order_number();
			}

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$this->convert_template();

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * get_content_html function.
		 *
		 * @access public
		 * @return string
		 */
		function get_content_html() {
			ob_start();
			echo str_replace( $this->find, $this->replace, $this->custom_template );
			return ob_get_clean();
		}

		/**
		 * get_content_plain function.
		 *
		 * @access public
		 * @return string
		 */
		function get_content_plain() {
			ob_start();
			echo str_replace( $this->find, $this->replace, $this->custom_template );
			return ob_get_clean();
		}

		/**
		 * Initialise Settings Form Fields
		 *
		 * @access public
		 * @return void
		 */
		function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable this email notification', 'woocommerce' ),
					'default' 		=> 'yes',
				),
				'recipient' => array(
					'title' 		=> __( 'Recipient(s)', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'woocommerce' ), esc_attr( get_option( 'admin_email' ) ) ),
					'placeholder' 	=> '',
					'default' 		=> '',
				),
				'subject' => array(
					'title' 		=> __( 'Subject', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), $this->subject ),
					'placeholder' 	=> '',
					'default' 		=> '',
				),
				'heading' => array(
					'title' 		=> __( 'Email Heading', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce' ), $this->heading ),
					'placeholder' 	=> '',
					'default' 		=> '',
				),
				'email_type' => array(
					'title' 		=> __( 'Email type', 'woocommerce' ),
					'type' 			=> 'select',
					'description' 	=> __( 'Choose which format of email to send.', 'woocommerce' ),
					'default' 		=> 'html',
					'class'			=> 'email_type',
					'options'		=> array(
						'plain'		 	=> __( 'Plain text', 'woocommerce' ),
						'html' 			=> __( 'HTML', 'woocommerce' ),
						'multipart' 	=> __( 'Multipart', 'woocommerce' ),
					),
				),
			);
		}

		function convert_template() {

			$this->find[]    = '{woocommerce_email_order_meta}';
			$this->replace[] = $this->woocommerce_email_order_meta();

			$this->find[]    = '{order_billing_name}';
			$this->replace[] = $this->object->billing_first_name . ' ' . $this->object->billing_last_name;

			$this->find[]    = '{email_order_items_table}';
			$this->replace[] = $this->object->email_order_items_table( false, true );

			$this->find[]    = '{email_order_total_footer}';
			$this->replace[] = $this->email_order_total_footer();

			$this->find[]    = '{order_billing_email}';
			$this->replace[] = $this->object->billing_email;

			$this->find[]    = '{order_billing_phone}';
			$this->replace[] = $this->object->billing_phone;

			$this->find[]    = '{email_addresses}';
			$this->replace[] = $this->get_email_addresses();

		}

		function woocommerce_email_order_meta() {
			ob_start();
			do_action( 'woocommerce_email_order_meta', $this->object, true );
			return ob_get_clean();
		}

		function email_order_total_footer() {
			ob_start();
			if ( $totals = $this->object->get_order_item_totals() ) {
				$i = 0;
				foreach ( $totals as $total ) {
					$i++;
					?>
					<tr>
					<th scope='row' colspan='2'
						style='text-align:left; border: 1px solid #eee; <?php echo 1 == $i ? 'border-top-width: 4px;' : ''; ?>'><?php echo $total['label']; ?></th>
					<td style='text-align:left; border: 1px solid #eee; <?php echo 1 == $i ? 'border-top-width: 4px;' : ''; ?>'><?php echo $total['value']; ?></td>
					</tr><?php
				}
			}
			return ob_get_clean();
		}

		function get_email_addresses() {
			ob_start();
			wc_get_template( 'emails/email-addresses.php', array( 'order' => $this->object ) );
			return ob_get_clean();
		}

	}

}
