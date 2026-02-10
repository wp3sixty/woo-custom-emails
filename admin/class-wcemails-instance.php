<?php

if ( ! class_exists( 'WCEmails_Instance' ) && class_exists( 'WC_Email' ) ) {

	class WCEmails_Instance extends WC_Email {
		/**
		 * Strings to find in subjects/headings.
		 *
		 * @var array
		 */
		public $find = array();

		/**
		 * Strings to replace in subjects/headings.
		 *
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
		public function __construct( $id, $title, $description, $subject, $recipients, $heading, $from_status, $to_status, $send_customer, $template ) {

			$this->id          = $id;
			$this->title       = $title;
			$this->description = $description;

			$this->heading = $heading;
			$this->subject = $subject;

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
		public function trigger( $order_id, $order = false ) {
			// Clean up possible leftover from previous invocations (e.g. in case of bulk updates & sends).
			// Save the original recipient so it is not mutated across multiple trigger() calls.
			$original_recipient = $this->recipient;
			$this->find         = array();
			$this->replace      = array();
			$this->bcc          = '';

			// Checkbox of send to customer is checked or not.
			$send_to_customer = ( 'on' === $this->send_customer );

			if ( $order_id ) {
				$this->object = ( $order instanceof WC_Order ) ? $order : wc_get_order( $order_id );
				if ( $send_to_customer ) {
					// Send TO customer, BCC the configured recipients (only if non-empty).
					if ( ! empty( $original_recipient ) ) {
						$this->bcc = $original_recipient;
					}
					$this->recipient = $this->object->get_billing_email();
				}
				// When send_to_customer is off, the email goes only to configured recipients.
				// The customer is NOT automatically added.

				$date_created = $this->object->get_date_created();
				$order_date   = $date_created ? $date_created->date_i18n( wc_date_format() ) : '';
				$order_number = $this->object->get_order_number();

				$this->placeholders['{order_date}']   = $order_date;
				$this->placeholders['{order_number}'] = $order_number;
			}

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				$this->recipient = $original_recipient;
				return;
			}

			$this->convert_template();

			// If send to customer is selected add configured recipients as BCC.
			if ( $send_to_customer && ! empty( $this->bcc ) ) {
				add_filter( 'woocommerce_email_headers', array( $this, 'add_bcc_to_custom_email' ), 10, 3 );
			}
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			if ( $send_to_customer && ! empty( $this->bcc ) ) {
				remove_filter( 'woocommerce_email_headers', array( $this, 'add_bcc_to_custom_email' ), 10 );
			}

			// Restore original recipient for the next trigger() call (bulk sends).
			$this->recipient = $original_recipient;
		}

		/**
		 * get_content_html function.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_html() {
			ob_start();

			$html = $this->format_string( $this->custom_template );

			do_action( 'woocommerce_email_header', $this->get_heading(), $this );

			// Use wpautop instead of the_content filter to avoid third-party plugin
			// output (social sharing, oEmbed, etc.) leaking into emails.
			echo wpautop( $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			do_action( 'woocommerce_email_footer', $this );

			return ob_get_clean();
		}

		/**
		 * get_content_plain function.
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_plain() {
			ob_start();

			$html = $this->format_string( $this->custom_template );

			do_action( 'woocommerce_email_header', $this->get_heading(), $this );

			// Strip HTML for plain text emails.
			echo wp_strip_all_tags( $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			do_action( 'woocommerce_email_footer', $this );

			return ob_get_clean();
		}

		/**
		 * Initialise Settings Form Fields
		 *
		 * @access public
		 * @return void
		 */
		public function init_form_fields() {
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
					/* translators: %s: admin email address */
					'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'woocommerce' ), esc_attr( get_option( 'admin_email' ) ) ),
					'placeholder' => '',
					'default'     => '',
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'woocommerce' ),
					'type'        => 'text',
					/* translators: %s: default email subject */
					'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), $this->subject ),
					'placeholder' => '',
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email Heading', 'woocommerce' ),
					'type'        => 'text',
					/* translators: %s: default email heading */
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

		public function change_order_status_trigger( $order_id, $old_status, $new_status ) {
			$from_status = $this->from_status;
			$to_status   = $this->to_status;
			if ( ! empty( $from_status ) && ! empty( $to_status ) && in_array( $old_status, $from_status, true ) && in_array( $new_status, $to_status, true ) ) {
				$this->trigger( $order_id );
			}
		}

		public function add_actions() {
			$from_status = $this->from_status;
			$to_status   = $this->to_status;
			if ( ! empty( $from_status ) && ! empty( $to_status ) ) {
				foreach ( $from_status as $k => $status ) {
					add_action( 'woocommerce_order_status_' . $status . '_to_' . $to_status[ $k ] . '_notification', array( $this, 'trigger' ) );
				}
			}
		}

		public function convert_template() {

			$this->placeholders['{woocommerce_email_order_meta}'] = $this->woocommerce_email_order_meta();
			$this->placeholders['{order_billing_name}']           = $this->object->get_billing_first_name() . ' ' . $this->object->get_billing_last_name();
			$this->placeholders['{email_order_items_table}']      = wc_get_email_order_items( $this->object );
			$this->placeholders['{email_order_total_footer}']     = $this->email_order_total_footer();
			$this->placeholders['{order_billing_email}']          = $this->object->get_billing_email();
			$this->placeholders['{order_billing_phone}']          = $this->object->get_billing_phone();
			$this->placeholders['{email_addresses}']              = $this->get_email_addresses();
			$this->placeholders['{site_title}']                   = get_bloginfo( 'name' );

			/**
			 * Filter custom email placeholders.
			 *
			 * @since 2.0.5
			 * @param array    $placeholders Key-value pairs of placeholder => replacement.
			 * @param WC_Order $order        The order object.
			 */
			$this->placeholders = apply_filters( 'wcemails_find_placeholders', $this->placeholders, $this->object );
		}

		public function woocommerce_email_order_meta() {
			ob_start();
			do_action( 'woocommerce_email_order_meta', $this->object, true );

			return ob_get_clean();
		}

		public function email_order_total_footer() {
			ob_start();
			$totals = $this->object->get_order_item_totals();
			if ( $totals ) {
				$i = 0;
				foreach ( $totals as $total ) {
					++$i;
					?>
					<tr>
					<th scope='row' colspan='2'
						style='text-align:left; border: 1px solid #eee; <?php echo 1 === $i ? 'border-top-width: 4px;' : ''; ?>'><?php echo wp_kses_post( $total['label'] ); ?></th>
					<td style='text-align:left; border: 1px solid #eee; <?php echo 1 === $i ? 'border-top-width: 4px;' : ''; ?>'><?php echo wp_kses_post( $total['value'] ); ?></td>
					</tr>
					<?php
				}
			}

			return ob_get_clean();
		}

		public function get_email_addresses() {
			ob_start();
			wc_get_template( 'emails/email-addresses.php', array( 'order' => $this->object ) );

			return ob_get_clean();
		}

		public function add_bcc_to_custom_email( $headers, $email_id, $order ) {
			if ( $this->id !== $email_id || empty( $this->bcc ) ) {
				return $headers;
			}
			if ( ! is_array( $headers ) ) {
				$headers = array( $headers );
			}
			$headers[] = 'Bcc: ' . $this->bcc;
			return $headers;
		}
	}

}
