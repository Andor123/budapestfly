<?php
/**
 * Client for the WPZOOM section catalog on ai.wpzoom.com.
 *
 * Ported from Inspiro Starter Sites 1.5.1, components/importer/inc/Ai/Catalog/Catalog.php (the catalog client)
 * and the token, site-key and license parts of components/importer/inc/Ai/AiProxyClient.php. Keep the two in
 * step: a change to the proxy's catalog route lands in both. When Inspiro Starter Sites is active its own client
 * is used instead (see WPZOOM_AI_Starter_Sites), so a site never keeps two caches.
 *
 * The catalog is served by wpzoom-api-key-provider on ai.wpzoom.com (route services/v1/ai-catalog): 159 sections
 * extracted from the Inspiro demos, each as block markup with sentinels for copy, photos and colors. This class
 * fetches the metadata of every section the site may use (cached in a transient) and the full markup only of the
 * sections being inserted (cached in an option by catalog version), and holds the palette and stylesheet helpers
 * the rendered sections need.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Catalog {

	const META_TRANSIENT = 'wpzoom_ai_catalog_meta';
	const DOWN_TRANSIENT = 'wpzoom_ai_catalog_down';
	const CACHE_OPTION   = 'wpzoom_ai_catalog_sections';

	/**
	 * The email registration issued by the proxy's /ai-connect route. Same options as Inspiro Starter Sites, so a
	 * site connected through either plugin is connected for both.
	 */
	const SITE_KEY_OPTION = 'inspiro_starter_sites_ai_site_key';
	const EMAIL_OPTION    = 'inspiro_starter_sites_ai_email';

	/** The license product the proxy checks the Inspiro theme key against. */
	const LICENSE_ITEM = 'inspiro';

	/**
	 * The shared proxy access token, the same value Inspiro Starter Sites sends (AiProxyClient::token()). Empty
	 * until it is filled in before a release; WPZOOM_AI_CONNECT_AI_TOKEN in wp-config.php overrides it.
	 */
	const PROXY_TOKEN = 'OSDM0o2HIDRM1wlMVa4wasK11dZ1OIdl';

	/** Blocks some sections need that only recent WordPress versions register. */
	const VERSIONED_BLOCKS = array( 'core/accordion', 'core/icon' );

	/** Button shapes => corner radius. */
	const BUTTON_SHAPES = array(
		'square'  => '0px',
		'soft'    => '4px',
		'rounded' => '10px',
		'pill'    => '999px',
	);

	/** @var array|null [ 'version' => ..., 'sections' => id => meta ] */
	private static $meta = null;

	/** @var string Error code of the last failed catalog request ('' when it worked). */
	private static $error = '';

	/* ------------------------------------------------------------------
	 * Proxy identity
	 * ---------------------------------------------------------------- */

	/**
	 * Proxy REST base, e.g. https://ai.wpzoom.com/wp-json/services/v1.
	 *
	 * @return string
	 */
	public static function base_url() {
		if ( defined( 'WPZOOM_AI_CONNECT_AI_ENDPOINT' ) && WPZOOM_AI_CONNECT_AI_ENDPOINT ) {
			$base = WPZOOM_AI_CONNECT_AI_ENDPOINT;
		} elseif ( defined( 'INSPIRO_STARTER_SITES_AI_ENDPOINT' ) && INSPIRO_STARTER_SITES_AI_ENDPOINT ) {
			$base = INSPIRO_STARTER_SITES_AI_ENDPOINT;
		} else {
			$base = 'https://ai.wpzoom.com/wp-json/services/v1';
		}
		/**
		 * Filters the AI proxy base URL (for a staging proxy).
		 *
		 * @param string $base
		 */
		return untrailingslashit( (string) apply_filters( 'wpzoom_ai_connect_ai_endpoint', $base ) );
	}

	/**
	 * Shared proxy access token: the same value every WPZOOM AI client sends (the registration and the license,
	 * not the token, are what the proxy enforces). Starter Sites' token wins when that plugin is loaded.
	 *
	 * @return string
	 */
	public static function token() {
		if ( class_exists( '\Inspiro\Starter_Sites\Ai\AiProxyClient' ) ) {
			return (string) \Inspiro\Starter_Sites\Ai\AiProxyClient::token();
		}
		if ( defined( 'WPZOOM_AI_CONNECT_AI_TOKEN' ) && WPZOOM_AI_CONNECT_AI_TOKEN ) {
			$token = WPZOOM_AI_CONNECT_AI_TOKEN;
		} elseif ( defined( 'INSPIRO_STARTER_SITES_AI_TOKEN' ) && INSPIRO_STARTER_SITES_AI_TOKEN ) {
			$token = INSPIRO_STARTER_SITES_AI_TOKEN;
		} else {
			$token = self::PROXY_TOKEN;
		}
		return (string) apply_filters( 'wpzoom_ai_connect_ai_token', $token );
	}

	/** A service URL with the identification query args the proxy reads. */
	private static function endpoint( $service ) {
		return add_query_arg(
			array(
				'token'    => rawurlencode( self::token() ),
				// home_url(), not site_url(): the theme activates its license with home_url() and the proxy
				// verifies the license against the URL it receives here.
				'site_url' => rawurlencode( home_url() ),
				'plugin'   => 'wpzoom-ai-connect',
			),
			self::base_url() . '/' . $service
		);
	}

	/** The registration key from /ai-connect ('' when the site is not connected). */
	public static function site_key() {
		return (string) get_option( self::SITE_KEY_OPTION, '' );
	}

	public static function is_connected() {
		return '' !== self::site_key();
	}

	/** The email the site is registered with ('' when not connected). */
	public static function connected_email() {
		return (string) get_option( self::EMAIL_OPTION, '' );
	}

	/**
	 * The Inspiro Premium license key when the theme is active and its license verified; it unlocks the premium
	 * sections on the proxy. Same rule as Starter Sites: owning a key counts only while Inspiro Premium is in use.
	 *
	 * @return string '' without a usable license.
	 */
	public static function premium_license() {
		if ( class_exists( '\Inspiro\Starter_Sites\Ai\AiProxyClient' ) ) {
			return (string) \Inspiro\Starter_Sites\Ai\AiProxyClient::premium_license();
		}
		$license = '';
		if ( self::is_premium_inspiro() && 'valid' === get_option( 'inspiro_license_key_status' ) ) {
			$license = trim( (string) get_option( 'inspiro_license_key', '' ) );
		}
		/**
		 * Filters the license key sent with catalog requests (an Inspiro Patterns license also unlocks the
		 * premium sections: return array( 'key' => ..., 'item' => 'inspiro-patterns' ) from
		 * wpzoom_ai_connect_catalog_license_item to send it).
		 *
		 * @param string $license
		 */
		return (string) apply_filters( 'wpzoom_ai_connect_catalog_license', $license );
	}

	/** True on Inspiro Premium (WPZOOM framework loaded, parent theme "inspiro" or named "Inspiro Premium"). */
	public static function is_premium_inspiro() {
		if ( ! class_exists( 'WPZOOM' ) ) {
			return false;
		}
		$template = get_template();
		if ( 'inspiro' === $template ) {
			return true;
		}
		return 'inspiro premium' === strtolower( trim( (string) wp_get_theme( $template )->get( 'Name' ) ) );
	}

	/** True on Inspiro Lite (parent theme "inspiro-lite"). */
	public static function is_inspiro_lite() {
		return 'inspiro-lite' === get_template();
	}

	/* ------------------------------------------------------------------
	 * Email connection (/ai-connect, /ai-verify)
	 * ---------------------------------------------------------------- */

	/**
	 * Registers this site and email with the proxy. The proxy answers `pending` when a 6-digit code was emailed
	 * (verify() releases the key) or stores the key at once.
	 *
	 * @param string $email   User email.
	 * @param bool   $consent Whether the user agreed to receive the code and occasional WPZOOM emails.
	 * @return array|WP_Error [ 'pending' => bool, 'email' => string ]
	 */
	public static function connect( $email, $consent = false ) {
		$response = wp_remote_post(
			self::endpoint( 'ai-connect' ),
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'email'   => $email,
						'consent' => $consent ? 1 : 0,
					)
				),
				'timeout' => 15,
			)
		);
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'ai_connect_unreachable', $response->get_error_message() );
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( $code < 200 || $code >= 300 || ! is_array( $data ) || empty( $data['success'] ) ) {
			$msg = ( is_array( $data ) && ! empty( $data['message'] ) ) ? (string) $data['message'] : ( 'HTTP ' . $code );
			return new WP_Error( 'ai_connect_error', $msg );
		}
		if ( ! empty( $data['pending'] ) ) {
			return array(
				'pending' => true,
				'email'   => sanitize_email( (string) ( isset( $data['email'] ) ? $data['email'] : $email ) ),
			);
		}
		if ( empty( $data['site_key'] ) ) {
			return new WP_Error( 'ai_connect_error', __( 'The AI service returned an unexpected response. Please try again.', 'wpzoom-ai-connect' ) );
		}
		self::store_registration( (string) $data['site_key'], (string) ( isset( $data['email'] ) ? $data['email'] : $email ) );
		return array(
			'pending' => false,
			'email'   => self::connected_email(),
		);
	}

	/**
	 * Confirms the emailed 6-digit code and stores the released site key.
	 *
	 * @param string $code The code.
	 * @return array|WP_Error [ 'email' => string ]
	 */
	public static function verify( $code ) {
		$response = wp_remote_post(
			self::endpoint( 'ai-verify' ),
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array( 'code' => preg_replace( '/\D/', '', (string) $code ) ) ),
				'timeout' => 15,
			)
		);
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'ai_verify_unreachable', $response->get_error_message() );
		}
		$status = (int) wp_remote_retrieve_response_code( $response );
		$data   = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( $status < 200 || $status >= 300 || ! is_array( $data ) || empty( $data['success'] ) || empty( $data['site_key'] ) ) {
			$msg = ( is_array( $data ) && ! empty( $data['message'] ) ) ? (string) $data['message'] : ( 'HTTP ' . $status );
			return new WP_Error( 'ai_verify_error', $msg );
		}
		self::store_registration( (string) $data['site_key'], (string) ( isset( $data['email'] ) ? $data['email'] : '' ) );
		return array( 'email' => self::connected_email() );
	}

	private static function store_registration( $site_key, $email ) {
		update_option( self::SITE_KEY_OPTION, sanitize_text_field( $site_key ), false );
		update_option( self::EMAIL_OPTION, sanitize_email( $email ), false );
		// A "connect first" answer cached minutes ago no longer applies.
		delete_transient( self::DOWN_TRANSIENT );
		delete_transient( self::META_TRANSIENT );
		self::$meta  = null;
		self::$error = '';
	}

	/* ------------------------------------------------------------------
	 * Catalog
	 * ---------------------------------------------------------------- */

	/**
	 * Versioned blocks this WordPress registers (sections needing others are never offered).
	 *
	 * @return string[]
	 */
	public static function blocks() {
		$registry = WP_Block_Type_Registry::get_instance();
		return array_values(
			array_filter(
				self::VERSIONED_BLOCKS,
				static function ( $block ) use ( $registry ) {
					return $registry->is_registered( $block );
				}
			)
		);
	}

	/**
	 * Cheap check: false only right after the catalog failed to load (for ten minutes, five after
	 * `registration_required`).
	 *
	 * @return bool
	 */
	public static function available() {
		return false === get_transient( self::DOWN_TRANSIENT );
	}

	/**
	 * Why the last catalog request failed: 'registration_required' (the proxy wants the site's email connected),
	 * 'wpzoom_catalog_unavailable' (proxy down or an unexpected answer), or '' when it worked.
	 *
	 * @return string
	 */
	public static function last_error() {
		if ( '' !== self::$error ) {
			return self::$error;
		}
		$down = get_transient( self::DOWN_TRANSIENT );
		return is_string( $down ) && '' !== $down ? $down : ( false === $down ? '' : 'wpzoom_catalog_unavailable' );
	}

	/**
	 * Metadata of every section this site may use: role, fits, tone, tier, description, photos, items, needs.
	 * Sections with unmet needs are included (the proxy is not sent `has`), so the client can list them as
	 * unavailable with the need named.
	 *
	 * @return array id => meta ([] when unavailable; see last_error())
	 */
	public static function meta() {
		if ( null !== self::$meta ) {
			return self::$meta['sections'];
		}

		$blocks   = self::blocks();
		$licensed = '' !== self::premium_license();
		$cached   = get_transient( self::META_TRANSIENT );
		if ( is_array( $cached ) && ! empty( $cached['sections'] ) && isset( $cached['blocks'] ) && $cached['blocks'] === $blocks
			&& isset( $cached['licensed'] ) && $cached['licensed'] === $licensed ) {
			self::$meta = $cached;
			return self::$meta['sections'];
		}

		if ( ! self::available() ) {
			self::$error = self::last_error();
			self::$meta  = array(
				'version'  => '',
				'sections' => array(),
			);
			return array();
		}

		$response = self::request( array(), $blocks );
		if ( is_wp_error( $response ) || empty( $response['sections'] ) ) {
			$code = is_wp_error( $response ) ? $response->get_error_code() : 'wpzoom_catalog_unavailable';
			set_transient( self::DOWN_TRANSIENT, $code, 'registration_required' === $code ? 5 * MINUTE_IN_SECONDS : 10 * MINUTE_IN_SECONDS );
			self::$error = $code;
			self::$meta  = array(
				'version'  => '',
				'sections' => array(),
			);
			return array();
		}

		delete_transient( self::DOWN_TRANSIENT );
		self::$error          = '';
		$response['blocks']   = $blocks;
		$response['licensed'] = $licensed;
		// A key this site believes valid that the server did not accept (expired, or just renewed) gets the free
		// catalog: keep that only briefly, so the premium sections appear soon after a renewal.
		$premium = in_array( 'premium', array_column( $response['sections'], 'tier' ), true );
		set_transient( self::META_TRANSIENT, $response, $licensed && ! $premium ? HOUR_IN_SECONDS : 12 * HOUR_IN_SECONDS );
		self::$meta = $response;

		return self::$meta['sections'];
	}

	/**
	 * One section's metadata.
	 *
	 * @param string $id Section id.
	 * @return array|null
	 */
	public static function get( $id ) {
		$meta = self::meta();
		return isset( $meta[ $id ] ) ? $meta[ $id ] : null;
	}

	/**
	 * Full sections (fields, markup, photo and asset maps) for rendering, from the local cache or one request
	 * for the missing ones.
	 *
	 * @param string[] $ids Section ids.
	 * @return array id => section (unknown ids are left out)
	 */
	public static function sections( array $ids ) {
		self::meta();
		$version = isset( self::$meta['version'] ) ? self::$meta['version'] : '';

		$cache = get_option( self::CACHE_OPTION, array() );
		if ( ! is_array( $cache ) || ! isset( $cache['version'] ) || $cache['version'] !== $version || ! isset( $cache['sections'] ) || ! is_array( $cache['sections'] ) ) {
			$cache = array(
				'version'  => $version,
				'sections' => array(),
			);
		}

		$ids     = array_values( array_unique( array_map( 'sanitize_key', $ids ) ) );
		$missing = array_values( array_diff( $ids, array_keys( $cache['sections'] ) ) );
		if ( $missing && '' !== $version ) {
			$response = self::request( $missing, self::blocks() );
			if ( ! is_wp_error( $response ) ) {
				$fetched = array();
				foreach ( $response['sections'] as $id => $section ) {
					if ( is_array( $section ) && ! empty( $section['markup'] ) ) {
						$fetched[ sanitize_key( (string) $id ) ] = $section;
					}
				}
				$cache['sections'] = $fetched + $cache['sections'];

				// Another request may have cached sections meanwhile: merge instead of overwriting.
				$stored = get_option( self::CACHE_OPTION, array() );
				if ( is_array( $stored ) && isset( $stored['version'] ) && $stored['version'] === $version && ! empty( $stored['sections'] ) && is_array( $stored['sections'] ) ) {
					$cache['sections'] += $stored['sections'];
				}
				update_option( self::CACHE_OPTION, $cache, false );
			} else {
				self::$error = $response->get_error_code();
			}
		}

		return array_intersect_key( $cache['sections'], array_flip( $ids ) );
	}

	/**
	 * One catalog request. Empty $ids = metadata of every section the site may use; ids = the full sections.
	 *
	 * @param string[] $ids    Section ids.
	 * @param string[] $blocks Versioned blocks this site registers.
	 * @return array|WP_Error [ 'version' => string, 'sections' => array ]
	 */
	private static function request( array $ids, array $blocks ) {
		$license = self::premium_license();
		/**
		 * Filters the license product sent with the key ('inspiro' or 'inspiro-patterns').
		 *
		 * @param string $item
		 * @param string $license
		 */
		$item = (string) apply_filters( 'wpzoom_ai_connect_catalog_license_item', self::LICENSE_ITEM, $license );

		if ( '' === self::token() ) {
			return new WP_Error( 'wpzoom_catalog_unavailable', __( 'No access token for ai.wpzoom.com is configured in this build of WPZOOM AI Connect.', 'wpzoom-ai-connect' ) );
		}

		$response = wp_remote_post(
			self::endpoint( 'ai-catalog' ),
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'site_key'     => self::site_key(),
						'license_key'  => $license,
						'license_item' => '' !== $license ? $item : '',
						'blocks'       => array_values( $blocks ),
						'ids'          => array_values( $ids ),
					)
				),
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'wpzoom_catalog_unavailable', $response->get_error_message() );
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$data   = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( 403 === $status && is_array( $data ) && isset( $data['code'] ) && 'registration_required' === $data['code'] ) {
			return new WP_Error( 'registration_required', isset( $data['message'] ) ? (string) $data['message'] : __( 'Connect your email first.', 'wpzoom-ai-connect' ) );
		}
		if ( 200 !== $status || ! is_array( $data ) || empty( $data['success'] ) || ! isset( $data['sections'] ) || ! is_array( $data['sections'] ) ) {
			$msg = is_array( $data ) && isset( $data['message'] ) ? (string) $data['message'] : __( 'The section catalog is unavailable. Please try again later.', 'wpzoom-ai-connect' );
			return new WP_Error( 'wpzoom_catalog_unavailable', $msg );
		}

		return array(
			'version'  => isset( $data['version'] ) ? (string) $data['version'] : '',
			'sections' => $data['sections'],
		);
	}

	/* ------------------------------------------------------------------
	 * Palette
	 * ---------------------------------------------------------------- */

	/**
	 * Every palette role the sections use, derived from one accent.
	 *
	 * @param string $accent  Brand color.
	 * @param string $dark    Dark ground/ink ('' = accent-tinted near-black).
	 * @param string $surface Light tinted ground ('' = accent-tinted near-white).
	 * @return array role => hex
	 */
	public static function palette( $accent, $dark = '', $surface = '' ) {
		$accent = sanitize_hex_color( $accent ) ? strtolower( $accent ) : '#0bb4aa';
		$dark   = sanitize_hex_color( $dark ) ? strtolower( $dark ) : self::mix( $accent, '#0e0e10', 0.9 );

		return array(
			'accent'      => $accent,
			'accentdark'  => self::mix( $accent, '#000000', 0.5 ),
			'accentlight' => self::mix( $accent, '#ffffff', 0.6 ),
			'surface'     => sanitize_hex_color( $surface ) ? strtolower( $surface ) : self::mix( $accent, '#ffffff', 0.93 ),
			'dark'        => $dark,
		);
	}

	/**
	 * One button style for every button of the inserted sections.
	 *
	 * @param mixed $raw {shape, case, weight}.
	 * @return array{shape:string,case:string,weight:string}
	 */
	public static function button_style( $raw ) {
		$raw    = is_array( $raw ) ? $raw : array();
		$shape  = isset( $raw['shape'] ) ? sanitize_key( (string) $raw['shape'] ) : '';
		$case   = isset( $raw['case'] ) ? sanitize_key( (string) $raw['case'] ) : '';
		$weight = isset( $raw['weight'] ) ? (string) $raw['weight'] : '';

		return array(
			'shape'  => isset( self::BUTTON_SHAPES[ $shape ] ) ? $shape : 'soft',
			'case'   => 'uppercase' === $case ? 'uppercase' : 'normal',
			'weight' => in_array( $weight, array( '400', '500', '600', '700' ), true ) ? $weight : '500',
		);
	}

	/**
	 * The stylesheet rendered sections carry. Sections read the theme palette's primary/secondary slugs with
	 * their source theme's meaning (Lite: near-black / accent; Premium swaps them): the two wrapper rules pin
	 * them per section to the palette, so a section looks the same wherever it came from.
	 *
	 * @param array $palette Role => hex.
	 * @param array $buttons Button style, see button_style().
	 * @return string
	 */
	public static function css( array $palette, array $buttons = array() ) {
		$buttons = self::button_style( $buttons );
		$caps    = 'uppercase' === $buttons['case'];
		$btn     = '.iss-ai-cs .wp-block-button.iss-btn>.wp-block-button__link';

		return '.iss-ai-cs--lite{--wp--preset--color--primary:' . $palette['dark'] . ';--wp--preset--color--secondary:' . $palette['accent'] . ';--wp--preset--color--header-footer:' . $palette['dark'] . '}'
			. '.iss-ai-cs--premium{--wp--preset--color--primary:' . $palette['accent'] . ';--wp--preset--color--secondary:' . $palette['dark'] . ';--wp--preset--color--header-footer:' . $palette['dark'] . ';--wp--preset--color--brown:' . $palette['accent'] . ';--wp--preset--color--green:' . $palette['accent'] . '}'
			// Themes color headings directly, so a heading inside a section that sets a (light) text color would
			// stay dark on a dark ground. Headings and paragraphs without their own color follow the section's.
			. '.iss-ai-cs:where(.has-text-color) :where(h1,h2,h3,h4,h5,h6,p),.iss-ai-cs :where(.has-text-color) :where(h1,h2,h3,h4,h5,h6,p){color:inherit}'
			// One button style for the whole site (the renderer strips the demos' own).
			. ':root{--iss-btn-radius:' . self::BUTTON_SHAPES[ $buttons['shape'] ] . ';--iss-btn-weight:' . $buttons['weight']
			. ';--iss-btn-case:' . ( $caps ? 'uppercase' : 'none' ) . ';--iss-btn-tracking:' . ( $caps ? '.08em' : '0' )
			. ';--iss-btn-size:' . ( $caps ? '.8125rem' : '1rem' ) . ';--iss-btn-fill:' . $palette['accent'] . ';--iss-btn-fill-hover:' . $palette['accentdark']
			. ';--iss-btn-ink:' . $palette['dark'] . '}'
			. $btn . '{display:inline-block;padding:.9em 1.9em;border:2px solid transparent;border-radius:var(--iss-btn-radius);background:none;box-shadow:none;'
			. 'font-size:var(--iss-btn-size);font-weight:var(--iss-btn-weight);line-height:1.2;text-transform:var(--iss-btn-case);letter-spacing:var(--iss-btn-tracking);text-decoration:none}'
			// Filled: the call to action, in the brand color on any ground.
			. '.iss-ai-cs .wp-block-button.iss-btn.is-style-fill>.wp-block-button__link{background:var(--iss-btn-fill);border-color:var(--iss-btn-fill);color:#fff}'
			. '.iss-ai-cs .wp-block-button.iss-btn.is-style-fill>.wp-block-button__link:hover{background:var(--iss-btn-fill-hover);border-color:var(--iss-btn-fill-hover);color:#fff}'
			// Outline: the secondary action, in the ground's ink.
			. '.iss-ai-cs .wp-block-button.iss-btn.is-style-outline>.wp-block-button__link{border-color:currentColor;color:var(--iss-btn-ink)}'
			. '.iss-ai-cs .wp-block-button.iss-btn.is-style-outline>.wp-block-button__link:hover{background:var(--iss-btn-ink);border-color:var(--iss-btn-ink);color:#fff!important}'
			. '.iss-ai-cs .wp-block-button.iss-btn.iss-btn-on-dark.is-style-outline>.wp-block-button__link{color:#fff}'
			. '.iss-ai-cs .wp-block-button.iss-btn.iss-btn-on-dark.is-style-outline>.wp-block-button__link:hover{background:#fff;border-color:#fff;color:var(--iss-btn-ink)!important}'
			// Text link: "Read more" under cards and lists.
			. '.iss-ai-cs .wp-block-button.iss-btn.iss-btn-link>.wp-block-button__link{padding:0 0 .2em;border:0;border-bottom:2px solid currentColor;border-radius:0;color:var(--iss-btn-fill)}'
			. '.iss-ai-cs .wp-block-button.iss-btn.iss-btn-link>.wp-block-button__link:hover{background:none;color:var(--iss-btn-fill-hover)}'
			. '.iss-ai-cs .wp-block-button.iss-btn.iss-btn-on-dark.iss-btn-link>.wp-block-button__link,.iss-ai-cs .wp-block-button.iss-btn.iss-btn-on-dark.iss-btn-link>.wp-block-button__link:hover{color:#fff}'
			// A call-to-action link at the end of the menu ("Book a class").
			. '.iss-ai-menu-cta>a{padding:.55em 1.2em!important;border-radius:var(--iss-btn-radius);background:var(--iss-btn-fill);color:#fff!important;font-weight:var(--iss-btn-weight);text-transform:var(--iss-btn-case);letter-spacing:var(--iss-btn-tracking)}'
			. '.iss-ai-menu-cta>a:hover{background:var(--iss-btn-fill-hover)}';
	}

	/**
	 * Mix two hex colors.
	 *
	 * @param string $a Color A.
	 * @param string $b Color B.
	 * @param float  $t Weight of B (0-1).
	 * @return string
	 */
	public static function mix( $a, $b, $t ) {
		$a = ltrim( (string) $a, '#' );
		$b = ltrim( (string) $b, '#' );
		if ( 3 === strlen( $a ) ) {
			$a = $a[0] . $a[0] . $a[1] . $a[1] . $a[2] . $a[2];
		}
		if ( 3 === strlen( $b ) ) {
			$b = $b[0] . $b[0] . $b[1] . $b[1] . $b[2] . $b[2];
		}
		$out = '#';
		for ( $i = 0; $i < 3; $i++ ) {
			$ca   = hexdec( substr( $a, $i * 2, 2 ) );
			$cb   = hexdec( substr( $b, $i * 2, 2 ) );
			$out .= str_pad( dechex( (int) round( $ca + ( $cb - $ca ) * $t ) ), 2, '0', STR_PAD_LEFT );
		}
		return $out;
	}

	/**
	 * Relative luminance of a hex color (0 dark … 255 light).
	 *
	 * @param string $hex Color.
	 * @return float
	 */
	public static function luminance( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( 6 !== strlen( $hex ) ) {
			return 0;
		}
		return 0.2126 * hexdec( substr( $hex, 0, 2 ) ) + 0.7152 * hexdec( substr( $hex, 2, 2 ) ) + 0.0722 * hexdec( substr( $hex, 4, 2 ) );
	}
}
