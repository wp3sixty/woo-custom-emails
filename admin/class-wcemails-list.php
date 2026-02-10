<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WCEmails_List' ) ) {

	class WCEmails_List extends WP_List_Table {

		public function __construct() {

			// Set parent defaults.
			parent::__construct( array(
				'singular' => 'WCEmail',     //singular name of the listed records
				'plural'   => 'WCEmails',    //plural name of the listed records
				'ajax'     => false,        //does this table support ajax?
			) );

		}

		public function get_columns() {
			$columns = array(
				'wcemails_title'        => __( 'Title', 'woo-custom-emails' ),
				'wcemails_description'  => __( 'Description', 'woo-custom-emails' ),
				'wcemails_subject'      => __( 'Subject', 'woo-custom-emails' ),
				'wcemails_heading'      => __( 'Heading', 'woo-custom-emails' ),
				'wcemails_order_action' => __( 'Order Action', 'woo-custom-emails' ),
				'wcemails_enable'       => __( 'Enable', 'woo-custom-emails' ),
			);
			return $columns;
		}

	public function column_wcemails_title( $item ) {
		$edit_url = add_query_arg( array( 'type' => 'add-email', 'wcemails_edit' => $item['ID'] ), admin_url( 'admin.php?page=wcemails-settings' ) );
		$delete_url = wp_nonce_url(
			add_query_arg( array( 'type' => 'view-email', 'wcemails_delete' => $item['ID'] ), admin_url( 'admin.php?page=wcemails-settings' ) ),
			'wcemails_delete_email'
		);
		ob_start() ?>
		<strong><a class="row-title"
			href="<?php echo esc_url( $edit_url ); ?>"
			title="<?php echo esc_attr( sprintf( __( 'Edit "%s"', 'woo-custom-emails' ), $item['title'] ) ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
		</strong>
		<div class="row-actions">
			<span class="edit">
				<a href="<?php echo esc_url( $edit_url ); ?>"
					data-key="<?php echo esc_attr( $item['ID'] ); ?>"
					title="<?php esc_attr_e( 'Edit this item', 'woo-custom-emails' ); ?>"><?php
					_e( 'Edit', 'woo-custom-emails' ); ?>
				</a> |
			</span>
			<span class="delete">
				<a href="<?php echo esc_url( $delete_url ); ?>"
					class="wcemails_delete"
					data-key="<?php echo esc_attr( $item['ID'] ); ?>"
					title="<?php esc_attr_e( 'Delete this item', 'woo-custom-emails' ); ?>"><?php
					_e( 'Delete', 'woo-custom-emails' ); ?>
				</a> |
			</span>
		</div><?php
		return ob_get_clean();
	}

	public function column_wcemails_description( $item ) {
		return isset( $item['description'] ) ? esc_html( $item['description'] ) : '';
	}

	public function column_wcemails_subject( $item ) {
		return isset( $item['subject'] ) ? esc_html( $item['subject'] ) : '';
	}

	public function column_wcemails_heading( $item ) {
		return esc_html( $item['heading'] );
	}

		public function column_wcemails_order_action( $item ) {
			return 'on' == $item['order_action'] ? 'Yes' : 'No'; ;
		}

		public function column_wcemails_enable( $item ) {
			return 'on' == $item['enable'] ? 'Yes' : 'No';;
		}

		public function get_sortable_columns() {
			$sortable_columns = array();
			return $sortable_columns;
		}

		public function get_bulk_actions() {
			$actions = array();
			return $actions;
		}

		public function process_bulk_action() {
			//Detect when a bulk action is being triggered...
			/*if( 'delete'===$this->current_action() ) {
			}*/

		}

		public function prepare_items() {
			$per_page = 10;

			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();

			$data = get_option( 'wcemails_email_details', array() );

			foreach ( $data as $key => $data_item ) {
				$data[ $key ]['ID'] = $key;
			}

			$current_page = $this->get_pagenum();

			$total_items = count( $data );

			$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

			$this->items = $data;

			/**
			 * REQUIRED. We also have to register our pagination options & calculations.
			 */
			$this->set_pagination_args( array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $per_page )   //WE have to calculate the total number of pages
			) );
		}

	}
}
