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
		$a = shortcode_atts( array(
			'location' => 'loca-not-defined',
			'show'  =>  'show-not-defined'
		), $atts );
	
		// Loop Arguments
		$args = array(
			'post_type'         => 'nwswa_event',
			'post_status'       => array( 'publish' ),
			'posts_per_page'    => -1, // -1 = all posts
			
			'relation' => 'AND', // Optional, defaults to "OR"

			'meta_query' => array(
				
				'date_ordering' => array(
					'key'  		=> 'nwswa_event_datetime',
					'value' => date( "U" ),
					'compare' => '>'
				),
				
				array(
					'meta_key'  => 'nwswa_event_location',
					'meta_value' => '8', //$a['location']
					'meta_compare' => '='
				),
				
				array(
					'meta_key'  => 'nwswa_event_show',
					'meta_value' => 'warten-auf-godot', //$a['show']
					'meta_compare' => '='
				),
				
			),
			
			
			
		'orderby' => 'date_ordering',
		'order' => 'ASC',
			
			
		);

					
					
		// Daten abfragen
		$loop = new WP_Query( $args );

		// Start output buffering
		ob_start();
		?>

		<style>
				.container_fluid{
					line-height:1.3;
					font-size:14pt;
					
				}
				.table-row {
				  display: flex;           display: -webkit-flex;
				  flex-direction: row;     -webkit-flex-direction: row;
				  flex-grow: 0;            -webkit-flex-grow: 0;
				  flex-wrap: nowrap;         -webkit-flex-wrap: nowrap;
				  width: 100%;
				  padding-left: 15px;
				  padding-right: 15px;
				}
				
				.text {
				  flex-grow: 1;            -webkit-flex-grow: 1;
				  padding-right: 20px;
				}
				.table-row {
				  border-collapse: collapse;
				  padding-top: 10px;
				}

				.table-row.header {
				  background-color:;
				  font-weight: bold;
				  padding-top: 8px;
				  padding-bottom: 0px;
				}
				.text {
				  width:150px;
				}
				
				.long {
				  width:250px;
				}

				.short {
				  width: 40px;
				}
		</style>
		<div class="container_fluid">
			<div class="table-row header">
				<div class="text short">Tag</div>
				<div class="text">Datum/Zeit</div>
				<div class="text long">Stück</div>
				<div class="text">Ort</div>
				<div class="text">freie Plätze</div>
			  </div>
		<?php
		// start des WordPress Loops für unseren post type
		while ( $loop->have_posts() ) : $loop->the_post();
			// post id abfragen
			$post_id = get_the_ID();
			
			// post id von show abfragen
			$post_id_show = get_post_meta( $post_id, 'nwswa_event_show', true );
			
			// location
			$event_location = get_post_meta( $post_id, 'nwswa_event_location', true );
			$location = get_post($event_location);
			
			// seats
			$event_seats = get_post_meta( $post_id, 'nwswa_event_seats', true );
			
			// date
			$event_datetime = get_post_meta( $post_id, 'nwswa_event_datetime', true );
					
			// number of reservations
			$reservation_quantity = (int)get_post_meta( $post_id, 'reservation_quantity', true );

			$args = array ( 
			// Post or Page ID
			'post_type' => 'nwswa_reservation',
			'meta_key'  => 'nwswa_reservation_event',
			'meta_value' => $post_id,
			'meta_compare' => '='
			);
			
			

			// The Query
			$the_query = new WP_Query( $args );

			// The Loop
			if ( $the_query->have_posts() ) {

				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$nwswa_reservation_quantity = get_post_meta( get_the_ID(), 'nwswa_reservation_quantity', true);
					$reservation_quantity += (int)$nwswa_reservation_quantity;
				}


				/* Restore original Post Data */
				wp_reset_postdata();

			} else {

			$reservation_quantity = 0;

			}


			// Template Ausgabe
			?>
			
			


  

  
    

  
  


			<div class="table-row">
				
				<?php
				// Get event Date
				if($event_datetime>0) {$text_event_datetime = date("d.m.Y H:i", $event_datetime);}
				
				// Get Day
				$day = date("l", $event_datetime);
				switch($day)
				{
				case "Monday": $day = "Mo"; break;
				case "Tuesday": $day = "Di"; break;
				case "Wednesday": $day = "Mi"; break;
				case "Thursday": $day = "Do"; break;
				case "Friday": $day = "Fr"; break;
				case "Saturday": $day = "Sa"; break;
				case "Sunday": $day = "So"; break;
				};
				
				// Get event title
				$text_event_title = get_the_title( $post_id_show );
				
				// Calculate free seats
				$free_seats = $event_seats - $reservation_quantity;
				
				// Define free seats text
				if ($reservation_quantity >= $event_seats){$free_seats_text = "ausverkauft";}
				else{$free_seats_text = $free_seats."<br /><a href='".get_the_permalink( $post_id_show )."#reservieren' class='btn btn-tobi2' >reservieren</a>";}
				
				//echo esc_attr($a['location']);
				//echo esc_attr($a['show']);
				?>
				
				<div class="text short"><?php echo $day ?></div>
				<div class="text">
					<?php echo $text_event_datetime ?>
				</div>
				<div class="text long">
					<a href="<?php echo get_the_permalink( $post_id_show ) ?>" class="btn btn-tobi2" ><?php echo $text_event_title ?></a>
					<?php // echo $text_event_title ?>
				</div>
				<div class="text"><a href="<?php echo get_the_permalink( $event_location ) ?>" class="btn btn-tobi2" ><?php echo $location->post_title; ?></a></div>
				<div class="text"><?php echo $free_seats_text ?></div>
				
				
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
            $seats .= ' selected="selected"';
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
	
	
	

}
