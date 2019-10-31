<?php
/* Exit if file access directly */
if ( ! defined( 'ABSPATH' ) ) exit;

// Call an instance from our class
$nwswa_cpt_show = new nwswa_cpt_show();

class nwswa_cpt_show {

	/*
	 * Constructor - the brain of our class
	 * */
	public function __construct() {
		// registriert den neuen custom post type
		add_action( 'init', array( $this, 'register_custom_post_type' ) );
		// Shortcode für die Ausgabe aller Veranstaltungen
		add_shortcode('shows-list', array( $this, 'shows_list' ));
		// Post Template Mapping
		add_filter('single_template', array( $this, 'custom_post_type_single_mapping' ));
		// Set columns in list view admin
		add_action('manage_nwswa_show_posts_columns', array($this, '_add_columns'), 10, 2);
		add_action('manage_nwswa_show_posts_custom_column', array($this, '_fill_columns'), 10, 2);
	}

	/*
	 * Register Custom Post Type
	 * Post Type Name = nwswa_show
	 * */
	public function register_custom_post_type(){
		// Backend string values
		$labels = array(
			'name'               => _x( 'Veranstaltungen', 'post type general name', 'nwswa_exbook' ),
			'singular_name'      => _x( 'Veranstaltung', 'post type singular name', 'nwswa_exbook' ),
			'add_new'            => __( 'Neue Veranstaltung anlegen', 'nwswa_exbook'),
			'add_new_item'       => __( 'Neue Veranstaltung anlegen', 'nwswa_exbook' ),
			'edit_item'          => __( 'Veranstaltung Daten bearbeiten', 'nwswa_exbook' ),
			'new_item'           => __( 'Neue Veranstaltung', 'nwswa_exbook' ),
			'all_items'          => __( 'Alle Veranstaltungen', 'nwswa_exbook' ),
			'view_item'          => __( 'Veranstaltung ansehen', 'nwswa_exbook' ),
			'search_items'       => __( 'Veranstaltungen durchsuchen', 'nwswa_exbook' ),
			'not_found'          => __( 'Keinen Veranstaltung gefunden', 'nwswa_exbook' ),
			'not_found_in_trash' => __( 'Keinen Veranstaltung im Papierkorb gefunden', 'nwswa_exbook' ),
			'parent_item_colon'  => '',
			'menu_name'          => 'Veranstaltungen'
		);

		// args for the new post_type
		$args = array(
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 10,
			'menu_icon'						=> 'dashicons-editor-video',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'capability_type'     => 'page',
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'supports'            => array( 'title', 'editor' ),
			'has_archive'         => false,
			'can_export'          => true,
			'rewrite'             => array('slug' => 'veranstaltung' ),
			'labels'              => $labels,
		);

		// now register post_type with our args
		register_post_type( 'nwswa_show', $args );
	}

	/*
	 * Checks if post is from our Post Type
	 * if so, we return our custom single template
	 * */
	public function custom_post_type_single_mapping($single) {

		global $post;

		if ( $post->post_type == 'nwswa_show' ) {
				$this->save_frontend_registration();
		    if ( file_exists( plugin_dir_path( __DIR__ ) . '/templates/'.$post->post_type.'_single.php' ) ) {
			    return plugin_dir_path( __DIR__ ) . '/templates/'.$post->post_type.'_single.php';
		    }
		}

		return $single;
	}



	public function save_frontend_registration() {
		

		if(!isset($_POST['submit'])) {
			return;
		}
			 // echo 'Test';

		if( !wp_verify_nonce($_POST['cform_generate_nonce'], 'submit') ) {
				return;
		}
		// echo 'Nonce: Ok';
		
		
		$message = array();

		if ( !isset($_POST['reservation_firstname']) ) {
				// echo 'Kein Namen';
				$message[] = "Kein Namen";
		}

		if (strlen($_POST['reservation_firstname']) < 3) {
				// echo 'Bitte füllen Sie das Feld Vorname aus.';
				$message[] .= "Bitte füllen Sie das Feld Vorname aus.";
		}
		
		if ( !isset($_POST['reservation_lastname']) ) {
				// echo 'Kein Nachnamen';
				$message[] .= "Kein Nachnamen";
		}

		if (strlen($_POST['reservation_lastname']) < 3) {
				// echo 'Bitte füllen Sie das Feld Nachname aus.';
				$message[] .= "Bitte füllen Sie das Feld Nachname aus.";
		}
		
		if ( !isset($_POST['reservation_phone']) ) {
				// echo 'Kein Telefon';
				$message[] .= "Kein Nachnamen";
		}

		if (strlen($_POST['reservation_phone']) < 3) {
				// echo 'Bitte füllen Sie das Feld Telefon aus.';
				$message[] .= "Bitte füllen Sie das Feld Telefon aus.";
		}
		
		if ( !isset($_POST['reservation_email']) ) {
				// echo 'Kein EMail';
				$message[] .= "Kein E-Mail";
		}

		if (strlen($_POST['reservation_email']) < 3) {
				// echo 'Bitte füllen Sie das Feld E-Mail aus.';
				$message[] .= "Bitte füllen Sie das Feld E-Mail aus.";
		}
		
		if (!filter_var($_POST['reservation_email'], FILTER_VALIDATE_EMAIL)) {
				// echo 'Bitte füllen Sie das Feld E-Mail aus.';
				$message[] .= "Ihre E-Mail ist ungültig.";
		}
		
		
		if ($_POST['reservation_quantity'] <= 0) {
				// echo 'Sie müssen mindestens 1 Platz auswählen.';
				$message[] .= "Sie müssen mindestens 1 Platz auswählen.";
		}

		
		 // Save post field user input into variables to ouptput as default form values
		 global $reservation_event;
		 global $reservation_firstname;
		 global $reservation_lastname;
		 global $reservation_phone;
		 global $reservation_email;
		 
		 $reservation_event = '';
		 $reservation_firstname = '';
		 $reservation_lastname = '';
		 $reservation_phone = '';
		 $reservation_email = '';
		 
		 if ($_POST['reservation_event']){
		 $reservation_event = $_POST['reservation_event'];}
		 
		 if ($_POST['reservation_firstname']){
		 $reservation_firstname = $_POST['reservation_firstname'];}
		 
		 if ($_POST['reservation_lastname']){
		 $reservation_lastname = $_POST['reservation_lastname'];}
		 
		 if ($_POST['reservation_phone']){
		 $reservation_phone = $_POST['reservation_phone'];}
		 
		 if ($_POST['reservation_email']){
		 $reservation_email = $_POST['reservation_email'];}

	 // wp_mail....
	 
	 /* todo: insert into mailchimp */

		
		global $message_html;
		
		if (is_array($message) && count($message)>0) {	
			$message_html .= '<ul class="error_message">';
			foreach($message as $msg_line) {
				$message_html .= '<li>'.$msg_line.'</li>';
			}
			$message_html .= '</ul>';
		}
		
		
		
		else {
	
		// Add the content of the form to $post as an array
		$post = array(
				'post_status'   => 'publish',
				'post_type' 	=> 'nwswa_reservation',
				'meta_input'   => array(
                    'nwswa_reservation_event' => $_POST['reservation_event'],
					'nwswa_reservation_firstname' => $_POST['reservation_firstname'],
					'nwswa_reservation_lastname' => $_POST['reservation_lastname'],
					'nwswa_reservation_phone' => $_POST['reservation_phone'],
					'nwswa_reservation_email' => $_POST['reservation_email'],
                    'nwswa_reservation_quantity'   => $_POST['reservation_quantity'],
					'nwswa_reservation_newsletter'   => $_POST['reservation_newsletter'],
                ),
		);
		wp_insert_post($post);
		
		
		// send e-mail to registered peorson
		
		$template_id = get_post_meta( $reservation_event, 'nwswa_event_mailtpl', true );
		
		$mail_subject = get_post_meta( $template_id, 'nwswa_mailtpl_mail_subject', true );
		$mail_template = get_post_meta( $template_id, 'nwswa_mailtpl_mail_content', true );
		
		
		
		
		
		//Replace shortcodes  in message text
		//Get meta fields
		$show_id = get_post_meta( $reservation_event, 'nwswa_event_show', true );
		$show_name = get_the_title($show_id);
		
		$location_id = get_post_meta( $reservation_event, 'nwswa_event_show', true );
		$show_name = get_the_title($location_id);
		
		$get_date = get_post_meta( $reservation_event, 'nwswa_event_datetime', true );
		
		
				$get_date_day = date("l", $get_date);
				switch($get_date_day)
				{
  				case "Monday": $get_date_day = "Mo"; break;
  				case "Tuesday": $get_date_day = "Di"; break;
  				case "Wednesday": $get_date_day = "Mi"; break;
  				case "Thursday": $get_date_day = "Do"; break;
  				case "Friday": $get_date_day = "Fr"; break;
  				case "Saturday": $get_date_day = "Sa"; break;
  				case "Sunday": $get_date_day = "So"; break;
				};
				
		$show_date = $get_date_day;
		$show_date .= ", ";
		$show_date .= date("d.m.Y, H:i", $get_date);		
		
		$show_reservation_quantity = $_POST['reservation_quantity'];
		
		
		$searchArray = array("<%quantity%>", "<%show%>", "<%location%>", "<%datetime%>");
		$replaceArray = array($show_reservation_quantity, $show_name, $show_location, $show_date);
		$intoString = $mail_template;
		$mail_template = str_replace($searchArray, $replaceArray, $intoString);

		
		$to = $reservation_email;
		$subject = $mail_subject;
		$message = $mail_template;
		$headers = array(
			'From: '.get_bloginfo( 'name' ).' <'.get_bloginfo( 'admin_email' ).'>'
		);

		wp_mail( $to, $subject, $message, $headers );

		
		// generate sucess message
		global $formular_sent;
		$formular_sent = "true";
		$message_html .= '<ul class="sucess_message">';
		$message_html .= "Vielen Dank für Ihre Reservierung. Sie werden in Kürze eine Bestätigung per E-Mail erhalten.";
		$message_html .= '</ul>';
		
		}
}
		
	
	 
	 
	 
	/*
	 * Save registration form input data
	 * */
	public function _save($post_id, $post, $update){
		

		$post_type = get_post_type($post_id);
		if ( "nwswa_event" != $post_type ) return;

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
		if ( ! isset( $_POST['nwswa_event_fields'] ) || ! wp_verify_nonce( $_POST['nwswa_event_fields'], plugin_basename(__FILE__) ) ) {
			return $post_id;
		}

		$event_meta = array(
			'event_datetime',
			'event_seats',
			'event_show',
			'event_location',
			'event_mailtpl',
		);

		foreach($event_meta as $event_meta_key) {
			$key = 'nwswa_'.$event_meta_key;
			$value = $_POST[$event_meta_key];
			if(is_string($_POST[$event_meta_key])) {
				$value = esc_textarea($_POST[$event_meta_key]);
			}
			if($event_meta_key=='event_datetime') {
				$datetime_array = $_POST[$event_meta_key];
				$value = mktime($datetime_array['hour'], $datetime_array['minute'], 0, $datetime_array['month'], $datetime_array['day'], $datetime_array['year']);
			}

			if ( get_post_meta( $post_id, $key, FALSE ) ) { // If the custom field already has a value
	        update_post_meta( $post_id, $key, $value );
	    } else { // If the custom field doesn't have a value
	        add_post_meta( $post_id, $key, $value );
	    }
	    if ( !$value ) delete_post_meta( $post_id, $key ); // Delete if blank
		}

	}

	/*
	 * Creates the shortcode to display all shows
	 * */
	public function shows_list() {

		// Loop Arguments
		$args = array(
			'post_type'         => 'nwswa_show',
			'post_status'       => array( 'publish' ),
			'posts_per_page'    => -1 // -1 = all posts
		);

		// Daten abfragen
		$loop = new WP_Query( $args );

		// Start output buffering
		ob_start();
		?>


		<div class="row">

		<?php
		// start des WordPress Loops für unseren post type
		while ( $loop->have_posts() ) : $loop->the_post();
			// post id abfragen
			$post_id = get_the_ID();

			// Template Ausgabe
			?>
			<div class="col-md-4 text-center">
				<img style="max-height: 100px;" class="img-fluid mx-auto d-block rounded-circle" src="<?php echo get_the_post_thumbnail_url( $post_id, 'full' ) ?>">

				<span style="font-size: 1.5rem; font-weight: 700; color: #0e7c7b;" class="text-center"><?php echo get_the_title( $post_id ) ?></span>
				<p class="text-center">
					<a href="<?php echo get_the_permalink( $post_id ) ?>" class="btn btn-tobi2" >Mehr erfahren</a>
				</p>
			</div>
		<?php
		// Ende unserer while-schleife
		endwhile;
		?>
		</div>

		<?php
		// reset data
		wp_reset_postdata();

		// return buffer
		return ob_get_clean();
	}

	public function _add_columns($columns) {
		unset($columns['date']);
		$columns['title'] = 'Name';
		$columns['events'] = 'Vorstellungen';
		return $columns;
	}

	public function _fill_columns($column_name, $post_id) {
    global $wpdb;
    switch ($column_name) {
        case 'events':
					$args = array(
		        'post_type'     => 'nwswa_event',
		        'post_status'   => 'publish',
		        'meta_query'    => array(
	            array(
	                'key'   => 'nwswa_event_show',
	                'value' => $post_id,
	                'compare'   => 'LIKE'
	            ))
			    );

					$events = get_posts($args);
					echo count($events);

            break;
        default:
            break;
    }
	}

}
