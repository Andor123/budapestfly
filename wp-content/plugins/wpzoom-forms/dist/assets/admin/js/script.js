jQuery(document).ready(function () {
	(function ($, Settings) {
		$('.wp-tab-bar a').click(function (event) {
			event.preventDefault();

			var href = $(this).attr('href');
			var query_args = getUrlVars(href);
			var context = $(this).closest('.wp-tab-bar').parent(); // Limit effect to the container element.

			$('.wp-tab-bar li', context).removeClass('wp-tab-active');
			$(this).closest('li').addClass('wp-tab-active');
			$('.wp-tab-panel', context).hide();
			$('#' + query_args['tab'], context).show();

			// Change url depending by active tab
			window.history.pushState('', '', href);
		});

		// Make setting wp-tab-active optional.
		$('.wp-tab-bar').each(function () {
			if ($('.wp-tab-active', this).length)
				$('.wp-tab-active', this).click();
			else
				$('a', this).first().click();
		});

		// reset settings to defaults
		$('#wpzoom_forms_reset_settings').click(function () {
			var data = {
				security: Settings.ajax_nonce,
				action: 'wpzoom_reset_settings',
			};

			if (window.confirm("Do you really want to Reset all settings to default?")) {
				$.post(Settings.ajaxUrl, data, function (response) {
					if (response.success) {
						var query_args = getUrlVars(window.location.href);

						if (query_args.length > 0) {
							window.location.href = window.location.href + "&wpzoom_reset_settings=1";
						} else {
							window.location.href = window.location.href + "?wpzoom_reset_settings=1";
						}
					} else {
						alert('Something went wrong when tried to reset the settings!')
					}
				});
			}
		});

		function getUrlVars($url) {
			var vars = [], hash;
			var hashes = $url.slice($url.indexOf('?') + 1).split('&');
			for (var i = 0; i < hashes.length; i++) {
				hash = hashes[i].split('=');
				vars.push(hash[0]);
				vars[hash[0]] = hash[1];
			}
			return vars;
		}

		// setting field preview
		$('.wpzoom-forms-field-preview').each(function () {
			var $this = $(this),
				$field = $(this).parents('fieldset');
			var thumbnail = $(this).data('preview-thumbnail'),
				position = $(this).data('preview-position');

			$(this).on('mouseover', function () {

				if ($this.hasClass('active')) {
					$this.removeClass('active');
					$field.find('.wpzoom-forms-field-preview-thumbnail').remove();
					return;
				}

				$this.addClass('active');
				$field.append('<span class="wpzoom-forms-field-preview-thumbnail preview-position-' + position + '"><img src="' + thumbnail + '" width="400" height="300"></span>');

				$('.wpzoom-forms-field-preview').not(this).parent().find('.wpzoom-forms-field-preview-thumbnail').remove();
				$('.wpzoom-forms-field-preview').not(this).removeClass('active');

			});

			$(this).on('mouseout', function () {

				if ($this.hasClass('active')) {
					$this.removeClass('active');
					$field.find('.wpzoom-forms-field-preview-thumbnail').remove();
					return;
				}

			});

		});

		// Add Color Picker to all inputs that have 'color-field' class
		$('.wpzoom-forms-color-picker').wpColorPicker({
			change: function (event, ui) {
				var $this = $(this);
				setTimeout(function () {
					$this.val(ui.color.toString().toUpperCase()) // uppercase color value
				}, 1);
			}
		});

		$('.wp-tab-bar a').on('click', function() {
			$this = $(this);
			const url = window.location.href;
			const urlParams = new URLSearchParams(url);
			const tab = urlParams.get('tab');
			if (tab === 'tab-ajax') {
				$('.wpzoom-forms-settings-ajax-promo-container').css('display', 'inline-block');
			} else {
				$('.wpzoom-forms-settings-ajax-promo-container').hide();
			}
		});

		function showCaptchaOptions() {
			const selectedCaptcha = $('input[name="wpzf-settings[wpzf_global_captcha_service]"]:checked').val();
			const selectedRecaptchaVersion = $('input[name="wpzf-settings[wpzf_global_captcha_type]"]:checked').val();
			
			if (selectedCaptcha === 'recaptcha') {
				$('tr.required-recaptcha').css('display', 'block');
				if (selectedRecaptchaVersion === 'v2') {
					$('tr.required-recaptcha-v3').hide();
				} else {
					$('tr.required-recaptcha-v2').hide();
				}
				$('tr.required-turnstile').hide();
			} else if (selectedCaptcha === 'turnstile') {
				$('tr.required-recaptcha').hide();
				$('tr.required-turnstile').css('display', 'block');
			} else {
				$('tr.required-recaptcha').hide();
				$('tr.required-turnstile').hide();
			}
		}

		showCaptchaOptions();

		//Show reCaptcha options only when it is selected
		$('input[name="wpzf-settings[wpzf_global_captcha_service]"]').on('change', showCaptchaOptions);
		$('input[name="wpzf-settings[wpzf_global_captcha_type]"]').on('change', showCaptchaOptions);

	})(jQuery, WPZOOM_Settings);
});