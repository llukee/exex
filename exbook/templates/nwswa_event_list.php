
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
		while ( $events_list->have_posts() ) : $events_list->the_post();
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

			$arguments_reservation_list = array (
				// Post or Page ID
				'post_type' => 'nwswa_reservation',
				'meta_key'  => 'nwswa_reservation_event',
				'meta_value' => $post_id,
				'meta_compare' => '='
			);

			// The Query
			$reservations_list = new WP_Query( $arguments_reservation_list );

			// The Loop
			if ( $reservations_list->have_posts() ) {

				while ( $reservations_list->have_posts() ) {
					$reservations_list->the_post();
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
				else{$free_seats_text = $free_seats."<br /><a href='".get_the_permalink( $post_id_show )."?event_id='.$post_id.'#reservieren' class='btn btn-tobi2' >reservieren</a>";}

				//echo esc_attr($arguments_event_list]'location']);
				//echo esc_attr($arguments_event_list['show']);
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
