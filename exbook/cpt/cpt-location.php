<?php
/* Exit if file access directly */
if ( ! defined( 'ABSPATH' ) ) exit;

// Call an instance from our class
$nwswa_cpt_location = new nwswa_cpt_location();

class nwswa_cpt_location {

	/*
	 * Constructor - the brain of our class
	 * */
	public function __construct() {
		// registriert den neuen custom post type
		add_action( 'init', array( $this, 'register_custom_post_type' ) );
		// Shortcode für die Ausgabe aller Standorte
		add_shortcode('locations-list', array( $this, 'locations_list' ));
		// Post Template Mapping
		add_filter('single_template', array( $this, 'custom_post_type_single_mapping' ));
	}

	/*
	 * Register Custom Post Type
	 * Post Type Name = nwswa_location
	 * */
	public function register_custom_post_type(){
		// Backend string values
		$labels = array(
			'name'               => _x( 'Standorte', 'post type general name', 'nwswa_exex' ),
			'singular_name'      => _x( 'Standort', 'post type singular name', 'nwswa_exex' ),
			'add_new'            => __( 'Neuen Standort anlegen', 'nwswa_exex'),
			'add_new_item'       => __( 'Neuen Standort anlegen', 'nwswa_exex' ),
			'edit_item'          => __( 'Standort Daten bearbeiten', 'nwswa_exex' ),
			'new_item'           => __( 'Neuer Standort', 'nwswa_exex' ),
			'all_items'          => __( 'Alle Standorte', 'nwswa_exex' ),
			'view_item'          => __( 'Standort ansehen', 'nwswa_exex' ),
			'search_items'       => __( 'Standorte durchsuchen', 'nwswa_exex' ),
			'not_found'          => __( 'Keinen Standort gefunden', 'nwswa_exex' ),
			'not_found_in_trash' => __( 'Keinen Standort im Papierkorb gefunden', 'nwswa_exex' ),
			'parent_item_colon'  => '',
			'menu_name'          => 'Standorte'
		);

		// args for the new post_type
		$args = array(
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 10,
			'menu_icon'						=> 'dashicons-location',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'capability_type'     => 'page',
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'supports'            => array( 'title', 'editor' ),
			'has_archive'         => false,
			'can_export'          => true,
			'rewrite'             => array('slug' => 'ort' ),
			'labels'              => $labels,
		);

		// now register post_type with our args
		register_post_type( 'nwswa_location', $args );
	}

	/*
	 * Checks if post is from our Post Type
	 * if so, we return our custom single template
	 * */
	public function custom_post_type_single_mapping($single) {

		global $post;

		if ( $post->post_type == 'nwswa_location' ) {
		    if ( file_exists( plugin_dir_path( __FILE__ ) . '/templates/'.$post->post_type.'_single.php' ) ) {
			    return plugin_dir_path( __FILE__ ) . '/templates/'.$post->post_type.'_single.php';
		    }
		}

		return $single;
	}

	/*
	 * Creates the shortcode to display all locations
	 * */
	public function locations_list() {

		// Loop Arguments
		$args = array(
			'post_type'         => 'nwswa_location',
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

}
