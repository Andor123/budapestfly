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

			// Show/hide promo banners based on active tab
			const tab = query_args['tab'];
			if (tab === 'tab-ajax') {
				$('.wpzoom-forms-settings-ajax-promo-container').css('display', 'inline-block');
				$('.wpzoom-forms-settings-integrations-promo-container').hide();
			} else if (tab === 'tab-integrations') {
				$('.wpzoom-forms-settings-integrations-promo-container').css('display', 'inline-block');
				$('.wpzoom-forms-settings-ajax-promo-container').hide();
			} else {
				$('.wpzoom-forms-settings-ajax-promo-container').hide();
				$('.wpzoom-forms-settings-integrations-promo-container').hide();
			}
		});

		// Make setting wp-tab-active optional.
		$('.wp-tab-bar').each(function () {
			if ($('.wp-tab-active', this).length) {
				$('.wp-tab-active', this).click();
			} else {
				$('a', this).first().click();
			}
			
			// Check initial tab on page load
			const url = window.location.href;
			const urlParams = new URLSearchParams(url);
			const tab = urlParams.get('tab');
			if (tab === 'tab-ajax') {
				$('.wpzoom-forms-settings-ajax-promo-container').css('display', 'inline-block');
				$('.wpzoom-forms-settings-integrations-promo-container').hide();
			} else if (tab === 'tab-integrations') {
				$('.wpzoom-forms-settings-integrations-promo-container').css('display', 'inline-block');
				$('.wpzoom-forms-settings-ajax-promo-container').hide();
			} else {
				$('.wpzoom-forms-settings-ajax-promo-container').hide();
				$('.wpzoom-forms-settings-integrations-promo-container').hide();
			}
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
				$('.wpzoom-forms-settings-integrations-promo-container').hide();
			} else if (tab === 'tab-integrations') {
				$('.wpzoom-forms-settings-integrations-promo-container').css('display', 'inline-block');
				$('.wpzoom-forms-settings-ajax-promo-container').hide();
			} else {
				$('.wpzoom-forms-settings-ajax-promo-container').hide();
				$('.wpzoom-forms-settings-integrations-promo-container').hide();
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

		// Turnstile live preview & key validation
		(function () {
			var $widget = $('#wpzf-turnstile-preview-widget');

			if (!$widget.length) {
				return;
			}

			var $status = $('#wpzf-turnstile-preview-status'),
				$siteKey = $('#wpzf_global_turnstile_site_key'),
				$secretKey = $('#wpzf_global_turnstile_secret_key'),
				$theme = $('#wpzf_global_turnstile_widget_theme'),
				i18n = Settings.turnstile_i18n || {},
				widgetId = null,
				apiRequested = false,
				renderTimer = null;

			function setStatus(state, message) {
				$status
					.removeClass('wpzf-ts-ok wpzf-ts-error wpzf-ts-pending')
					.addClass(state ? 'wpzf-ts-' + state : '')
					.text(message || '');
			}

			function isTurnstileSelected() {
				return $('input[name="wpzf-settings[wpzf_global_captcha_service]"]:checked').val() === 'turnstile';
			}

			function loadApi() {
				if (apiRequested) {
					return;
				}
				apiRequested = true;
				window.wpzfTurnstileApiReady = renderWidget;
				var script = document.createElement('script');
				script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=wpzfTurnstileApiReady';
				script.async = true;
				script.defer = true;
				document.head.appendChild(script);
			}

			function verifySecret(token) {
				var secret = $.trim($secretKey.val());

				if (!secret) {
					setStatus('ok', i18n.no_secret);
					return;
				}

				setStatus('pending', i18n.verifying);

				$.post(Settings.ajaxUrl, {
					action: 'wpzoom_forms_verify_turnstile',
					security: Settings.turnstile_nonce,
					token: token,
					secret: secret
				}).done(function (response) {
					if (response.success) {
						setStatus('ok', i18n.success);
					} else {
						setStatus('error', (response.data && response.data.message) || i18n.request_failed);
					}
				}).fail(function () {
					setStatus('error', i18n.request_failed);
				});
			}

			function onWidgetError(code) {
				code = String(code || '');

				if (code.indexOf('1101') === 0 || code.indexOf('400') === 0) {
					setStatus('error', i18n.invalid_site_key);
				} else if (code.indexOf('110200') === 0) {
					setStatus('error', i18n.invalid_domain + ' ' + window.location.hostname);
				} else {
					setStatus('error', (i18n.widget_error || '%s').replace('%s', code));
				}

				return true; // Error handled, don't retry.
			}

			function renderWidget() {
				if (!isTurnstileSelected()) {
					return;
				}

				var siteKey = $.trim($siteKey.val());

				if (widgetId !== null && window.turnstile) {
					window.turnstile.remove(widgetId);
					widgetId = null;
				}

				if (!siteKey) {
					setStatus('', i18n.enter_site_key);
					return;
				}

				if (!window.turnstile) {
					setStatus('pending', i18n.loading);
					loadApi();
					return;
				}

				setStatus('pending', i18n.loading);

				widgetId = window.turnstile.render($widget.get(0), {
					sitekey: siteKey,
					theme: $theme.val() || 'auto',
					callback: verifySecret,
					'error-callback': onWidgetError,
					'expired-callback': function () {
						setStatus('', i18n.expired);
					}
				});
			}

			function scheduleRender() {
				clearTimeout(renderTimer);
				renderTimer = setTimeout(renderWidget, 600);
			}

			$siteKey.on('input', scheduleRender);
			$theme.on('change', renderWidget);

			// Re-validate the secret key with a fresh token when it changes.
			$secretKey.on('input', function () {
				clearTimeout(renderTimer);
				renderTimer = setTimeout(function () {
					if (widgetId !== null && window.turnstile) {
						window.turnstile.reset(widgetId); // New token -> triggers callback -> re-verifies.
					} else {
						renderWidget();
					}
				}, 600);
			});

			$('input[name="wpzf-settings[wpzf_global_captcha_service]"]').on('change', function () {
				if (isTurnstileSelected()) {
					renderWidget();
				}
			});

			if (isTurnstileSelected()) {
				renderWidget();
			}
		})();

	})(jQuery, WPZOOM_Settings);
});