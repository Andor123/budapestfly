<?php
/**
 * The SEO title, meta description, focus keyword and noindex flag of a post or page, read and set the same way
 * whether the site runs Yoast SEO or Rank Math (Yoast wins when both are active).
 *
 * Both plugins keep these fields in post meta. Yoast's keys start with an underscore (protected meta that the REST
 * API will not write), which is one reason this lives in the plugin. Yoast's own WPSEO_Meta is used when it exists,
 * so its sanitizing runs; otherwise, and for Rank Math, plain post meta. An empty string clears a field, which puts
 * the plugin's template back. Canonical URLs, social previews and schema are not covered.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_SEO {

	/** Lengths search results show before cutting; longer values are saved, with a warning. */
	const TITLE_MAX       = 60;
	const DESCRIPTION_MAX = 160;

	/** The fields update() sets. */
	const FIELDS = array( 'title', 'description', 'focus_keyword', 'noindex' );

	/** Yoast's WPSEO_Meta keys, without the _yoast_wpseo_ prefix. */
	const YOAST_KEYS = array( 'title' => 'title', 'description' => 'metadesc', 'focus_keyword' => 'focuskw' );

	/** Rank Math's post meta keys. */
	const RANK_MATH_KEYS = array( 'title' => 'rank_math_title', 'description' => 'rank_math_description', 'focus_keyword' => 'rank_math_focus_keyword' );

	/** The active SEO plugin: yoast, rank_math or none. */
	public static function plugin(): string {
		if ( defined( 'WPSEO_VERSION' ) ) {
			return 'yoast';
		}
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			return 'rank_math';
		}
		return 'none';
	}

	/** The post, or an error when it does not exist or the user may not edit it. */
	private static function post( $post_id ) {
		$post = get_post( (int) $post_id );
		if ( ! $post || in_array( $post->post_status, array( 'trash', 'auto-draft' ), true ) || 'revision' === $post->post_type ) {
			return new WP_Error( 'wpzoom_not_found', sprintf( /* translators: %d: post ID */ __( 'No post or page with id %d.', 'wpzoom-ai-connect' ), (int) $post_id ), array( 'status' => 404 ) );
		}
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return new WP_Error( 'wpzoom_forbidden', __( 'You cannot edit this post, so its SEO fields cannot be read or changed.', 'wpzoom-ai-connect' ), array( 'status' => 403 ) );
		}
		return $post;
	}

	/**
	 * sanitize_text_field() without its removal of "%" followed by two hex digits, which would cut template variables
	 * such as Rank Math's %category% or %date% apart.
	 */
	private static function text( $value ): string {
		$guard = "\u{E000}";
		$value = str_replace( array( $guard, '%' ), array( '', $guard ), (string) $value );
		return str_replace( $guard, '%', sanitize_text_field( $value ) );
	}

	/** Rendered text: tags removed and entities decoded, the way a search result shows it. */
	private static function shown( string $html ): string {
		return self::text( html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' ) );
	}

	private static function length( string $value ): int {
		return function_exists( 'mb_strlen' ) ? mb_strlen( $value, 'UTF-8' ) : strlen( $value );
	}

	/* ---------- reading ---------- */

	private static function yoast_value( WP_Post $post, string $key ): string {
		if ( class_exists( 'WPSEO_Meta' ) && method_exists( 'WPSEO_Meta', 'get_value' ) ) {
			return (string) WPSEO_Meta::get_value( $key, $post->ID );
		}
		return (string) get_post_meta( $post->ID, '_yoast_wpseo_' . $key, true );
	}

	/** Rank Math's focus keywords: a comma-separated list whose first item is the focus keyword. */
	private static function rank_math_keywords( WP_Post $post ): array {
		$raw = (string) get_post_meta( $post->ID, self::RANK_MATH_KEYS['focus_keyword'], true );
		return array_values( array_filter( array_map( 'trim', explode( ',', $raw ) ), 'strlen' ) );
	}

	/** Rank Math's robots list for the post (empty when it follows the site default). */
	private static function rank_math_robots( WP_Post $post ): array {
		$robots = get_post_meta( $post->ID, 'rank_math_robots', true );
		return is_array( $robots ) ? array_values( array_filter( array_map( 'strval', $robots ) ) ) : array();
	}

	/** A Rank Math setting, or $default_value when it is not set or the helper is missing. */
	private static function rank_math_setting( string $key, $default_value = null ) {
		if ( class_exists( '\RankMath\Helper' ) && method_exists( '\RankMath\Helper', 'get_settings' ) ) {
			return \RankMath\Helper::get_settings( $key, $default_value );
		}
		return $default_value;
	}

	/**
	 * Rank Math registers its %variables% only on the front end ('wp') and in admin screens, never for a REST request,
	 * which is how abilities run. Its manager's setup() is public and runs once, so call it before replacing.
	 */
	private static function rank_math_variables(): void {
		$rank_math = function_exists( 'rank_math' ) ? rank_math() : null;
		$variables = is_object( $rank_math ) && isset( $rank_math->variables ) ? $rank_math->variables : null;
		if ( is_object( $variables ) && method_exists( $variables, 'setup' ) ) {
			$variables->setup();
		}
	}

	/** Whether the site default for this post type hides it from search engines. */
	private static function default_noindex( string $plugin, WP_Post $post ): bool {
		if ( 'yoast' === $plugin ) {
			return class_exists( 'WPSEO_Options' ) && method_exists( 'WPSEO_Options', 'get' ) && (bool) WPSEO_Options::get( 'noindex-' . $post->post_type, false );
		}
		$custom = self::rank_math_setting( "titles.pt_{$post->post_type}_custom_robots" );
		$robots = $custom ? self::rank_math_setting( "titles.pt_{$post->post_type}_robots" ) : self::rank_math_setting( 'titles.robots_global' );
		return is_array( $robots ) && in_array( 'noindex', $robots, true );
	}

	/** The four fields as stored, plus whether noindex follows the site default. */
	private static function read( string $plugin, WP_Post $post ): array {
		if ( 'yoast' === $plugin ) {
			$robots   = self::yoast_value( $post, 'meta-robots-noindex' ); // "1" noindex, "2" index, "0" or "" the default.
			$default  = ! in_array( $robots, array( '1', '2' ), true );
			$values   = array(
				'title'         => self::yoast_value( $post, self::YOAST_KEYS['title'] ),
				'description'   => self::yoast_value( $post, self::YOAST_KEYS['description'] ),
				'focus_keyword' => self::yoast_value( $post, self::YOAST_KEYS['focus_keyword'] ),
				'noindex'       => $default ? self::default_noindex( $plugin, $post ) : '1' === $robots,
			);
			$keywords = array();
		} else {
			$robots   = self::rank_math_robots( $post );
			$default  = ! in_array( 'noindex', $robots, true ) && ! in_array( 'index', $robots, true );
			$keywords = self::rank_math_keywords( $post );
			$values   = array(
				'title'         => (string) get_post_meta( $post->ID, self::RANK_MATH_KEYS['title'], true ),
				'description'   => (string) get_post_meta( $post->ID, self::RANK_MATH_KEYS['description'], true ),
				'focus_keyword' => $keywords[0] ?? '',
				'noindex'       => $default ? self::default_noindex( $plugin, $post ) : in_array( 'noindex', $robots, true ),
			);
		}
		$values['noindex_default'] = $default;
		$values['keywords']        = array_slice( $keywords, 1 );
		return $values;
	}

	/** What a search result would show, with the plugin's variables filled in; null when the plugin cannot say. */
	private static function render( string $plugin, WP_Post $post, string $field, string $value ): ?string {
		try {
			if ( 'yoast' === $plugin ) {
				if ( ! function_exists( 'wpseo_replace_vars' ) ) {
					return null;
				}
				$option   = ( 'title' === $field ? 'title-' : 'metadesc-' ) . $post->post_type;
				$template = '' !== $value ? $value : ( class_exists( 'WPSEO_Options' ) && method_exists( 'WPSEO_Options', 'get' ) ? (string) WPSEO_Options::get( $option, '' ) : '' );
				return self::rendered( $template, static function ( $text ) use ( $post ) {
					return wpseo_replace_vars( $text, $post );
				} );
			}
			if ( ! class_exists( '\RankMath\Helper' ) || ! method_exists( '\RankMath\Helper', 'replace_vars' ) ) {
				return null;
			}
			// Rank Math's own fallback title template when the post type has none.
			$template = '' !== $value ? $value : (string) self::rank_math_setting( "titles.pt_{$post->post_type}_{$field}", 'title' === $field ? '%title% %sep% %sitename%' : '' );
			self::rank_math_variables();
			return self::rendered( $template, static function ( $text ) use ( $post ) {
				return \RankMath\Helper::replace_vars( $text, $post );
			} );
		} catch ( \Throwable $e ) {
			return null; // a template helper that needs the front end: leave the rendered value out rather than guess
		}
	}

	/** A template run through the plugin's replacer; null when there is no template or nothing comes out. */
	private static function rendered( string $template, callable $replace ): ?string {
		if ( '' === $template ) {
			return null;
		}
		$text = self::shown( (string) $replace( $template ) );
		return '' === $text ? null : $text;
	}

	/** The shape get() returns. */
	private static function describe( string $plugin, WP_Post $post ): array {
		$values = self::read( $plugin, $post );
		$out    = array(
			'post_id'         => $post->ID,
			'plugin'          => $plugin,
			'title'           => self::text( $values['title'] ),
			'description'     => self::text( $values['description'] ),
			'focus_keyword'   => self::text( $values['focus_keyword'] ),
			'noindex'         => (bool) $values['noindex'],
			'noindex_default' => (bool) $values['noindex_default'],
		);
		if ( $values['keywords'] ) {
			$out['other_keywords'] = array_map( array( __CLASS__, 'text' ), $values['keywords'] );
		}
		$title       = self::render( $plugin, $post, 'title', $values['title'] );
		$description = self::render( $plugin, $post, 'description', $values['description'] );
		if ( null !== $title ) {
			$out['rendered_title'] = $title;
		}
		if ( null !== $description ) {
			$out['rendered_description'] = $description;
		}
		$out['title_too_long']       = self::length( $title ?? $out['title'] ) > self::TITLE_MAX;
		$out['description_too_long'] = self::length( $description ?? $out['description'] ) > self::DESCRIPTION_MAX;
		$out['search_engines']       = self::visibility( $out['noindex'], $out['noindex_default'] );
		$out['link']                 = get_permalink( $post );
		$out['edit_url']             = get_edit_post_link( $post->ID, 'raw' );
		$notes                       = array();
		if ( 'yoast' === $plugin && defined( 'RANK_MATH_VERSION' ) ) {
			$notes[] = __( 'Yoast SEO and Rank Math are both active; these are the Yoast SEO values.', 'wpzoom-ai-connect' );
		}
		if ( $out['title_too_long'] ) {
			$notes[] = sprintf( /* translators: %d: number of characters */ __( 'The title is longer than %d characters, so search results may cut it.', 'wpzoom-ai-connect' ), self::TITLE_MAX );
		}
		if ( $out['description_too_long'] ) {
			$notes[] = sprintf( /* translators: %d: number of characters */ __( 'The description is longer than %d characters, so search results may cut it.', 'wpzoom-ai-connect' ), self::DESCRIPTION_MAX );
		}
		if ( '0' === (string) get_option( 'blog_public' ) ) {
			$notes[] = __( 'WordPress is set to discourage search engines from indexing the whole site (Settings → Reading).', 'wpzoom-ai-connect' );
		}
		if ( $notes ) {
			$out['note'] = implode( ' ', $notes );
		}
		return $out;
	}

	/** The noindex flag in plain words. */
	private static function visibility( bool $noindex, bool $default ): string {
		$text = $noindex ? __( 'This page is hidden from search engines (noindex).', 'wpzoom-ai-connect' ) : __( 'Search engines may show this page.', 'wpzoom-ai-connect' );
		return $default ? $text . ' ' . __( 'This is the site default for this post type.', 'wpzoom-ai-connect' ) : $text;
	}

	/* ---------- the abilities ---------- */

	/** wpzoom/get-seo: the SEO fields of a post. Changes nothing. */
	public static function get( $post_id ) {
		$post = self::post( $post_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		$plugin = self::plugin();
		if ( 'none' !== $plugin ) {
			return self::describe( $plugin, $post );
		}
		$hidden = '0' === (string) get_option( 'blog_public' );
		return array(
			'post_id'        => $post->ID,
			'plugin'         => 'none',
			'title'          => self::shown( get_the_title( $post ) ),
			'description'    => self::shown( get_the_excerpt( $post ) ),
			'noindex'        => $hidden,
			'search_engines' => $hidden ? __( 'WordPress is set to discourage search engines from indexing the whole site (Settings → Reading).', 'wpzoom-ai-connect' ) : __( 'Search engines may show this page.', 'wpzoom-ai-connect' ),
			'link'           => get_permalink( $post ),
			'note'           => __( 'No SEO plugin is active, so there are no SEO fields. Search engines use the post title and usually a snippet of its text, shown here as title and description (the excerpt). Install Yoast SEO or Rank Math to set them.', 'wpzoom-ai-connect' ),
		);
	}

	/** wpzoom/update-seo: sets the fields that were passed, and returns get()'s shape plus the previous values. */
	public static function update( array $in ) {
		$post = self::post( $in['post_id'] ?? 0 );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		$plugin = self::plugin();
		if ( 'none' === $plugin ) {
			return new WP_Error( 'wpzoom_no_seo_plugin', __( 'No SEO plugin is active, so there are no SEO fields to set. Install and activate Yoast SEO or Rank Math (Plugins → Add New), then try again. Until then search engines use the post title and text.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
		}
		$given = array_intersect_key( $in, array_flip( self::FIELDS ) );
		if ( ! $given ) {
			return new WP_Error( 'wpzoom_nothing_to_change', __( 'Nothing to change: pass at least one of title, description, focus_keyword or noindex.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
		}

		$before = self::read( $plugin, $post );
		foreach ( $given as $field => $value ) {
			if ( 'noindex' === $field ) {
				self::write_noindex( $plugin, $post, null === $value ? null : (bool) $value );
			} else {
				self::write_text( $plugin, $post, $field, self::text( $value ) );
			}
		}
		clean_post_cache( $post->ID );

		$out      = self::describe( $plugin, $post );
		$previous = array();
		foreach ( array_keys( $given ) as $field ) {
			$previous[ $field ] = 'noindex' === $field ? ( $before['noindex_default'] ? null : (bool) $before['noindex'] ) : self::text( $before[ $field ] );
		}
		$out['changed']  = array_keys( $given );
		$out['previous'] = $previous;
		$saved           = __( 'Saved.', 'wpzoom-ai-connect' );
		if ( array_key_exists( 'noindex', $given ) ) {
			$saved .= ' ' . ( null === $given['noindex']
				/* translators: %s: the page's search engine visibility, e.g. "search engines may show this page." */
				? sprintf( __( 'Search engine visibility is back to the site default: %s', 'wpzoom-ai-connect' ), lcfirst( $out['search_engines'] ) )
				: ( $given['noindex'] ? __( 'This page is now hidden from search engines; it drops out of search results over the coming days or weeks.', 'wpzoom-ai-connect' ) : __( 'This page may now appear in search results again.', 'wpzoom-ai-connect' ) ) );
		}
		$out['note'] = $saved . ( isset( $out['note'] ) ? ' ' . $out['note'] : '' );
		return $out;
	}

	/* ---------- writing ---------- */

	private static function write_text( string $plugin, WP_Post $post, string $field, string $value ): void {
		if ( 'yoast' === $plugin ) {
			$key = self::YOAST_KEYS[ $field ];
			self::yoast_write( $post, $key, $value );
			return;
		}
		if ( 'focus_keyword' === $field ) {
			// Rank Math keeps several keywords; the first is the focus keyword. Replace that one and keep the rest.
			$keywords = self::rank_math_keywords( $post );
			$rest     = array_values( array_filter( array_slice( $keywords, 1 ), static function ( $k ) use ( $value ) {
				return strtolower( $k ) !== strtolower( $value );
			} ) );
			$value    = implode( ',', '' === $value ? $rest : array_merge( array( $value ), $rest ) );
		}
		self::meta_write( $post, self::RANK_MATH_KEYS[ $field ], $value );
	}

	/** true: noindex; false: index; null: the site default. Rank Math's other robots values are left alone. */
	private static function write_noindex( string $plugin, WP_Post $post, ?bool $noindex ): void {
		if ( 'yoast' === $plugin ) {
			self::yoast_write( $post, 'meta-robots-noindex', null === $noindex ? '' : ( $noindex ? '1' : '2' ) );
			return;
		}
		$robots = array_values( array_diff( self::rank_math_robots( $post ), array( 'noindex', 'index' ) ) );
		if ( null !== $noindex ) {
			array_unshift( $robots, $noindex ? 'noindex' : 'index' );
		}
		if ( $robots ) {
			update_post_meta( $post->ID, 'rank_math_robots', $robots );
		} else {
			delete_post_meta( $post->ID, 'rank_math_robots' );
		}
	}

	/** A Yoast field through WPSEO_Meta when it exists (Yoast's own sanitizing); an empty value deletes it. */
	private static function yoast_write( WP_Post $post, string $key, string $value ): void {
		if ( class_exists( 'WPSEO_Meta' ) ) {
			if ( '' === $value && method_exists( 'WPSEO_Meta', 'delete' ) ) {
				WPSEO_Meta::delete( $key, $post->ID );
				return;
			}
			if ( '' !== $value && method_exists( 'WPSEO_Meta', 'set_value' ) ) {
				WPSEO_Meta::set_value( $key, wp_slash( $value ), $post->ID );
				return;
			}
		}
		self::meta_write( $post, '_yoast_wpseo_' . $key, $value );
	}

	private static function meta_write( WP_Post $post, string $key, string $value ): void {
		if ( '' === $value ) {
			delete_post_meta( $post->ID, $key );
		} else {
			update_post_meta( $post->ID, $key, wp_slash( $value ) );
		}
	}
}
