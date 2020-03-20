<?php
/**
 * The template for displaying all single shows
 */
						if (isset($_GET['event_id']) && $_GET['event_id']>0) {
						 $reservation_event = $_GET['event_id'];
				}
				
get_header();
?>
<style>
  #reservation label {
    display: block;
  }
  #reservation .text {
    width: 60%;
    min-width: 250px;
    padding: 0.36rem 0.66rem;
  }
  #reservation_event {
    width: 60%;
    min-width: 250px;
    padding: 0.36rem 0.66rem;
  }
</style>

<section id="primary" class="content-area">
		<main id="main" class="site-main">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<header class="entry-header">
					<h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
				</header>

				<div class="entry-content">
					<?php the_content(); ?>

				<h2 id="reservieren">Reservieren</h2>
				<?php echo ($message_html); ?>
				<?php echo ($message_mailchimp_html); ?>
			

				<?php
				// Check if there are events in the future. If true, display registration form.
						$post_id = get_the_ID();
						$args_ = array(
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
										'key' => 'nwswa_event_show',
										'value' => $post_id,
									)
								),
							);
						$query = new WP_Query( $args_ );




						if ( $query->have_posts() && $formular_sent != 'true' ) { ?>

					
					<form id="reservation" name="contact-form" action="" method="post">


					<?php wp_nonce_field( 'SubmitButton', 'cform_generate_nonce' );?>

					<p><label for="reservation_event">Vorstellung:</label>

					
					
					<select id="reservation_event" class="onchange" name="reservation_event">

						<?php
						// Query the shows here
						$post_id = get_the_ID();
						$args = array(
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
										'key' => 'nwswa_event_show',
										'value' => $post_id,
									)
								),
							'orderby' => 'date_ordering',
							'order' => 'ASC',
							);
						$query = new WP_Query( $args );
						while ( $query->have_posts() ) {



									$option_text = '';
									$query->the_post();
									$event_id = get_the_ID();


									//////////////
									// Start
									// Chek if there are free seats available
									//////////

									// Get total seats
									$event_seats = get_post_meta( $event_id, 'nwswa_event_seats', true );

									// Get number of reservations
									//$reservation_quantity = (int)get_post_meta( $post_id, 'reservation_quantity', true );

									$args = array (
									// Post or Page ID
									'post_type' => 'nwswa_reservation',
									'meta_key'  => 'nwswa_reservation_event',
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

									// The Query
									$the_query = new WP_Query( $args );

									// The Loop
									if ( $the_query->have_posts() ) {

										while ( $the_query->have_posts() ) {
											$the_query->the_post();
											$nwswa_reservation_quantity= get_post_meta( get_the_ID(), 'nwswa_reservation_quantity', true);
											$reservation_quantity += (int)$nwswa_reservation_quantity;
										}

									}

									else {

										$reservation_quantity = 0;

									}
									
										// Restore original Post Data
										wp_reset_postdata();

									// Calculate free seats
									$free_seats = $event_seats - $reservation_quantity;

									// Create reservation text
									if ($reservation_quantity >= $event_seats){$free_seats_text = "ausverkauft";}
									else{$free_seats_text = "Freie Plätze: ".$free_seats;}
									
									$reservation_quantity = 0; 
									
									

									//////////////
									// End
									// Chek if there are free seats available
									//////////


									// show title + event datetime
									$show_id = get_post_meta( $event_id, 'nwswa_event_show', true );
									$show = get_post($show_id);
									$datetime_ts = get_post_meta( $event_id, 'nwswa_event_datetime', true );

									$option_text .= $show->post_title;
									$option_text .= ' - ';
									$option_text .= date("d.m.Y H:i", $datetime_ts);
									$option_text .= ' - ';
									$option_text .= $free_seats_text;

							$selected = "";

							if($event_id == $event) {
								$selected = ' selected="selected"';
							}
							if ($free_seats_text == "ausverkauft"){
										continue;
							} else {
								if ($reservation_event == $event_id) {
									$selected_text = 'selected="'.$event_id.'"';
								} else {
									$selected_text = '';
								}
								echo '<option' . $selected . ' value=' . $event_id . ' '.$selected_text .'>' . $option_text . '</option>';
							}
						}
					  ?>
					  </select></p>



								<p><label>Vorname</label> <input type="text" value="<?php echo $reservation_firstname; ?>" name="reservation_firstname" class="text" id="vorname"></p>
								<p><label>Nachname</label> <input type="text" value="<?php echo $reservation_lastname; ?>" name="reservation_lastname" class="text" id="nachname"></p>
								<p><label>Telefon</label> <input type="text" value="<?php echo $reservation_phone; ?>" name="reservation_phone" class="text" id="telefon"></p>
								<p><label>E-Mail</label> <input type="email" value="<?php echo $reservation_email; ?>" name="reservation_email" class="text" id="email"></p>

								<p><label for="reservation_quantity" value="text">Anzahl Pl&auml;tze:</label>

								<?php
								// Check how many seats are available for selected event
								
								
								// Get total seats
								$event_seats_select = get_post_meta( $reservation_event, 'nwswa_event_seats', true );
								
								
								// Calculate reservation quanitity basel on selected event
								$args = array (
									// Post or Page ID
									'post_type' => 'nwswa_reservation',
									
									'meta_query' => array(
										'relation' => 'AND',
										'post_id' => array(
											'key'     => 'nwswa_reservation_event',
											'value' => $reservation_event,
											'compare' => '=',
										),
										'post_status' => array(
											'key'     => 'nwswa_reservation_status',
											'value' => 'storniert',
											'compare' => '!=',
										), 
									)
									);

									// The Query
									$the_query = new WP_Query( $args );

									// The Loop
									if ( $the_query->have_posts() ) {

										while ( $the_query->have_posts() ) {
											$the_query->the_post();
											$nwswa_reservation_quantity = get_post_meta( get_the_ID(), 'nwswa_reservation_quantity', true);
											$reservation_quantity_select += (int)$nwswa_reservation_quantity;
										}

									}
									

								// Calculate free seats
								$free_seats_select = $event_seats_select - $reservation_quantity_select;

								


								?>
									<select name="reservation_quantity">
									 <?php for($q=1;$q<=$free_seats_select;$q++) {
										$selected = '';
										if($q==$quantity) {
											$selected = ' selected="selected" ';
										}
										echo '<option value="'.$q.'" '.$selected.'>'.$q.'</option>';
									} ?>
									</select></p>

								<p><label>News abonnieren?</label> <input type="checkbox"name="reservation_newsletter" checked="checked" value="1"></input></p>

								<p><label>Bitte löse folgende Rechnungsaufgabe: <br />Was ergibt 2 x 4?</label> <input type="text" value="<?php echo $security_check; ?>" name="security_check" class="text" id="security_check"></p>

								<p><input type="submit" name="SubmitButton" class="button" value="Reservierung absenden" id="sendmessage"></p>

							</form>
					<?php } ?>




					<?php endwhile; else : ?>
						<p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
					<?php endif; ?>
				</div>
			</article><!-- #post-<?php the_ID(); ?> -->
		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();
