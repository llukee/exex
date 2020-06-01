<?php
/* Exit if file access directly */
if ( ! defined( 'ABSPATH' ) ) exit;

// Call an instance from our class
$nwswa_cpt_reservation = new nwswa_cpt_reservation();

class nwswa_cpt_reservation {

	/*
	 * Constructor - the brain of our class
	 * */
	public function __construct() {
		// registriert den neuen custom post type
		add_action( 'init', array( $this, 'register_custom_post_type' ) );
		// Post Template Mapping
		add_filter('single_template', array( $this, 'custom_post_type_single_mapping' ));
		// Add metabox save function for custom fields
		add_action( 'save_post', array($this, '_save' ), 10, 3);
		// Add metabox for custom fields
		add_action( 'add_meta_boxes', array($this, 'custom_post_type_add_metabox' ));
		// Set columns in list view admin
		add_action('manage_nwswa_reservation_posts_columns', array($this, '_add_columns'), 10, 2);
		add_action('manage_nwswa_reservation_posts_custom_column', array($this, '_fill_columns'), 10, 2);
	}

	/*
	 * Register Custom Post Type
	 * Post Type Name = nwswa_reservation
	 * */
	public function register_custom_post_type(){
		// Backend string values
		$labels = array(
			'name'               => _x( 'Reservationen', 'post type general name', 'nwswa_exbook' ),
			'singular_name'      => _x( 'Reservation', 'post type singular name', 'nwswa_exbook' ),
			'add_new'            => __( 'Neue Reservation anlegen', 'nwswa_exbook'),
			'add_new_item'       => __( 'Neue Reservation anlegen', 'nwswa_exbook' ),
			'edit_item'          => __( 'Reservation Daten bearbeiten', 'nwswa_exbook' ),
			'new_item'           => __( 'Neue Reservation', 'nwswa_exbook' ),
			'all_items'          => __( 'Alle Reservationen', 'nwswa_exbook' ),
			'view_item'          => __( 'Reservation ansehen', 'nwswa_exbook' ),
			'search_items'       => __( 'Reservationen durchsuchen', 'nwswa_exbook' ),
			'not_found'          => __( 'Keine Reservation gefunden', 'nwswa_exbook' ),
			'not_found_in_trash' => __( 'Keine Reservation im Papierkorb gefunden', 'nwswa_exbook' ),
			'parent_item_colon'  => '',
			'menu_name'          => 'Reservationen'
		);

		// args for the new post_type
		$args = array(
			'public'              => true,
			'reservation_ui'             => true,
			'reservation_in_menu'        => true,
			'menu_position'       => 10,
			'menu_icon'						=> 'dashicons-tickets',
			'reservation_in_admin_bar'   => true,
			'reservation_in_nav_menus'   => true,
			'capability_type'     => 'page',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'supports'            => false,
			'has_archive'         => false,
			'can_export'          => true,
			'rewrite'             => false,
			'labels'              => $labels,
		);

		// now register post_type with our args
		register_post_type( 'nwswa_reservation', $args );
	}

	/*
	 * Checks if post is from our Post Type
	 * if so, we return our custom single template
	 * */
	public function custom_post_type_single_mapping($single) {

		global $post;

		if ( $post->post_type == 'nwswa_reservation' ) {
		    if ( file_exists( plugin_dir_path( __FILE__ ) . '/templates/'.$post->post_type.'_single.php' ) ) {
			    return plugin_dir_path( __FILE__ ) . '/templates/'.$post->post_type.'_single.php';
		    }
		}

		return $single;
	}

	/*
	 * add metaboxes */
	public function custom_post_type_add_metabox() {
		add_meta_box(
			'nwswa_reservation_metabox',
			'Reservation Daten',
			array($this, 'nwswa_reservation_metabox'),
			'nwswa_reservation',
			'normal',
			'default'
		);
	}

	/**
	 * Output the HTML for the metabox.
	 */
	public function nwswa_reservation_metabox($post, $args) {
    global $post, $wp_locale;
		// Nonce field to validate form request came from current site
		wp_nonce_field( plugin_basename( __FILE__ ), 'nwswa_reservation_fields' );

		// Output the fields
		$reservation_id = $post->ID;

		/***
		* FIELD EVENT
		*/
		$event = get_post_meta( $post->ID, 'nwswa_reservation_event', true );

    echo '<p><label for="reservation_event">Vorstellung:</label>';
    echo '<select id="reservation_event" name="reservation_event">';
    // Query the shows here
    $query = new WP_Query( 'post_type=nwswa_event' );
    while ( $query->have_posts() ) {
				$option_text = '';
        $query->the_post();
				$event_id = get_the_ID();

				// show title + event datetime
				$show_id = get_post_meta( $event_id, 'nwswa_event_show', true );
				$show = get_post($show_id);
				$datetime_ts = get_post_meta( $event_id, 'nwswa_event_datetime', true );

				$option_text .= $show->post_title;
				$option_text .= ' - ';
				$option_text .= date("d.m.Y H:i", $datetime_ts);

        $selected = "";

        if($event_id == $event){
            $selected = ' selected="selected"';
        }
        echo '<option' . $selected . ' value=' . $event_id . '>' . $option_text . '</option>';
    }
    echo '</select></p>';

		/***
		* FIELD FIRSTNAME
		*/
		$firstname = get_post_meta( $reservation_id, 'nwswa_reservation_firstname', true );

		echo '<p><label for="reservation_firstname">Vorname:</label>';
		echo '<input type="text" name="reservation_firstname" value="' . $firstname  . '" /></p>';

		/***
		* FIELD LASTNAME
		*/
		$lastname = get_post_meta( $reservation_id, 'nwswa_reservation_lastname', true );

		echo '<p><label for="reservation_lastname">Nachname:</label>';
		echo '<input type="text" name="reservation_lastname" value="' . $lastname  . '" /></p>';

		/***
		* FIELD PHONE
		*/
		$phone = get_post_meta( $reservation_id, 'nwswa_reservation_phone', true );

		echo '<p><label for="reservation_phone">Telefonnummer:</label>';
		echo '<input type="tel" name="reservation_phone" value="' . $phone  . '" /></p>';

		/***
		* FIELD EMAIL
		*/
		$email = get_post_meta( $reservation_id, 'nwswa_reservation_email', true );

		echo '<p><label for="reservation_email">E-Mail:</label>';
		echo '<input type="email" name="reservation_email" value="' . $email  . '" /></p>';

		/***
		* FIELD QUANTITY
		*/
		$quantity = get_post_meta( $reservation_id, 'nwswa_reservation_quantity', true );

		
					
								


		
		
		echo '<p><label for="reservation_quantity">Anzahl Plätze:</label>';
		echo '<select name="reservation_quantity">';
		for($q=1;$q<=100;$q++) {
			$selected = '';
			if($q==$quantity) {
				$selected = ' selected="selected" ';
			}
			echo '<option value="'.$q.'" '.$selected.'>'.$q.'</option>';
		}
		echo '</select></p>';
		
		
		
		
	

		/***
		* FIELD STATUS
		*/
		$status = get_post_meta( $reservation_id, 'nwswa_reservation_status', true );

		echo '<p><label for="reservation_status">Status:</label>';
		echo '<select name="reservation_status">';
		foreach(array('bestätigt','storniert') as $reservation_status) {
			$selected = '';
			if($reservation_status==$status) {
				$selected = ' selected="selected" ';
			}
			echo '<option value="'.$reservation_status.'" '.$selected.'>'.$reservation_status.'</option>';
		}
		echo '</select></p>';

		/***
		* FIELD NEWSLETTER
		*/
		$newsletter = get_post_meta( $reservation_id, 'nwswa_reservation_newsletter', true );

		echo '<p><label for="reservation_newsletter">Newsletter:</label>';
		echo '<select name="reservation_newsletter">';
		foreach(array(0=>'nicht gewünscht',1=>'abonniert') as $key=>$reservation_newsletter) {
			$selected = '';
			if($key==$newsletter) {
				$selected = ' selected="selected" ';
			}
			echo '<option value="'.$key.'" '.$selected.'>'.$reservation_newsletter.'</option>';
		}
		echo '</select></p>';

		/***
		* FIELD MEMO
		*/
		$memo = get_post_meta( $reservation_id, 'nwswa_reservation_memo', true );

		echo '<p><label for="reservation_memo">Memo:</label>';
		echo '<textarea name="reservation_memo" cols="50" rows="5">'.$memo.'</textarea></p>';


		echo '<style>
		#post-body-content {
			margin-bottom:0;
		}
label {
	width: 160px;
	display: block;
	float: left;
}
		</style>';

	}

	public function _save($post_id, $post, $update){

		$post_type = get_post_type($post_id);
		if ( "nwswa_reservation" != $post_type ) return;

		// Return if the user doesn't have edit permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
	  }

		// Verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times.
		if ( ! isset( $_POST['nwswa_reservation_fields'] ) || ! wp_verify_nonce( $_POST['nwswa_reservation_fields'], plugin_basename(__FILE__) ) ) {
			return $post_id;
		}

		$reservation_meta = array(
			'reservation_event',
			'reservation_firstname',
			'reservation_lastname',
			'reservation_phone',
			'reservation_email',
			'reservation_quantity',
			'reservation_status',
			'reservation_newsletter',
			'reservation_memo',
		);

		foreach($reservation_meta as $reservation_meta_key) {
			$key = 'nwswa_'.$reservation_meta_key;
			$value = $_POST[$reservation_meta_key];
			if(is_string($_POST[$reservation_meta_key])) {
				$value = esc_textarea($_POST[$reservation_meta_key]);
			}

			if ( get_post_meta( $post_id, $key, FALSE ) ) { // If the custom field already has a value
	        update_post_meta( $post_id, $key, $value );
	    } else { // If the custom field doesn't have a value
	        add_post_meta( $post_id, $key, $value );
	    }
	    if ( !$value ) delete_post_meta( $post_id, $key ); // Delete if blank
		}

	}

	public function _add_columns($columns) {
		unset($columns['date']);
		unset($columns['title']);
		$columns['reservation_event'] = __('Vorstellung', 'nwswa_exbook');
		$columns['reservation_fullname'] = __('Name', 'nwswa_exbook');
		$columns['reservation_phone'] = __('Telefon', 'nwswa_exbook');
		$columns['reservation_email'] = __('E-Mail', 'nwswa_exbook');
		$columns['reservation_quantity'] = __('Plätze', 'nwswa_exbook');
		$columns['reservation_status'] = __('Status', 'nwswa_exbook');
		$columns['reservation_newsletter'] = __('Newsletter', 'nwswa_exbook');
		return $columns;
	}

	public function _fill_columns($column_name, $post_id) {
    global $wpdb;
    switch ($column_name) {
        case 'reservation_event':
					$reservation_event = get_post_meta( $post_id, 'nwswa_reservation_event', true );
					$event = get_post($reservation_event);

					// show title + event datetime
					$show_id = get_post_meta( $event->ID, 'nwswa_event_show', true );
					$show = get_post($show_id);
					$datetime_ts = get_post_meta( $event->ID, 'nwswa_event_datetime', true );

					$reservation_event_name = '';
					$reservation_event_name .= $show->post_title;
					$reservation_event_name .= ' - ';
					$reservation_event_name .= date("d.m.Y H:i", $datetime_ts);

					echo $reservation_event_name;
            break;
				case 'reservation_fullname':
					$nwswa_reservation_firstname = get_post_meta( $post_id, 'nwswa_reservation_firstname', true );
					$nwswa_reservation_lastname = get_post_meta( $post_id, 'nwswa_reservation_lastname', true );
					echo $nwswa_reservation_firstname.' '.$nwswa_reservation_lastname;
            break;
				case 'reservation_phone':
					$reservation_phone = get_post_meta( $post_id, 'nwswa_reservation_phone', true );
					echo $reservation_phone;
            break;
				case 'reservation_phone':
					$reservation_phone = get_post_meta( $post_id, 'nwswa_reservation_phone', true );
					echo $reservation_phone;
            break;
				case 'reservation_email':
					$reservation_email = get_post_meta( $post_id, 'nwswa_reservation_email', true );
					echo $reservation_email;
            break;
				case 'reservation_quantity':
					$reservation_quantity = get_post_meta( $post_id, 'nwswa_reservation_quantity', true );
					echo $reservation_quantity;
            break;
				case 'reservation_status':
					$reservation_status = get_post_meta( $post_id, 'nwswa_reservation_status', true );
					echo $reservation_status;
            break;
				case 'reservation_newsletter':
					$reservation_newsletter = get_post_meta( $post_id, 'nwswa_reservation_newsletter', true );
					echo ($reservation_newsletter=='1'?'abonniert':'nicht gewünscht');
            break;
        default:
            break;
    }
	}
	
	add_filter( 'manage_edit-nwswa_cpt_reservation_sortable_columns', 'my_sortable_cake_column' );
		function my_sortable_cake_column( $columns ) {
			$columns['reservation_event'] = 'reservation_events';
		 
			//To make a column 'un-sortable' remove it from the array
			//unset($columns['date']);
		 
			return $columns;
		}

}
