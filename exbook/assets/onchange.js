<script>
						$(document).ready(function() {
							$('#reservation_event').on('change', function() {
							var $form = $(this).closest('form');
							$form.find('input[type=submit]').click();
							 });
						});
</script>