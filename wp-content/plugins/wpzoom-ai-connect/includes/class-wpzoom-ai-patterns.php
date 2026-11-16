<?php
/**
 * Block patterns: the design sections the active theme ships (its `patterns/` folder and the patterns it
 * registers under its own slug) and WPZOOM's pattern plugins, listed for an AI and inserted into pages as blocks.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Patterns {

	/**
	 * Pattern categories and name prefixes of WPZOOM's pattern plugins and themes. The active theme's own
	 * patterns count whatever their prefix: Foodica registers `hero`, Tribune `tribune/...`, Twenty Twenty-Five
	 * `twentytwentyfive/...`.
	 */
	const CATEGORIES = array( 'inspiro', 'inspiro-patterns', 'wpzoom' );
	const PREFIXES   = array( 'wpzoom/', 'inspiro/', 'inspiro-patterns/', 'inspiro-lite/' );

	/**
	 * Makes sure the theme's `patterns/` folder is registered in this request. Core does it on `init`;
	 * a theme that registers late, or a request where `init` ran before the theme, leaves the registry empty.
	 */
	public static function ensure_registered(): void {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;
		if ( ! function_exists( '_register_theme_block_patterns' ) ) {
			return;
		}
		$registry = WP_Block_Patterns_Registry::get_instance();
		foreach ( $registry->get_all_registered() as $pattern ) {
			if ( self::from_theme( $pattern ) ) {
				return; // Already there.
			}
		}
		_register_theme_block_patterns(); // Skips slugs that are already registered.
	}

	/** Slugs of the pattern files in the active theme's (and its parent's) `patterns/` folder. */
	private static function theme_file_slugs(): array {
		static $slugs = null;
		if ( null !== $slugs ) {
			return $slugs;
		}
		$slugs  = array();
		$themes = array( wp_get_theme() );
		if ( $themes[0]->parent() ) {
			$themes[] = $themes[0]->parent();
		}
		foreach ( $themes as $theme ) {
			if ( ! method_exists( $theme, 'get_block_patterns' ) ) {
				continue;
			}
			foreach ( (array) $theme->get_block_patterns() as $data ) {
				if ( ! empty( $data['slug'] ) ) {
					$slugs[] = (string) $data['slug'];
				}
			}
		}
		return $slugs;
	}

	/** `slug/` prefixes the active theme (and its parent) register patterns under. */
	private static function theme_prefixes(): array {
		static $prefixes = null;
		if ( null !== $prefixes ) {
			return $prefixes;
		}
		$theme = wp_get_theme();
		$slugs = array( get_stylesheet(), get_template(), (string) $theme->get( 'TextDomain' ) );
		if ( $theme->parent() ) {
			$slugs[] = (string) $theme->parent()->get( 'TextDomain' );
		}
		$prefixes = array();
		foreach ( array_unique( array_filter( $slugs ) ) as $slug ) {
			$prefixes[] = $slug . '/';
		}
		return $prefixes;
	}

	/** Whether a registered pattern comes from the active theme: its `patterns/` folder or its own slug prefix. */
	private static function from_theme( array $pattern ): bool {
		$name = (string) ( $pattern['name'] ?? '' );
		if ( 0 === strpos( $name, 'wpzoom/' ) || in_array( $name, self::theme_file_slugs(), true ) ) {
			return true; // Foodica registers its pattern files itself, under their bare slugs (hero, about...).
		}
		foreach ( self::theme_prefixes() as $prefix ) {
			if ( 0 === strpos( $name, $prefix ) ) {
				return true;
			}
		}
		if ( ! empty( $pattern['filePath'] ) ) {
			$file = wp_normalize_path( (string) $pattern['filePath'] );
			foreach ( array_unique( array( get_stylesheet_directory(), get_template_directory() ) ) as $dir ) {
				if ( 0 === strpos( $file, trailingslashit( wp_normalize_path( $dir ) ) ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Patterns meant for templates rather than page content: full templates, header/footer parts, and
	 * patterns limited to post types other than pages and posts.
	 */
	private static function is_template_pattern( array $pattern ): bool {
		if ( ! empty( $pattern['templateTypes'] ) ) {
			return true;
		}
		foreach ( (array) ( $pattern['blockTypes'] ?? array() ) as $block_type ) {
			if ( 0 === strpos( (string) $block_type, 'core/template-part' ) ) {
				return true;
			}
		}
		$post_types = (array) ( $pattern['postTypes'] ?? array() );
		return $post_types && ! array_intersect( $post_types, array( 'page', 'post' ) );
	}

	/**
	 * @return array<int, array> Patterns, each: slug, title, description, keywords, categories, source.
	 */
	public static function all( string $search = '' ): array {
		self::ensure_registered();
		$search   = strtolower( trim( $search ) );
		$patterns = array();
		foreach ( WP_Block_Patterns_Registry::get_instance()->get_all_registered() as $pattern ) {
			if ( ! self::is_wpzoom( $pattern ) || ( isset( $pattern['inserter'] ) && false === $pattern['inserter'] ) || self::is_template_pattern( $pattern ) ) {
				continue;
			}
			$item = self::describe( $pattern );
			if ( $search ) {
				$haystack = strtolower( implode( ' ', array( $item['slug'], $item['title'], $item['description'], implode( ' ', $item['keywords'] ) ) ) );
				if ( false === strpos( $haystack, $search ) ) {
					continue;
				}
			}
			$patterns[] = $item;
		}
		usort(
			$patterns,
			static function ( $a, $b ) {
				return strcmp( $a['title'], $b['title'] );
			}
		);
		return $patterns;
	}

	/**
	 * Inserts a pattern's blocks into an existing page/post or a new draft page.
	 *
	 * @param array $args { pattern: string, page_id?: int, new_page_title?: string, position?: 'append'|'prepend' }
	 * @return array|WP_Error
	 */
	public static function insert( array $args ) {
		self::ensure_registered();
		$slug    = isset( $args['pattern'] ) ? trim( (string) $args['pattern'] ) : '';
		$pattern = $slug ? WP_Block_Patterns_Registry::get_instance()->get_registered( $slug ) : null;
		if ( ! $pattern ) {
			$known = array_column( self::all(), 'slug' );
			return new WP_Error(
				'wpzoom_unknown_pattern',
				sprintf( /* translators: 1: pattern slug, 2: the available pattern slugs, comma-separated */ __( 'Unknown pattern "%1$s". Available: %2$s.', 'wpzoom-ai-connect' ), $slug, implode( ', ', $known ) )
			);
		}

		$position = ( isset( $args['position'] ) && 'prepend' === $args['position'] ) ? 'prepend' : 'append';
		$content  = trim( (string) $pattern['content'] );
		if ( '' === $content ) {
			return new WP_Error( 'wpzoom_empty_pattern', sprintf( /* translators: %s: pattern slug */ __( 'Pattern "%s" has no content.', 'wpzoom-ai-connect' ), $slug ) );
		}

		$page_id = isset( $args['page_id'] ) ? (int) $args['page_id'] : 0;
		$created = false;
		if ( $page_id ) {
			$post = get_post( $page_id );
			if ( ! $post ) {
				return new WP_Error( 'wpzoom_page_not_found', sprintf( /* translators: %d: post ID */ __( 'No page or post with id %d.', 'wpzoom-ai-connect' ), $page_id ) );
			}
		} else {
			$title = isset( $args['new_page_title'] ) ? sanitize_text_field( (string) $args['new_page_title'] ) : '';
			if ( '' === $title ) {
				return new WP_Error( 'wpzoom_missing_target', __( 'Send page_id to add to an existing page, or new_page_title to create a draft page.', 'wpzoom-ai-connect' ) );
			}
			$page_id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'draft',
					'post_title'   => $title,
					'post_content' => '',
				),
				true
			);
			if ( is_wp_error( $page_id ) ) {
				return $page_id;
			}
			$created = true;
			$post    = get_post( $page_id );
		}

		$existing = trim( (string) $post->post_content );
		if ( '' === $existing ) {
			$new = $content;
		} elseif ( 'prepend' === $position ) {
			$new = $content . "\n\n" . $existing;
		} else {
			$new = $existing . "\n\n" . $content;
		}

		$result = wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => wp_slash( $new ),
			),
			true
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$post = get_post( $page_id );

		$blocks = array_filter(
			parse_blocks( $content ),
			static function ( $block ) {
				return ! empty( $block['blockName'] );
			}
		);

		return array(
			'page_id'      => $page_id,
			'title'        => $post->post_title,
			'status'       => $post->post_status,
			'created'      => $created,
			'pattern'      => $slug,
			'position'     => $position,
			'blocks_added' => count( $blocks ),
			'edit_url'     => get_edit_post_link( $page_id, 'raw' ),
			'preview_url'  => 'publish' === $post->post_status ? get_permalink( $page_id ) : get_preview_post_link( $page_id ),
			'note'         => __( 'The pattern carries demo text and images. Replace them with the site\'s own content, then publish the page.', 'wpzoom-ai-connect' ),
		);
	}

	/** Whether a registered pattern is one to offer: the active theme's own, or from a WPZOOM pattern plugin. */
	private static function is_wpzoom( array $pattern ): bool {
		if ( self::from_theme( $pattern ) ) {
			return true;
		}
		$name = (string) ( $pattern['name'] ?? '' );
		foreach ( self::PREFIXES as $prefix ) {
			if ( 0 === strpos( $name, $prefix ) ) {
				return true;
			}
		}
		$categories = isset( $pattern['categories'] ) ? (array) $pattern['categories'] : array();
		return (bool) array_intersect( $categories, self::CATEGORIES );
	}

	private static function describe( array $pattern ): array {
		$name = (string) $pattern['name'];
		return array(
			'slug'        => $name,
			'title'       => trim( wp_strip_all_tags( (string) ( $pattern['title'] ?? $name ) ) ),
			'description' => trim( wp_strip_all_tags( (string) ( $pattern['description'] ?? '' ) ) ),
			'keywords'    => array_values( array_map( 'strval', (array) ( $pattern['keywords'] ?? array() ) ) ),
			'categories'  => array_values( array_map( 'strval', (array) ( $pattern['categories'] ?? array() ) ) ),
			'source'      => self::from_theme( $pattern ) ? 'theme' : 'plugin',
		);
	}
}
