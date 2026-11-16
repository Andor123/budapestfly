<?php
/**
 * The site's Additional CSS, read and extended by an AI.
 *
 * Classic themes keep it in the Customizer's custom_css post; block themes in the user's global styles
 * (Site Editor → Styles → Additional CSS). Both keep revisions, so every change can be undone from WordPress.
 * The AI adds CSS in labelled blocks; saving again with the same label replaces that block instead of
 * piling up, and empty CSS with a label removes it. CSS a person wrote is never touched.
 *
 * Writing needs WordPress's own edit_css capability (administrators on a single site; nobody when
 * DISALLOW_UNFILTERED_HTML is set; only super admins on multisite). The site owner can switch it off for the
 * WPZOOM AI Connect connection on the AI Connect screen (option wpzoom_ai_connect_allow_css).
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_CSS {

	const MAX_ADD = 20000;

	/** Option the site owner uses to let (default) or stop the WPZOOM AI Connect connection editing Additional CSS. */
	const OPTION = 'wpzoom_ai_connect_allow_css';

	/** Whether the connection may edit Additional CSS on this site. */
	public static function allowed_for_connection(): bool {
		return '0' !== (string) get_option( self::OPTION, '1' );
	}

	private static function is_block_theme(): bool {
		return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	}

	/** The block theme's user global styles post (created if missing), decoded. */
	private static function global_styles(): array {
		$post = WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles( wp_get_theme(), true );
		$id   = (int) ( $post['ID'] ?? 0 );
		$data = json_decode( (string) ( $post['post_content'] ?? '' ), true );
		return array( 'id' => $id, 'data' => is_array( $data ) ? $data : array( 'version' => 3, 'isGlobalStylesUserThemeJSON' => true ) );
	}

	private static function current(): string {
		if ( self::is_block_theme() ) {
			$styles = self::global_styles();
			return (string) ( $styles['data']['styles']['css'] ?? '' );
		}
		return (string) wp_get_custom_css();
	}

	private static function where(): string {
		return self::is_block_theme() ? __( 'Appearance → Editor → Styles → Additional CSS', 'wpzoom-ai-connect' ) : __( 'Appearance → Customize → Additional CSS', 'wpzoom-ai-connect' );
	}

	/** Labelled blocks the AI added earlier: label => full block text. */
	private static function blocks( string $css ): array {
		preg_match_all( '#\n?/\* AI Connect: (.+?) \*/\n.*?/\* end AI Connect: \1 \*/\n?#s', $css, $m, PREG_SET_ORDER );
		$out = array();
		foreach ( $m as $match ) {
			$out[ $match[1] ] = $match[0];
		}
		return $out;
	}

	public static function get(): array {
		$css = self::current();
		return array(
			'css'          => $css,
			'length'       => strlen( $css ),
			'where'        => self::where(),
			'theme'        => wp_get_theme()->get( 'Name' ),
			'added_by_ai'  => array_keys( self::blocks( $css ) ),
			'note'         => __( 'To change the look, add CSS with wpzoom/add-custom-css under a short label. Read the page with inspect_page first to pick selectors that exist.', 'wpzoom-ai-connect' ),
		);
	}

	/**
	 * Rejects what the Customizer's Additional CSS screen rejects (a STYLE closing tag, or a text ending in part of
	 * one, checked the way WP_Customize_Custom_CSS_Setting::validate() does), plus markup, @import of other
	 * stylesheets, unclosed comments and unbalanced braces.
	 */
	private static function validate( string $css ) {
		$length = strlen( $css );
		for ( $at = strcspn( $css, '<' ); $at < $length; $at += strcspn( $css, '<', ++$at ) ) {
			$remaining = $length - $at;
			if ( 0 === substr_compare( $css, '</style', $at, min( 7, $remaining ), true ) ) {
				if ( $remaining < 8 || 1 === strspn( $css, " \t\f\r\n/>", $at + 7, 1 ) ) {
					return new WP_Error( 'wpzoom_css_invalid', __( 'CSS cannot contain or end in a closing </style> tag.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
				}
			}
		}
		if ( preg_match( '#<\s*/?\s*[a-z!]#i', $css ) ) {
			return new WP_Error( 'wpzoom_css_invalid', __( 'Markup is not allowed in CSS: send only CSS rules.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
		}
		if ( preg_match( '#@import\b#i', $css ) ) {
			return new WP_Error( 'wpzoom_css_invalid', __( '@import is not allowed: load fonts through the theme\'s typography settings instead.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
		}
		$stripped = preg_replace( '#/\*.*?\*/#s', '', $css );
		if ( false !== strpos( (string) $stripped, '/*' ) ) {
			return new WP_Error( 'wpzoom_css_invalid', __( 'A CSS comment is not closed.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
		}
		$stripped = preg_replace( '#"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'#s', '""', (string) $stripped );
		if ( substr_count( (string) $stripped, '{' ) !== substr_count( (string) $stripped, '}' ) ) {
			return new WP_Error( 'wpzoom_css_invalid', __( 'The CSS has unbalanced braces: every { needs a }.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
		}
		return true;
	}

	private static function save( string $css ) {
		if ( self::is_block_theme() ) {
			$styles = self::global_styles();
			if ( ! $styles['id'] ) {
				return new WP_Error( 'wpzoom_css_failed', __( 'The global styles of this theme could not be loaded.', 'wpzoom-ai-connect' ) );
			}
			$data                  = $styles['data'];
			$data['styles']        = isset( $data['styles'] ) && is_array( $data['styles'] ) ? $data['styles'] : array();
			$data['styles']['css'] = $css;
			$result                = wp_update_post( array( 'ID' => $styles['id'], 'post_content' => wp_slash( wp_json_encode( $data ) ) ), true );
			return is_wp_error( $result ) ? $result : true;
		}
		$result = wp_update_custom_css_post( $css );
		return is_wp_error( $result ) ? $result : true;
	}

	public static function add( array $in ) {
		$label = trim( preg_replace( '/[^A-Za-z0-9 ,.:()\'-]+/', ' ', (string) ( $in['label'] ?? '' ) ) );
		$css   = trim( (string) ( $in['css'] ?? '' ) );
		if ( '' === $label ) {
			return new WP_Error( 'wpzoom_css_label', __( 'Give the change a short label, e.g. "Menu hover color". Saving again with the same label replaces that change.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
		}
		$label = substr( $label, 0, 80 );
		if ( strlen( $css ) > self::MAX_ADD ) {
			return new WP_Error( 'wpzoom_css_too_long', __( 'Add at most 20,000 characters of CSS at a time.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
		}
		if ( '' !== $css ) {
			$valid = self::validate( $css );
			if ( is_wp_error( $valid ) ) {
				return $valid;
			}
		}

		$current = self::current();
		$blocks  = self::blocks( $current );
		$block   = '' === $css ? '' : "\n/* AI Connect: {$label} */\n{$css}\n/* end AI Connect: {$label} */\n";
		if ( isset( $blocks[ $label ] ) ) {
			$new    = str_replace( $blocks[ $label ], $block, $current );
			$action = '' === $css ? 'removed' : 'replaced';
		} elseif ( '' === $css ) {
			return new WP_Error( 'wpzoom_css_label', sprintf( /* translators: 1: the label sent, 2: the labels used so far, comma-separated, or "none" */ __( 'No CSS was added under "%1$s". Labels added so far: %2$s.', 'wpzoom-ai-connect' ), $label, $blocks ? implode( ', ', array_keys( $blocks ) ) : __( 'none', 'wpzoom-ai-connect' ) ), array( 'status' => 400 ) );
		} else {
			$new    = rtrim( $current ) . "\n" . $block;
			$action = 'added';
		}

		$saved = self::save( '' === trim( $new ) ? '' : trim( $new ) . "\n" );
		if ( is_wp_error( $saved ) ) {
			return $saved;
		}
		return array(
			'action'      => $action,
			'label'       => $label,
			'where'       => self::where(),
			'live'        => true,
			'note'        => __( 'The change is live for visitors now (a page cache may need a purge). The previous CSS is kept as a revision in WordPress.', 'wpzoom-ai-connect' ),
			'added_by_ai' => array_keys( self::blocks( self::current() ) ),
		);
	}
}
