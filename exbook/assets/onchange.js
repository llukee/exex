				(function($) {
							$(document).ready(function() {
							$('.onchange').on('change', function() {
							var $form = $(this).closest('form');
							window.history.replaceState(null, null, window.location.pathname + "#reservieren");
							$form.find('input[type=submit]').click();
							 });
						});
				})( jQuery );
				
				
				(function($) {
					$(document).ready(function(){
						$("#sendmessage").click(function(){
							$("#sendmessage").css("pointer-events","none");
						});
					});
				})( jQuery );