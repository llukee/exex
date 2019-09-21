<?php
define('WP_USE_THEMES', false);
require('../../../wp-load.php');
global $wpdb;

function mycode_table_column_exists( $table_name, $column_name ) {
	global $wpdb;
	$column = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
		DB_NAME, $table_name, $column_name
	) );
	if ( ! empty( $column ) ) {
		return true;
	}
	return false;
}

/* Skript, um alte Datenbank Einträge in die WordPress Custom Posts einzutragen
 * 1. Bestehende Einträge löschen
 * 2. Standalone Tabellen ohne Verknüpfungen
 * 3. Tabellen mit Verknüpfungen und auslesen der IDs über bestehende Einträge
 */

 /* remove all entries from wp tables with custom post types */
 $results = $wpdb->get_results( "SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE 'nwswa_%'", OBJECT );
 foreach($results as $result) {
   $wpdb->delete("{$wpdb->prefix}postmeta", array('meta_id' => $result->meta_id));
 }

 $results = $wpdb->get_results( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type LIKE 'nwswa_%'", OBJECT );
 foreach($results as $result) {
   $wpdb->delete("{$wpdb->prefix}posts", array('ID' => $result->ID));
 }

 /* tbl: show */
 $show_list = $wpdb->get_results( "SELECT * FROM `show`", OBJECT );
 foreach($show_list as $show) {
   wp_insert_post( array(
       'post_type'     => 'nwswa_show',
       'post_title'    => $show->name,
       'post_status'       =>  'publish',
   ) );
 }

 /* tbl: location */
 $location_list = $wpdb->get_results( "SELECT * FROM location", OBJECT );
 foreach($location_list as $location) {
   wp_insert_post( array(
       'post_type'     => 'nwswa_location',
       'post_title'    => $location->name,
       'post_status'       =>  'publish',
   ) );
 }

 /* tbl: mailtpl */
 $mailtpl_list = $wpdb->get_results( "SELECT * FROM mailtmpl", OBJECT );
 foreach($mailtpl_list as $mailtpl) {
   $nwswa_mailtpl_id = wp_insert_post( array(
       'post_type'     => 'nwswa_mailtpl',
       'post_title'    => $mailtpl->name,
       'post_status'       =>  'publish',
   ) );
   add_post_meta($nwswa_mailtpl_id, 'nwswa_mailtpl_mail_subject', $mailtpl->subject, true);
   add_post_meta($nwswa_mailtpl_id, 'nwswa_mailtpl_mail_content', $mailtpl->message, true);
 }

 /* tbl: event */
 $event_list = $wpdb->get_results( "SELECT * FROM event", OBJECT );
 foreach($event_list as $event) {
   $nwswa_event_id = wp_insert_post( array(
       'post_type'     => 'nwswa_event',
       'post_title'    => 'show: show name ' . $event->datetime,
       'post_status'       =>  'publish',
   ) );
   add_post_meta($nwswa_event_id, 'nwswa_event_datetime', strtotime($event->datetime), true);
   add_post_meta($nwswa_event_id, 'nwswa_event_seats', $event->seats, true);

   $old_show_id = $event->show_id;
   $show_list = $wpdb->get_results( "SELECT * FROM `show` WHERE id='".$old_show_id."' LIMIT 1", OBJECT );
   $show_list = array_pop($show_list);
   if(is_object($show_list)) {
     $nwswa_event_show_list = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_title='".$show_list->name."' AND post_type='nwswa_show' AND post_status='publish' LIMIT 1", OBJECT );
     $nwswa_event_show_list = array_pop($nwswa_event_show_list);
     add_post_meta($nwswa_event_id, 'nwswa_event_show', $nwswa_event_show_list->ID, true);
   }

   $old_location_id = $event->location_id;
   $location_list = $wpdb->get_results( "SELECT * FROM `location` WHERE id='".$old_location_id."' LIMIT 1", OBJECT );
   $location_list = array_pop($location_list);
   if(is_object($location_list)) {
     $nwswa_event_location_list = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_title='".$location_list->name."' AND post_type='nwswa_location' AND post_status='publish' LIMIT 1", OBJECT );
     $nwswa_event_location_list = array_pop($nwswa_event_location_list);
     add_post_meta($nwswa_event_id, 'nwswa_event_location', $nwswa_event_location_list->ID, true);
   }

   $old_mailtpl_id = $event->resmail_id;
   $mailtpl_list = $wpdb->get_results( "SELECT * FROM `mailtmpl` WHERE id='".$old_mailtpl_id."' LIMIT 1", OBJECT );
   $mailtpl_list = array_pop($mailtpl_list);
   if(is_object($mailtpl_list)) {
     $nwswa_event_mailtpl_list = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_title='".$mailtpl_list->name."' AND post_type='nwswa_mailtpl' AND post_status='publish' LIMIT 1", OBJECT );
     $nwswa_event_mailtpl_list = array_pop($nwswa_event_mailtpl_list);
     add_post_meta($nwswa_event_id, 'nwswa_event_mailtpl', $nwswa_event_mailtpl_list->ID, true);
   }

   // check if new_event_id col exists in old table event
   $existing_columns = mycode_table_column_exists("event", "new_event_id");
   if($existing_columns===false) {
     // create new col
     $wpdb->query("ALTER TABLE event ADD new_event_id INT(11) NOT NULL DEFAULT '0'");
   }
   // save new event id in old event table
   $wpdb->update("event", array("new_event_id" => $nwswa_event_id), array("id" => $event->id));

 }

/* tbl: reservation */
$reservation_list = $wpdb->get_results( "SELECT * FROM reservation", OBJECT );
foreach($reservation_list as $reservation) {
  $nwswa_reservation_id = wp_insert_post( array(
      'post_type'     => 'nwswa_reservation',
      'post_title'    => 'event: '.$reservation->event_id.' ' . $reservation->email,
      'post_status'       =>  'publish',
  ) );
  add_post_meta($nwswa_reservation_id, 'nwswa_reservation_firstname', $reservation->firstname, true);
  add_post_meta($nwswa_reservation_id, 'nwswa_reservation_lastname', $reservation->name, true);
  add_post_meta($nwswa_reservation_id, 'nwswa_reservation_phone', $reservation->tel, true);
  add_post_meta($nwswa_reservation_id, 'nwswa_reservation_email', $reservation->email, true);
  add_post_meta($nwswa_reservation_id, 'nwswa_reservation_quantity', $reservation->quantity, true);
  add_post_meta($nwswa_reservation_id, 'nwswa_reservation_status', $reservation->status, true);
  add_post_meta($nwswa_reservation_id, 'nwswa_reservation_newsletter', $reservation->newsletter, true);
  add_post_meta($nwswa_reservation_id, 'nwswa_reservation_memo', $reservation->memo, true);

  $old_event_id = $reservation->event_id;
  $event_list = $wpdb->get_results( "SELECT * FROM `event` WHERE id='".$old_event_id."' LIMIT 1", OBJECT );
  $event_list = array_pop($event_list);
  if(is_object($event_list)) {
    add_post_meta($nwswa_reservation_id, 'nwswa_reservation_event', $event_list->new_event_id, true);
  }
}
