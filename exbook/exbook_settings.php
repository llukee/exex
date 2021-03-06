<?php

function exbook_register_settings() {
  add_option( 'exbook_mailchimp_apikey', '');
  register_setting( 'exbook_options', 'exbook_mailchimp_apikey', 'exbook_callback' );
  
  add_option( 'exbook_mailchimp_listid', '');
  register_setting( 'exbook_options', 'exbook_mailchimp_listid', 'exbook_callback' );
  
  add_option( 'exbook_email_sender', '');
  register_setting( 'exbook_options', 'exbook_email_sender', 'exbook_callback' );
}
add_action( 'admin_init', 'exbook_register_settings' );

function exbook_register_options_page() {
  add_options_page('Exbook Einstellungen', 'Exbook', 'manage_options', 'exbook', 'exbook_options_page');
}
add_action('admin_menu', 'exbook_register_options_page');

function exbook_options_page() {
?>
<div>
<h2>Exbook Einstellungen</h2>
<form method="post" action="options.php">
<?php settings_fields( 'exbook_options' ); ?>
<h3>Mailchimp</h3>

<p>Dein API Key findest du nach dem Login bei Mail Chimp unter Account -> Extras -> API keys</p>

<p><a href="https://mailchimp.com/help/find-audience-id/">Wie finde ich die Audience ID?</a></p>
<table>
<tr valign="top">
<th scope="row"><label for="exbook_mailchimp_apikey">API Key</label></th>
<td><input type="text" id="exbook_mailchimp_apikey" name="exbook_mailchimp_apikey" value="<?php echo get_option('exbook_mailchimp_apikey'); ?>" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="exbook_mailchimp_listid">Audience ID</label></th>
<td><input type="text" id="exbook_mailchimp_listid" name="exbook_mailchimp_listid" value="<?php echo get_option('exbook_mailchimp_listid'); ?>" /></td>
</tr>

</table>

<h3>General</h3>
<table>
	<tr valign="top">
		<th scope="row"><label for="exbook_email_sender">E-mail sender</label></th>
		<td><input type="text" id="exbook_email_sender" name="exbook_email_sender" value="<?php echo get_option('exbook_email_sender'); ?>" /></td>
	</tr>
</table>

<?php submit_button(); ?>
</form>
</div>
<?php
}
