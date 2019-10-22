<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<header class="entry-header">	
	<h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
</header>
	
<div class="entry">
 		<?php the_content(); ?>
</div>
	
<form id="form">
<?php wp_nonce_field( 'contact_form_submit', 'cform_generate_nonce' );?>
            <label>Name</label> <input type="text" name="name" class="text" id="name"><br>
            <label>Email</label> <input type="email" name="email" class="text" id="email"><br>

            <label>Subject</label> <input type="text" name="subject" class="text" id="subject"><br>
            <label>Message</label><textarea id="message" class="textarea" name="message"></textarea>
            <input name="action" type="hidden" value="simple_contact_form_process" />
            <input type="submit" name="submit_form" class="button" value="send Message" id="sendmessage">
            <div class="formmessage"><p></p></div>
        </form>
		
<?php endwhile; else : ?>
	<p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
<?php endif; ?>