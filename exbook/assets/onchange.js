<script>
				(function($) {
							$(document).ready(function() {
							$('.onchange').on('change', function() {
							var $form = $(this).closest('form');
							$form.find('input[type=submit]').click();
							 });
						});
				})( jQuery );
</script>