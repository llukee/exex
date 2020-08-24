<?php
/* Exit if file access directly */
if ( ! defined( 'ABSPATH' ) ) exit;

// Call an instance from our class
$nwswa_cpt_event = new nwswa_cpt_event();

class nwswa_cpt_event {

	/*
	 * Constructor - the brain of our class
	 * */
	public function __construct() {
		// registriert den neuen custom post type
		add_action( 'init', array( $this, 'register_custom_post_type' ) );
		// Shortcode für die Ausgabe aller Vorstellungen
		add_shortcode('events-list', array( $this, 'events_list' ));
		// Post Template Mapping
		add_filter('single_template', array( $this, 'custom_post_type_single_mapping' ));
		// Add metabox save function for custom fields
		add_action( 'save_post', array($this, '_save' ), 10, 3);
		// Add metabox for custom fields
		add_action( 'add_meta_boxes', array($this, 'custom_post_type_add_metabox' ));
		// Set columns in list view admin
		add_action('manage_nwswa_event_posts_columns', array($this, '_add_columns'), 10, 2);
		add_action('manage_nwswa_event_posts_custom_column', array($this, '_fill_columns'), 10, 2);
		add_action('post_row_actions', array($this, '_row_actions'), 10, 2);
		// add css to frontend
		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );

	}

	public function enqueue_scripts() {
		wp_register_style( 'exbook_frontend_stylesheet', plugins_url( '../assets/exbook_frontend.css', __FILE__ ) );
    wp_enqueue_style( 'exbook_frontend_stylesheet' );
	}

	/*
	 * Register Custom Post Type
	 * Post Type Name = nwswa_event
	 * */
	public function register_custom_post_type(){
		// Backend string values
		$labels = array(
			'name'               => _x( 'Vorstellungen', 'post type general name', 'nwswa_exbook' ),
			'singular_name'      => _x( 'Vorstellung', 'post type singular name', 'nwswa_exbook' ),
			'add_new'            => __( 'Neue Vorstellung anlegen', 'nwswa_exbook'),
			'add_new_item'       => __( 'Neue Vorstellung anlegen', 'nwswa_exbook' ),
			'edit_item'          => __( 'Vorstellung Daten bearbeiten', 'nwswa_exbook' ),
			'new_item'           => __( 'Neue Vorstellung', 'nwswa_exbook' ),
			'all_items'          => __( 'Alle Vorstellungen', 'nwswa_exbook' ),
			'view_item'          => __( 'Vorstellung ansehen', 'nwswa_exbook' ),
			'search_items'       => __( 'Vorstellungen durchsuchen', 'nwswa_exbook' ),
			'not_found'          => __( 'Keinen Vorstellung gefunden', 'nwswa_exbook' ),
			'not_found_in_trash' => __( 'Keinen Vorstellung im Papierkorb gefunden', 'nwswa_exbook' ),
			'parent_item_colon'  => '',
			'menu_name'          => 'Vorstellungen'
		);

		// args for the new post_type
		$args = array(
			'public'              => true,
			'event_ui'             => true,
			'event_in_menu'        => true,
			'menu_position'       => 10,
			'menu_icon'						=> 'dashicons-calendar-alt',
			'event_in_admin_bar'   => true,
			'event_in_nav_menus'   => true,
			'capability_type'     => 'page',
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'supports'            => false,
			'has_archive'         => false,
			'can_export'          => true,
			'rewrite'             => array('slug' => 'vorstellung' ),
			'labels'              => $labels,
		);

		// now register post_type with our args
		register_post_type( 'nwswa_event', $args );
	}

	/*
	 * Checks if post is from our Post Type
	 * if so, we return our custom single template
	 * */
	public function custom_post_type_single_mapping($single) {

		global $post;

		if ( $post->post_type == 'nwswa_event' ) {
		    if ( file_exists( plugin_dir_path( __FILE__ ) . '/templates/'.$post->post_type.'_single.php' ) ) {
			    return plugin_dir_path( __FILE__ ) . '/templates/'.$post->post_type.'_single.php';
		    }
		}

		return $single;
	}


	/*
	 * Creates the shortcode to display all events
	 * */
	public function events_list($atts, $content = null ) {

		$attributes_event_list = shortcode_atts( array(
			'location' => 0,
			'show'  => 0
		), $atts );

		$arguments_event_list = $this->get_eventlist_args_by_attrs($attributes_event_list);

		// Daten abfragen
		$events_list = new WP_Query( $arguments_event_list );
		ob_start();
		if ( file_exists( plugin_dir_path( __FILE__ ) . '../templates/nwswa_event_list.php' ) ) {
			require_once(plugin_dir_path( __FILE__ ) . '../templates/nwswa_event_list.php');
		}
		return ob_get_clean();
	}


	private function get_eventlist_args_by_attrs($attributes_event_list=false) {
		if($attributes_event_list===false || !is_array($attributes_event_list)) {
			exit('missing attributes for event list');
		}

		// Checks if the shortcode loation attribute is not defined and create the args withouth this meta filter
		if (0 == ($attributes_event_list['location']) && 0 != ($attributes_event_list['show'])) {
			// echo "1";
			// Loop Arguments
			$arguments_event_list = array(
				'post_type'         => 'nwswa_event',
				'post_status'       => array( 'publish' ),
				'posts_per_page'    => -1,
				'post_count' => '100',
				'meta_query' => array(
					'relation' 				=> 'AND', // Optional, defaults to "OR"
					'date_ordering' => array(
						'key'  		=> 'nwswa_event_datetime',
						'value' => date( "U" ),
						'compare' => '>'
					),
					array(
						'key'  => 'nwswa_event_show',
						'value' => $attributes_event_list['show'],
						'compare' => '=',
					),
				),

			'orderby' => 'date_ordering',
			'order' => 'ASC',
			);
		}

		// Checks if the shortcode show attribute is not defined and create the args withouth this meta filter
		elseif (0 == ($attributes_event_list['show']) && 0 != ($attributes_event_list['location'])) {
			// echo "2";
			// Loop Arguments
			$arguments_event_list = array(
				'post_type'         => 'nwswa_event',
				'post_status'       => array( 'publish' ),
				'posts_per_page'    => -1, // -1 = all posts
				'post_count' => '100',
				'meta_query' => array(
					'relation' 				=> 'AND', // Optional, defaults to "OR"
					'date_ordering' => array(
						'key'  		=> 'nwswa_event_datetime',
						'value' => date( "U" ),
						'compare' => '>'
					),

					array(
						'key'  => 'nwswa_event_location',
						'value' => $attributes_event_list['location'],
						'compare' => '=',
					),

				),

			'orderby' => 'date_ordering',
			'order' => 'ASC',
			);
		}
		// If both shortcode  attributes are  defined create the args with all filters
		elseif (0 != ($attributes_event_list['show']) && 0 != ($attributes_event_list['location'])) {
			// echo "3";
			// Loop Arguments
			$arguments_event_list = array(
				'post_type'         => 'nwswa_event',
				'post_status'       => array( 'publish' ),
				'posts_per_page'    => -1, // -1 = all posts

				'meta_query' => array(
					'relation' 				=> 'AND', // Optional, defaults to "OR"
					'date_ordering' => array(
						'key'  		=> 'nwswa_event_datetime',
						'value' => date( "U" ),
						'compare' => '>'
					),

					array(
						'key'  => 'nwswa_event_location',
						'value' => $attributes_event_list['location'],
						'compare' => '=',
					),

					array(
						'key'  => 'nwswa_event_show',
						'value' => $attributes_event_list['show'],
						'compare' => '=',
					),
				),

			'orderby' => 'date_ordering',
			'order' => 'ASC',
			);
		}

		// If no  shortcode  attributes are  defined create the args without  all filters
		else {
			// echo "4";
			// Loop Arguments
			$arguments_event_list = array(
				'post_type'         => 'nwswa_event',
				'post_status'       => array( 'publish' ),
				'posts_per_page'    => -1, // -1 = all posts
				'post_count' => '100',
				'meta_query' => array(
					'relation' 				=> 'AND', // Optional, defaults to "OR"
					'date_ordering' => array(
						'key'  		=> 'nwswa_event_datetime',
						'value' => date( "U" ),
						'compare' => '>'
					),
		),


			'orderby' => 'date_ordering',
			'order' => 'ASC',
			);
		}
		return $arguments_event_list;
	}

















	/*
	 * add metaboxes */
	public function custom_post_type_add_metabox() {
		add_meta_box(
			'nwswa_event_metabox',
			'Vorstellung Daten',
			array($this, 'nwswa_event_metabox'),
			'nwswa_event',
			'normal',
			'default'
		);
	}


	/**
	 * Output the HTML for the metabox.
	 */
	public function nwswa_event_metabox($post, $args) {
    global $post, $wp_locale;
		// Nonce field to validate form request came from current site
		wp_nonce_field( plugin_basename( __FILE__ ), 'nwswa_event_fields' );

		// Output the fields

		/***
		* FIELD DATETIME
		*/
    $time_adj = current_time( 'timestamp' );
    $datetime = get_post_meta( $post->ID, 'nwswa_event_datetime', true );
	
	if ($datetime <= 0){
		$datetime = time();
	}
	
		$month = date("m", $datetime);
		$day = date("d", $datetime);
		$year = date("Y", $datetime);
		$hour = date("H", $datetime);
		$min = date("i", $datetime);

    if ( empty( $month ) ) {
        $month = gmdate( 'm', $time_adj );
    }
    if ( empty( $day ) ) {
        $day = gmdate( 'd', $time_adj );
    }
    if ( empty( $year ) ) {
        $year = gmdate( 'Y', $time_adj );
    }
    if ( empty($hour) ) {
        $hour = gmdate( 'H', $time_adj );
    }
    if ( empty($min) ) {
        $min = '00';
    }

		echo '<p><label for="field_id">Datum und Uhrzeit:</label>';

		echo '<input type="num" name="' . 'event_datetime[day]" value="' . $day  . '" size="2" maxlength="2" />';

    $month_s = '<select name="' . 'event_datetime[month]">';
    for ( $i = 1; $i < 13; $i = $i +1 ) {
        $month_s .= "\t\t\t" . '<option value="' . zeroise( $i, 2 ) . '"';
        if ( $i == $month )
            $month_s .= ' selected="selected"';
        $month_s .= '>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
    }
    $month_s .= '</select>';

    echo $month_s;
    echo '<input type="num" name="' . 'event_datetime[year]" value="' . $year . '" size="4" maxlength="4" /> um ';
    echo '<input type="num" name="' . 'event_datetime[hour]" value="' . $hour . '" size="2" maxlength="2"/>:';
    echo '<input type="num" name="' . 'event_datetime[minute]" value="' . $min . '" size="2" maxlength="2" />';

		echo '</p>';

		/***
		* FIELD SEATS
		*/
		$seats = get_post_meta( $post->ID, 'nwswa_event_seats', true );
		if ( empty( $seats ) ) {
				$seats = 30;
		}

		$seats_html = '<p><label for="event_seats">Plätze:</label>';

		$seats_html .= '<select name="' . 'event_seats">';
    for ( $i = 1; $i < 101; $i = $i +1 ) {
        $seats_html .= "\t\t\t" . '<option value="' . $i . '"';
        if ( $i == $seats )
            $seats_html .= ' selected="selected"';
        $seats_html .= '>' . $i . "</option>\n";
    }
    $seats_html .= '</select></p>';

		echo $seats_html;

		/***
		* FIELD Connection to CPT Show
		*/
		$show_id = get_post_meta($post->ID, 'nwswa_event_show', true);

    echo '<p><label for="event_show">Veranstaltung:</label>';
    echo '<select id="event_show" name="event_show">';
    // Query the shows here
    $query = new WP_Query( 'post_type=nwswa_show' );
    while ( $query->have_posts() ) {
        $query->the_post();
        $id = get_the_ID();
        $selected = "";

        if($id == $show_id){
            $selected = ' selected="selected"';
        }
        echo '<option' . $selected . ' value=' . $id . '>' . get_the_title() . '</option>';
    }
    echo '</select></p>';

		/***
		* FIELD Connection to CPT Location
		*/
		$location_id = get_post_meta($post->ID, 'nwswa_event_location', true);

    echo '<p><label for="event_location">Standort:</label>';
    echo '<select id="event_location" name="event_location">';
    // Query the locations here
    $query = new WP_Query( 'post_type=nwswa_location' );
    while ( $query->have_posts() ) {
        $query->the_post();
        $id = get_the_ID();
        $selected = "";

        if($id == $location_id){
            $selected = ' selected="selected"';
        }
        echo '<option' . $selected . ' value=' . $id . '>' . get_the_title() . '</option>';
    }
    echo '</select></p>';

		/***
		* FIELD Connection to CPT Mailvorlage
		*/
		$event_id = get_post_meta($post->ID, 'nwswa_event_mailtpl', true);

    echo '<p><label for="event_mailtpl">Mailvorlage:</label>';
    echo '<select id="event_mailtpl" name="event_mailtpl">';
    // Query the events here
    $query = new WP_Query( 'post_type=nwswa_mailtpl' );
    while ( $query->have_posts() ) {
        $query->the_post();
        $id = get_the_ID();
        $selected = "";

        if($id == $event_id){
            $selected = ' selected="selected"';
        }
        echo '<option' . $selected . ' value=' . $id . '>' . get_the_title() . '</option>';
    }
    echo '</select></p>';


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

	public function _add_columns($columns) {
		unset($columns['date']);
		unset($columns['title']);
		$columns['event_show'] = __('Veranstaltung', 'nwswa_exbook');
		$columns['event_datetime'] = __('Datum & Uhrzeit', 'nwswa_exbook');
		$columns['event_location'] = __('Standort', 'nwswa_exbook');
		$columns['event_seats'] = __('Plätze', 'nwswa_exbook');
		$columns['event_mailtpl'] = __('Mailvorlage', 'nwswa_exbook');
		return $columns;
	}
	
	
// Make the custom column sortable
function my_sortable_cake_column( $columns ) {
    $columns['event_show'] = 'event_show';
 
    return $columns;
}

// Handle the custom column sorting
function itsg_add_custom_column_do_sortable( $vars ) {

		// check if sorting has been applied
		if ( isset( $vars['orderby'] ) && 'event_show' == $vars['orderby'] ) {

			// apply the sorting to the post list
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'event_show',
					'orderby' => 'meta_value'
				)
			);
		}
	

	return $vars;
}






	public function _fill_columns($column_name, $post_id) {
    global $wpdb;
    switch ($column_name) {
        case 'event_show':
					$event_show = get_post_meta( $post_id, 'nwswa_event_show', true );
					$show = get_post($event_show);
					echo $show->post_title;
					
            break;
				case 'event_datetime':
					$event_datetime = get_post_meta( $post_id, 'nwswa_event_datetime', true );
					if($event_datetime>0) {
						echo date("d.m.Y H:i", $event_datetime);
					}
            break;
				case 'event_location':
					$event_location = get_post_meta( $post_id, 'nwswa_event_location', true );
					$location = get_post($event_location);
					echo $location->post_title;
            break;
				case 'event_seats':
					$event_seats = get_post_meta( $post_id, 'nwswa_event_seats', true );
					echo $event_seats;
            break;
				case 'event_mailtpl':
					$event_mailtpl = get_post_meta( $post_id, 'nwswa_event_mailtpl', true );
					$mailtpl = get_post($event_mailtpl);
					echo $mailtpl->post_title;
            break;
        default:
            break;
    }
	}

	public function _row_actions($actions, $post) {
		if ($post->post_type=='nwswa_event' && $post->ID>0) {
        $actions['pdf'] = '<a href="'.wp_nonce_url(sprintf('admin.php?post=%d&action=nwswa_reservation_pdf',$post->ID), basename(__FILE__), 'nwswa_reservation_pdf_nonce').'" target="_blank" title="" rel="permalink">PDF</a>';
    }
    return $actions;
	}




}


function nwswa_reservation_pdf(){
	global $wpdb;

	if ( !file_exists( plugin_dir_path( __FILE__ ) . '../exbook_mpdf.php' ) ) {
		wp_die('mpdf not found.');
	}
	require_once( plugin_dir_path( __FILE__ ) . '../exbook_mpdf.php' );

	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'nwswa_reservation_pdf' == $_REQUEST['action'] ) ) ) {
		wp_die('No event selected.');
	}

	/*
	 * Nonce verification
	 */
	if ( !isset( $_GET['nwswa_reservation_pdf_nonce'] ) || !wp_verify_nonce( $_GET['nwswa_reservation_pdf_nonce'], basename( __FILE__ ) ) ) {
		return;
	}

	/*
	 * get the original post id
	 */
	$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
	/*
	 * and all the original post data then
	 */
	$post = get_post( $post_id );

	// set pdf template file
	$pdf_template_file = plugin_dir_path( __FILE__ ) . '../templates/nwswa_reservation_pdf.php';
	if ( !file_exists( $pdf_template_file ) ) {
		wp_die(sprintf('pdf template %s not found.', $pdf_template_file));
	}

	mpdf_create($post->ID, $pdf_template_file);

	exit();
}

add_action( 'admin_action_nwswa_reservation_pdf', 'nwswa_reservation_pdf' );
