				(function($) {
							$(document).ready(function() {
							$('.onchange').on('change', function() {
							var $form = $(this).closest('form');
							window.history.pushState({}, document.title, "/");
							$form.find('input[type=submit]').click();
							 });
						});
				})( jQuery );
