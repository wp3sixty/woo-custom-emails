<?php

if ( ! class_exists( 'WCEmails_Instance' ) && class_exists( 'WC_Email' ) ) {

	class WCEmails_Instance extends WC_Email {
		/**
		 * Strings to find in subjects/headings.
		 * @var array
		 */
		public $find = array();

		/**
		 * Strings to replace in subjects/headings.
		 * @var array
		 */
		public $replace = array();

		/**
		 * @param $id
		 * @param $title
		 * @param $description
		 * @param $subject
		 * @param $heading
		 * @param $from_status
		 * @param $to_status
		 * @param $template
		 */
		function __construct( $id, $title, $description, $subject, $recipients, $heading, $from_status, $to_status, $send_customer, $template ) {

			$this->id          = $id;
			$this->title       = __( $title, 'woocommerce' );
			$this->description = __( $description, 'woocommerce' );

			$this->heading = __( $heading, 'woocommerce' );
			$this->subject = __( $subject, 'woocommerce' );

			$this->custom_template = $template;
			$this->from_status     = $from_status;
			$this->to_status       = $to_status;
			$this->send_customer   = $send_customer;

			$this->add_actions();

			// Call parent constructor
			parent::__construct();

			// Other settings
			$this->recipient = ! empty( $recipients ) ? $recipients : $this->get_option( 'recipient' );

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
			// checkbox of send to customer is checked or not.
			$send_to_customer = ('on' == $this->send_customer);

			if ( $order_id ) {
				$this->object = wc_get_order( $order_id );
				if ( $send_to_customer ) {
					$this->bcc = $this->recipient;
					$this->recipient = $this->object->get_billing_email();
				} else {
					$recipients = explode( ',', $this->recipient );
					array_push( $recipients, $this->object->get_billing_email() );
					$this->recipient = implode( ',', $recipients );
				}

				$order_date = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );
				$order_number = $this->object->get_order_number();

				/**
				 * WooCommerce =< 3.2.X
				 */
				$this->find['order-date']   = '{order_date}';
				$this->replace['order-date']   = $order_date;

				$this->find['order-number'] = '{order_number}';
				$this->replace['order-number'] = $order_number;

				/**
				 * WooCommerce > 3.2.X
				 */
				$this->placeholders['{order_date}']   = $order_date;
				$this->placeholders['{order_number}'] = $order_number;

			}

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$this->convert_template();

			// if send to customer is selected add recipients to BCC
			if ( $send_to_customer ) {
				add_filter( 'woocommerce_email_headers', array( $this, 'add_bcc_to_custom_email' ), 10, 3 );
			}
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			if ( $send_to_customer ) {
				remove_filter( 'woocommerce_email_headers', array( $this, 'add_bcc_to_custom_email' ), 10 );
			}
		}

		/**
		 * get_content_html function.
		 *
		 * @access public
		 * @return string
		 */
		function get_content_html() {
			ob_start();

			$html = $this->format_string( $this->custom_template );

			do_action( 'woocommerce_email_header', $this->get_heading(), $this );

			echo apply_filters( 'the_content', $html );

			do_action( 'woocommerce_email_footer', $this );

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

			$html = $this->format_string( $this->custom_template );

			do_action( 'woocommerce_email_header', $this->get_heading(), $this );

			echo apply_filters( 'the_content', $html );

			do_action( 'woocommerce_email_footer', $this );

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
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'woocommerce' ),
					'default' => 'yes',
				),
				'recipient'  => array(
					'title'       => __( 'Recipient(s)', 'woocommerce' ),
					'type'        => 'text',
					'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'woocommerce' ), esc_attr( get_option( 'admin_email' ) ) ),
					'placeholder' => '',
					'default'     => '',
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'woocommerce' ),
					'type'        => 'text',
					'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email Heading', 'woocommerce' ),
					'type'        => 'text',
					'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce' ), $this->heading ),
					'placeholder' => '',
					'default'     => '',
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type',
					'options'     => array(
						'plain'     => __( 'Plain text', 'woocommerce' ),
						'html'      => __( 'HTML', 'woocommerce' ),
						'multipart' => __( 'Multipart', 'woocommerce' ),
					),
				),
			);
		}

		function change_order_status_trigger( $order_id, $old_status, $new_status ) {
			$from_status = $this->from_status;
			$to_status   = $this->to_status;
			if ( ! empty( $from_status ) && ! empty( $to_status ) && in_array( $old_status, $from_status ) && in_array( $new_status, $to_status ) ) {
				$this->trigger( $order_id );
			}
		}

		function add_actions() {
			$from_status = $this->from_status;
			$to_status   = $this->to_status;
			if ( ! empty( $from_status ) && ! empty( $to_status ) ) {
				foreach ( $from_status as $k => $status ) {
					add_action( 'woocommerce_order_status_' . $status . '_to_' . $to_status[ $k ] . '_notification', array( $this, 'trigger' ) );
				}
			}
		}

		function convert_template() {

			$this->placeholders['{woocommerce_email_order_meta}']    = $this->woocommerce_email_order_meta();
			$this->placeholders['{order_billing_name}']    = $this->object->get_billing_first_name() . ' ' . $this->object->get_billing_last_name();
			$this->placeholders['{email_order_items_table}']    = wc_get_email_order_items( $this->object );
			$this->placeholders['{email_order_total_footer}']    = $this->email_order_total_footer();
			$this->placeholders['{order_billing_email}']    = $this->object->get_billing_email();
			$this->placeholders['{order_billing_phone}']    = $this->object->get_billing_phone();
			$this->placeholders['{email_addresses}']    = $this->get_email_addresses();
			$this->placeholders['{site_title}']    = get_bloginfo('name');

			// For old woocommerce use find and replace methods
			foreach ( $this->placeholders as $find => $replace ) {
				$this->find[]    = $find;
				$this->replace[] = $replace;
			}

			$this->placeholders = apply_filters( 'wcemails_find_placeholders', $this->placeholders, $this->object );

			// Legacy filters
			$this->find      = apply_filters( 'wcemails_find_placeholders', $this->find, $this->object );
			$this->replace   = apply_filters( 'wcemails_replace_placeholders', $this->replace, $this->object );

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
					$i ++;
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

		function add_bcc_to_custom_email( $headers, $email_id, $order ) {
			if ( $this->id != $email_id || empty( $this->bcc ) ) {
				return $headers;
			}
			if ( ! is_array( $headers ) ) {
				$headers = array( $headers );
			}
			$headers[] = 'Bcc: '.$this->bcc;
			return $headers;
		}

	}

}
