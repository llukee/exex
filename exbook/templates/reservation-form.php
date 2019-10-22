					<form id="reservieren">
					<h2>Reservieren</h2>
					<?php wp_nonce_field( 'contact_form_submit', 'cform_generate_nonce' );?>

					<p><label for="reservation_event">Vorstellung:</label>
					<select id="reservation_event" name="reservation_event">
						
						<?php 
						// Query the shows here
						$query = new WP_Query( 'post_type=nwswa_event' );
						while ( $query->have_posts() ) {
									$option_text = '';
							$query->the_post();
									$event_id = get_the_ID();

									// show title + event datetime
									$show_id = get_post_meta( $event_id, 'nwswa_event_show', true );
									$show = get_post($show_id);
									$datetime_ts = get_post_meta( $event_id, 'nwswa_event_datetime', true );

									$option_text .= $show->post_title;
									$option_text .= ' - ';
									$option_text .= date("d.m.Y H:i", $datetime_ts);

							$selected = "";

							if($event_id == $event){
								$selected = ' selected="selected"';
							}
							echo '<option' . $selected . ' value=' . $event_id . '>' . $option_text . '</option>';
						}
					  ?>
					  </select></p>

								<p><label>Vorname</label> <input type="text" name="vorname" class="text" id="vorname"></p>
								<p><label>Nachname</label> <input type="text" name="nachname" class="text" id="nachname"></p>
								<p><label>Telefon</label> <input type="text" name="telefon" class="text" id="telefon"></p>
								<p><label>Email</label> <input type="email" name="email" class="text" id="email"></p>
								
								<p><label for="reservation_quantity">Anzahl Pl&auml;tze:</label>
									<select name="reservation_quantity">
									 <?php for($q=1;$q<=100;$q++) {
										$selected = '';
										if($q==$quantity) {
											$selected = ' selected="selected" ';
										}
										echo '<option value="'.$q.'" '.$selected.'>'.$q.'</option>';
									} ?>
									</select></p>
								
								<p><label>News abonnieren?</label> <input type="checkbox"name="reservation_newsletter" checked="checked"></input></p>
								
								<input name="action" type="hidden" value="simple_contact_form_process" />
								<p><input type="submit" name="submit_form" class="button" value="Reservierung absenden" id="sendmessage"></p>
								
								<div class="formmessage"><p>Das Formular wurde erfolgreich gesandt.</p></div>
							</form>