jQuery(document).ready(function(){
	( function($) {

		// If there are no forms, show the modal
		if ( $('.post-type-wpzf-form #the-list').find('.no-items').length > 0 ) {
			$('#wpzoom-forms-modal').css('display', 'flex');
		}

		// Show modal on click of "Add New" button
		$('.wpzf_settings-add-new a, #menu-posts-wpzf-form a[href$="post-new.php?post_type=wpzf-form"]').on('click', function(e){
			e.preventDefault();
			$('#wpzoom-forms-modal').css('display', 'flex');
		});

		// Get base URL for creating new forms
		var baseUrl = $('#wpzoom-forms-modal').data('new-post-url');

		// Direct template selection - click to proceed immediately
		$('.wpzoom-forms-templates-list li:not(.wpzoom-forms-template-list-item-cta)').on('click', function(e){
			e.preventDefault();
			
			var $item = $(this);
			var $link = $item.find('a');
			var templateId = $link.data('template-id');
			
			// Check if this is a pro template
			if ( $item.hasClass('wpzoom-forms-template-list-item-pro') ) {
				// Redirect to pro upgrade page
				window.open('https://www.wpzoom.com/plugins/wpzoom-forms/?utm_source=wpadmin&utm_medium=wpzoom-forms-free&utm_campaign=pro-template-' + templateId, '_blank');
				return;
			}
			
			// Build URL with template parameter and navigate
			if ( templateId && baseUrl ) {
				var url = new URL( baseUrl );
				url.searchParams.set( 'template', templateId );
				window.location.href = url.toString();
			}
		});

		// Close modal
		$('#wpzoom_close_modal, .wpzoom-forms-modal-close').on('click', function(e){
			e.preventDefault();
			$('#wpzoom-forms-modal').css('display', 'none');
		});

		$(document).keydown(function(evt) {
			evt = evt || window.event;
			var isEscape = false;
			if ("key" in evt) {
				isEscape = (evt.key === "Escape" || evt.key === "Esc");
			} else {
				isEscape = (evt.keyCode === 27);
			}
			if (isEscape) {
				$('#wpzoom-forms-modal').css('display', 'none');
			}
		});

	})(jQuery);
});