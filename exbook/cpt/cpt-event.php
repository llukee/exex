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
		// Add metabox for custom fields
		add_action( 'add_meta_boxes', array($this, 'custom_post_type_add_metabox' ));
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
	public function events_list() {

		// Loop Arguments
		$args = array(
			'post_type'         => 'nwswa_event',
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
		$metabox_id = $args['args']['id'];

		/***
		* FIELD DATETIME
		*/
    $time_adj = current_time( 'timestamp' );
    $month = get_post_meta( $post->ID, $metabox_id . '_month', true );

    if ( empty( $month ) ) {
        $month = gmdate( 'm', $time_adj );
    }

    $day = get_post_meta( $post->ID, $metabox_id . '_day', true );

    if ( empty( $day ) ) {
        $day = gmdate( 'd', $time_adj );
    }

    $year = get_post_meta( $post->ID, $metabox_id . '_year', true );

    if ( empty( $year ) ) {
        $year = gmdate( 'Y', $time_adj );
    }

    $hour = get_post_meta($post->ID, $metabox_id . '_hour', true);

    if ( empty($hour) ) {
        $hour = gmdate( 'H', $time_adj );
    }

    $min = get_post_meta($post->ID, $metabox_id . '_minute', true);

    if ( empty($min) ) {
        $min = '00';
    }

		echo '<p><label for="field_id">Datum und Uhrzeit:</label>';

		echo '<input type="text" name="' . $metabox_id . '_day" value="' . $day  . '" size="2" maxlength="2" />';

    $month_s = '<select name="' . $metabox_id . '_month">';
    for ( $i = 1; $i < 13; $i = $i +1 ) {
        $month_s .= "\t\t\t" . '<option value="' . zeroise( $i, 2 ) . '"';
        if ( $i == $month )
            $month_s .= ' selected="selected"';
        $month_s .= '>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
    }
    $month_s .= '</select>';

    echo $month_s;
    echo '<input type="text" name="' . $metabox_id . '_year" value="' . $year . '" size="4" maxlength="4" /> um ';
    echo '<input type="text" name="' . $metabox_id . '_hour" value="' . $hour . '" size="2" maxlength="2"/>:';
    echo '<input type="text" name="' . $metabox_id . '_minute" value="' . $min . '" size="2" maxlength="2" />';

		echo '</p>';

		/***
		* FIELD SEATS
		*/
		$seats = get_post_meta( $post->ID, $metabox_id . '_seats', true );
		if ( empty( $seats ) ) {
				$seats = 30;
		}

		$seats_html = '<p><label for="field_id">Plätze:</label>';

		$seats_html .= '<select name="' . $metabox_id . '_seats">';
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
		$show_id = get_post_meta($post->ID, 'nwswa_show', true);

    echo '<p><label for="nwswa_show">Veranstaltung:</label>';
    echo '<select id="nwswa_show" name="nwswa_show">';
    // Query the shows here
    $query = new WP_Query( 'post_type=nwswa_show' );
    while ( $query->have_posts() ) {
        $query->the_post();
        $id = get_the_ID();
        $selected = "";

        if($id == $author_id){
            $selected = ' selected="selected"';
        }
        echo '<option' . $selected . ' value=' . $id . '>' . get_the_title() . '</option>';
    }
    echo '</select></p>';

		/***
		* FIELD Connection to CPT Location
		*/
		$location_id = get_post_meta($post->ID, 'nwswa_location', true);

    echo '<p><label for="nwswa_location">Standort:</label>';
    echo '<select id="nwswa_location" name="nwswa_location">';
    // Query the locations here
    $query = new WP_Query( 'post_type=nwswa_location' );
    while ( $query->have_posts() ) {
        $query->the_post();
        $id = get_the_ID();
        $selected = "";

        if($id == $author_id){
            $selected = ' selected="selected"';
        }
        echo '<option' . $selected . ' value=' . $id . '>' . get_the_title() . '</option>';
    }
    echo '</select></p>';

		/***
		* FIELD Connection to CPT Mailvorlage
		*/
		$mailtpl_id = get_post_meta($post->ID, 'nwswa_mailtpl', true);

    echo '<p><label for="nwswa_mailtpl">Mailvorlage:</label>';
    echo '<select id="nwswa_mailtpl" name="nwswa_mailtpl">';
    // Query the mailtpls here
    $query = new WP_Query( 'post_type=nwswa_mailtpl' );
    while ( $query->have_posts() ) {
        $query->the_post();
        $id = get_the_ID();
        $selected = "";

        if($id == $author_id){
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

}
