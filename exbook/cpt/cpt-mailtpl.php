<?php
/* Exit if file access directly */
if ( ! defined( 'ABSPATH' ) ) exit;

// Call an instance from our class
$nwswa_cpt_mailtpl = new nwswa_cpt_mailtpl();

class nwswa_cpt_mailtpl {

	/*
	 * Constructor - the brain of our class
	 * */
	public function __construct() {
		// registriert den neuen custom post type
		add_action( 'init', array( $this, 'register_custom_post_type' ) );
	}

	/*
	 * Register Custom Post Type
	 * Post Type Name = nwswa_mailtpl
	 * */
	public function register_custom_post_type(){
		// Backend string values
		$labels = array(
			'name'               => _x( 'Mailvorlagen', 'post type general name', 'nwswa_exex' ),
			'singular_name'      => _x( 'Mailvorlage', 'post type singular name', 'nwswa_exex' ),
			'add_new'            => __( 'Neue Mailvorlage anlegen', 'nwswa_exex'),
			'add_new_item'       => __( 'Neue Mailvorlage anlegen', 'nwswa_exex' ),
			'edit_item'          => __( 'Mailvorlage Daten bearbeiten', 'nwswa_exex' ),
			'new_item'           => __( 'Neue Mailvorlage', 'nwswa_exex' ),
			'all_items'          => __( 'Alle Mailvorlagen', 'nwswa_exex' ),
			'view_item'          => __( 'Mailvorlage ansehen', 'nwswa_exex' ),
			'search_items'       => __( 'Mailvorlagen durchsuchen', 'nwswa_exex' ),
			'not_found'          => __( 'Keinen Mailvorlage gefunden', 'nwswa_exex' ),
			'not_found_in_trash' => __( 'Keinen Mailvorlage im Papierkorb gefunden', 'nwswa_exex' ),
			'parent_item_colon'  => '',
			'menu_name'          => 'Mailvorlagen'
		);

		// args for the new post_type
		$args = array(
			'public'              => false,
			'mailtpl_ui'             => true,
			'mailtpl_in_menu'        => true,
			'menu_position'       => 10,
			'menu_icon'						=> 'dashicons-email-alt',
			'mailtpl_in_admin_bar'   => true,
			'mailtpl_in_nav_menus'   => true,
			'capability_type'     => 'page',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'supports'            => array( 'title', 'editor' ),
			'has_archive'         => false,
			'can_export'          => true,
			'labels'              => $labels,
		);

		// now register post_type with our args
		register_post_type( 'nwswa_mailtpl', $args );
	}

}
