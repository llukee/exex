<?php
	/* PDF Template */

	global $post_id;
	global $post;
	global $pdf_content_html;
	global $pdf_header;
	global $pdf_footer;

	/*
	 * get the original post id
	 */
	$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
	/*
	 * and all the original post data then
	 */
	$post_event = get_post( $post_id );

	$event_show = get_post_meta( $post_id, 'nwswa_event_show', true );
	$show = get_post($event_show);

	$event_datetime = get_post_meta( $post_id, 'nwswa_event_datetime', true );


	$args_nwswa_reservations = array (
		// Post or Page ID
		'post_type' => 'nwswa_reservation',
		'meta_key'  => 'nwswa_reservation_event',
		'orderby' => 'nwswa_reservation_lastname',
		'order' => 'ASC',
		'posts_per_page'    => -1,
		'meta_query' => array(
						'relation' => 'AND',
						'post_id' => array(
							'key'     => 'nwswa_reservation_event',
							'value' => $post_id,
							'compare' => '=',
						),
						'post_status' => array(
							'key'     => 'nwswa_reservation_status',
							'value' => 'storniert',
							'compare' => '!=',
						), 
					),
	);

	// get all reservations
	$query_nwswa_reservations = new WP_Query( $args_nwswa_reservations );



	//Set the Footer and the Header
	$pdf_header = array (
  		'odd' =>
  			array (
    			'R' =>
   					array (
						'content' => '{PAGENO}',
						'font-size' => 8,
						'font-style' => 'B',
						'font-family' => 'DejaVuSansCondensed',
    				),
    				'line' => 1,
  				),
  		'even' =>
  			array (
    			'R' =>
    				array (
						'content' => '{PAGENO}',
						'font-size' => 8,
						'font-style' => 'B',
						'font-family' => 'DejaVuSansCondensed',
    				),
    				'line' => 1,
  			),
	);
	$pdf_footer = array (
	  	'odd' =>
	 	 	array (
	    		'R' =>
	    			array (
						'content' => '{DATE d.m.Y}',
					    'font-size' => 8,
					    'font-style' => 'BI',
					    'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'C' =>
	    			array (
	      				'content' => '- {PAGENO} / {nb} -',
	      				'font-size' => 8,
	      				'font-style' => '',
	      				'font-family' => '',
	    			),
	    		'L' =>
	    			array (
	      				'content' => 'Copyright © '.'{DATE Y} '.get_bloginfo('name'),
	      				'font-size' => 8,
	      				'font-style' => 'BI',
	      				'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'line' => 1,
	  		),
	  	'even' =>
			array (
	    		'R' =>
	    			array (
						'content' => '{DATE d.m.Y}',
					    'font-size' => 8,
					    'font-style' => 'BI',
					    'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'C' =>
	    			array (
	      				'content' => '- {PAGENO} / {nb} -',
	      				'font-size' => 8,
	      				'font-style' => '',
	      				'font-family' => '',
	    			),
	    		'L' =>
	    			array (
	      				'content' => 'Copyright © '.'{DATE Y} '.get_bloginfo('name'),
	      				'font-size' => 8,
	      				'font-style' => 'BI',
	      				'font-family' => 'DejaVuSansCondensed',
	    			),
	    		'line' => 1,
	  		),
	);

	$pdf_content_html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html xml:lang="en">

		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<title>' . get_bloginfo() . '</title>
		</head>
		<body xml:lang="en">
			<bookmark content="'.htmlspecialchars(get_bloginfo('name'), ENT_QUOTES).'" level="0" /><tocentry content="'.htmlspecialchars(get_bloginfo('name'), ENT_QUOTES).'" level="0" />
			<div id="content" class="widecolumn">';

		$pdf_content_html .= sprintf("
		<h1>Reservationen für die Veranstaltung '%s um %s'</h1>
		", $show->post_title, date("d.m.Y H:i", $event_datetime));

		$table_rows = array();
		$total_reservations_amount = 0;
		// loop reservations
		if ( $query_nwswa_reservations->have_posts() ) {
			while ( $query_nwswa_reservations->have_posts() ) {
				$query_nwswa_reservations->the_post();
				$reservation_id = get_the_ID();

				$table_rows[] = array(
					'Vorname' => get_post_meta($reservation_id, 'nwswa_reservation_firstname', true),
					'Nachname' => get_post_meta($reservation_id, 'nwswa_reservation_lastname', true),
					'Telefonnummer' => get_post_meta($reservation_id, 'nwswa_reservation_phone', true),
					'E-Mail' => get_post_meta($reservation_id, 'nwswa_reservation_email', true),
					'Kommentar' => get_post_meta($reservation_id, 'nwswa_reservation_memo', true),
					'Anzahl Plätze' => get_post_meta($reservation_id, 'nwswa_reservation_quantity', true),
				);

				$total_reservations_amount+= get_post_meta($reservation_id, 'nwswa_reservation_quantity', true);
			}
		}

		$pdf_content_html .= '<table id="reservations_table"><tr>';

		if(is_array($table_rows) && count($table_rows)) {
			$heading = false;
			foreach($table_rows as $table_row) {
				if($heading === false) {
					$pdf_content_html .= '<thead><tr><th>'.implode('</th><th>', array_keys($table_row)).'</th></tr></thead><tbody>';
					$heading = true;
				}
				$pdf_content_html .= '<tr><td>'.implode('</td><td>', $table_row).'</td></tr>';
			}
		}

		$pdf_content_html .= '</tbody><tfoot>';

		$table_row_total = array(
			'','','','',$total_reservations_amount
		);

		$pdf_content_html .= '<tr><th>'.implode('</th><th>', $table_row_total).'</th></tr>';

		$pdf_content_html .= '</tfoot></table>';

		$pdf_content_html .= '</div> <!--content-->';


	$pdf_content_html .= '
		</body>
		</html>';
?>
