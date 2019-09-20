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
		add_action( 'init', array( $this, '_register' ) );
		// Add metabox for custom fields
		add_action( 'add_meta_boxes', array($this, '_add' ));
		// Add metabox save function for custom fields
		add_action( 'save_post', array($this, '_save' ), 10, 3);
		// Set columns in list view admin
		add_action('manage_nwswa_mailtpl_posts_columns', array($this, '_add_columns'), 10, 2);
		add_action('manage_nwswa_mailtpl_posts_custom_column', array($this, '_fill_columns'), 10, 2);
	}

	/*
	 * Register Custom Post Type
	 * Post Type Name = nwswa_mailtpl
	 * */
	public function _register(){
		// Backend string values
		$labels = array(
			'name'               => _x( 'Mailvorlagen', 'post type general name', 'nwswa_exbook' ),
			'singular_name'      => _x( 'Mailvorlage', 'post type singular name', 'nwswa_exbook' ),
			'add_new'            => __( 'Neue Mailvorlage anlegen', 'nwswa_exbook'),
			'add_new_item'       => __( 'Neue Mailvorlage anlegen', 'nwswa_exbook' ),
			'edit_item'          => __( 'Mailvorlage Daten bearbeiten', 'nwswa_exbook' ),
			'new_item'           => __( 'Neue Mailvorlage', 'nwswa_exbook' ),
			'all_items'          => __( 'Alle Mailvorlagen', 'nwswa_exbook' ),
			'view_item'          => __( 'Mailvorlage ansehen', 'nwswa_exbook' ),
			'search_items'       => __( 'Mailvorlagen durchsuchen', 'nwswa_exbook' ),
			'not_found'          => __( 'Keinen Mailvorlage gefunden', 'nwswa_exbook' ),
			'not_found_in_trash' => __( 'Keinen Mailvorlage im Papierkorb gefunden', 'nwswa_exbook' ),
			'parent_item_colon'  => '',
			'menu_name'          => 'Mailvorlagen'
		);

		// args for the new post_type
		$args = array(
			'public'              => true,
			'mailtpl_ui'             => true,
			'mailtpl_in_menu'        => true,
			'menu_position'       => 10,
			'menu_icon'						=> 'dashicons-email-alt',
			'mailtpl_in_admin_bar'   => true,
			'mailtpl_in_nav_menus'   => true,
			'capability_type'     => 'page',
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'supports'            => false,
			'has_archive'         => false,
			'can_export'          => true,
			'labels'              => $labels,
		);

		// now register post_type with our args
		register_post_type( 'nwswa_mailtpl', $args );
	}

	/*
	 * add metaboxes */
	public function _add() {
		add_meta_box(
			'nwswa_mailtpl_metabox',
			'Mailvorlage Daten',
			array($this, 'nwswa_mailtpl_metabox'),
			'nwswa_mailtpl',
			'normal',
			'default'
		);
	}

	/**
	 * Output the HTML for the metabox.
	 */
	public function nwswa_mailtpl_metabox($post, $args) {
    global $post, $wp_locale;
		// Nonce field to validate form request came from current site
		wp_nonce_field( plugin_basename( __FILE__ ), 'nwswa_mailtpl_fields' );

		// Output the fields
		$mail_subject = get_post_meta( $post->ID, 'nwswa_mailtpl_mail_subject', true );
		$mail_content = get_post_meta( $post->ID, 'nwswa_mailtpl_mail_content', true );

		echo '<p><label for="post_title">Name:</label>';
		echo '<input type="text" name="post_title" id="post_title" value="' . $post->post_title . '" />';
		echo '</p>';

		echo '<p><label for="mail_subject">E-Mail Betreff:</label>';
		echo '<input type="text" name="mail_subject" id="mail_subject" value="' . $mail_subject . '" />';
		echo '</p>';

		echo '<p><label for="content">E-Mail Nachricht:</label>';
		echo '<textarea name="mail_content" id="mail_content" cols="50" rows="10">' . $mail_content . '</textarea>';
		echo '</p>';

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
		if ( "nwswa_mailtpl" != $post_type ) return;

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
		if ( ! isset( $_POST['nwswa_mailtpl_fields'] ) || ! wp_verify_nonce( $_POST['nwswa_mailtpl_fields'], plugin_basename(__FILE__) ) ) {
			return $post_id;
		}

		$mailtpl_meta = array(
			'mail_subject',
			'mail_content',
		);

		foreach($mailtpl_meta as $mailtpl_meta_key) {
			$key = 'nwswa_mailtpl_'.$mailtpl_meta_key;
			$value = esc_textarea($_POST[$mailtpl_meta_key]);
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
		$columns['title'] = 'Name';
		$columns['mail_subject'] = __('E-Mail Betreff', 'nwswa_exbook');
		$columns['mail_content'] = __('E-Mail Nachricht', 'nwswa_exbook');
		return $columns;
	}

	public function _fill_columns($column_name, $post_id) {
    global $wpdb;
    switch ($column_name) {
        case 'mail_subject':
					$mail_subject = get_post_meta( $post_id, 'nwswa_mailtpl_mail_subject', true );
					echo $mail_subject;
            break;
				case 'mail_content':
					$mail_content = get_post_meta( $post_id, 'nwswa_mailtpl_mail_content', true );
					echo $mail_content;
            break;
        default:
            break;
    }
}

	private function _debug($exit=true){
  	var_dump(debug_backtrace());
    return ($exit)? exit(): false;
	}

}
