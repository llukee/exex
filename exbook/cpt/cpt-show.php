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
		    if ( file_exists( plugin_dir_path( __FILE__ ) . '/templates/'.$post->post_type.'_single.php' ) ) {
			    return plugin_dir_path( __FILE__ ) . '/templates/'.$post->post_type.'_single.php';
		    }
		}

		return $single;
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
