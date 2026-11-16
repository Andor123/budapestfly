<?php
/**
 * Catalog sections on any theme: the glue between the catalog client, the renderer and the page.
 *
 * Ported from the catalog parts of Inspiro Starter Sites 1.5.1, components/importer/inc/Ai/AiPageBuilder.php
 * (catalog_index, preview_markup, example_content, site_has, site_colors, palette, css, mark_page, print_css,
 * enqueue_editor_css, catalog_asset, placeholder_pool / pool_resolver), changed where a site without Inspiro
 * needs something else: the palette comes from the active theme's theme.json, photos come from the site's own
 * media before a neutral placeholder, block themes keep their own button style, and the stylesheet also supplies
 * the Inspiro preset colors, spacing and font sizes the sections were designed with when the active theme lacks
 * them. When Inspiro Starter Sites is active, WPZOOM_AI_Starter_Sites uses its AiPageBuilder instead of this.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Sections {

	/** Post meta: this page holds catalog sections (needs the stylesheet). */
	const PAGE_META_KEY = '_wpzoom_ai_catalog_page';

	/** Starter Sites' marker for the same thing: its stylesheet applies when it is active. */
	const STARTER_SITES_META_KEY = '_inspiro_starter_sites_ai_page';

	/** Option: demo asset URL => attachment id (icons and logos a few sections carry). */
	const ASSETS_OPTION = 'wpzoom_ai_catalog_assets';

	/** Starter Sites' map of the same assets, reused read-only when it has one. */
	const STARTER_SITES_ASSETS_OPTION = 'inspiro_starter_sites_ai_catalog_assets';

	/** Option: orientation => attachment id of the neutral placeholder image. */
	const PLACEHOLDERS_OPTION = 'wpzoom_ai_catalog_placeholders';

	/** Attachment meta on placeholders and sideloaded assets, so they are never picked as "the site's photos". */
	const PLACEHOLDER_META = '_wpzoom_ai_placeholder';

	/** The site's newest photos considered for the sections. */
	const SITE_MEDIA_LIMIT = 12;

	/** Placeholder sizes per orientation. */
	const PLACEHOLDER_SIZES = array(
		'landscape' => array( 1200, 800 ),
		'portrait'  => array( 800, 1000 ),
		'square'    => array( 900, 900 ),
	);

	/**
	 * The presets the sections were designed with (both Inspiro theme.json files). Written into the stylesheet,
	 * scoped to the sections, for every slug the active theme does not register. primary, secondary,
	 * header-footer, brown and green come from the palette (see WPZOOM_AI_Catalog::css()).
	 */
	const INSPIRO_COLORS = array(
		'tertiary'         => '#6c6c77',
		'lightgrey'        => '#d9d9d9',
		'foreground'       => '#000000',
		'background'       => '#f9fafd',
		'white'            => '#ffffff',
		'light-background' => '#ffffff',
		'black'            => '#101010',
		'titan-white'      => '#e0d8e2',
	);
	const INSPIRO_SPACING = array(
		'x-small' => '20px',
		'small'   => '40px',
		'medium'  => '60px',
		'large'   => '80px',
		'x-large' => '100px',
	);
	const INSPIRO_FONT_SIZES = array(
		'x-small'  => '14px',
		'small'    => '16px',
		'medium'   => '20px',
		'large'    => '36px',
		'x-large'  => '42px',
		'xx-large' => '72px',
		'max-36'   => '36px',
		'max-48'   => '48px',
		'max-60'   => '60px',
		'max-72'   => '72px',
	);
	const INSPIRO_FONT_FAMILIES = array(
		'syne'    => 'Syne, sans-serif',
		'dm-sans' => '"DM Sans", sans-serif',
	);

	/** @var string[]|null */
	private static $has = null;

	/** @var array|null */
	private static $colors = null;

	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'print_css' ), 101 );
		// enqueue_block_assets, not enqueue_block_editor_assets: block themes render the editor in an iframe and
		// only the former reaches it.
		add_action( 'enqueue_block_assets', array( __CLASS__, 'enqueue_editor_css' ), 20 );
	}

	/* ------------------------------------------------------------------
	 * Index
	 * ---------------------------------------------------------------- */

	/**
	 * Every section with what the AI needs to pick one, as wpzoom/list-sections returns it.
	 *
	 * @return array[]
	 */
	public static function index() {
		$meta = WPZOOM_AI_Catalog::meta();
		if ( ! $meta ) {
			return array();
		}
		$has      = self::site_has();
		$licensed = '' !== WPZOOM_AI_Catalog::premium_license();
		$out      = array();
		foreach ( $meta as $id => $section ) {
			$needs  = isset( $section['needs'] ) ? (array) $section['needs'] : array();
			$locked = ! $licensed && isset( $section['tier'] ) && 'premium' === $section['tier'];
			$out[]  = array(
				'id'          => (string) $id,
				'role'        => isset( $section['role'] ) ? (string) $section['role'] : '',
				'fits'        => isset( $section['fits'] ) ? $section['fits'] : 'any',
				'tone'        => isset( $section['tone'] ) ? $section['tone'] : 'light',
				'tier'        => isset( $section['tier'] ) ? $section['tier'] : 'lite',
				'description' => isset( $section['description'] ) ? (string) $section['description'] : '',
				'photos'      => isset( $section['photos'] ) ? (int) $section['photos'] : 0,
				'items'       => isset( $section['items'] ) ? (int) $section['items'] : 0,
				'needs'       => array_values( $needs ),
				'locked'      => $locked,
				'available'   => ! $locked && ! array_diff( $needs, $has ),
			);
		}
		return $out;
	}

	/**
	 * Section needs this site satisfies (see the catalog's `needs`).
	 *
	 * @return string[]
	 */
	public static function site_has() {
		if ( null !== self::$has ) {
			return self::$has;
		}
		$has    = array();
		$counts = wp_count_posts( 'post' );
		if ( $counts && ! empty( $counts->publish ) ) {
			$has[] = 'posts';
		}
		if ( self::first_form_id() ) {
			$has[] = 'wpzoom-forms';
		}
		if ( post_type_exists( 'portfolio_item' ) ) {
			$has[] = 'wpzoom-portfolio';
		}
		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'outermost/icon-block' ) ) {
			$has[] = 'icon-block';
		}
		self::$has = $has;
		return $has;
	}

	/** What a need means for the user, for "this section needs …" answers. */
	public static function describe_need( $need ) {
		switch ( $need ) {
			case 'posts':
				return __( 'at least one published post (it lists the latest posts)', 'wpzoom-ai-connect' );
			case 'wpzoom-forms':
				return __( 'the WPZOOM Forms plugin with a form (it embeds a contact form)', 'wpzoom-ai-connect' );
			case 'wpzoom-portfolio':
				return __( 'the WPZOOM Portfolio plugin (it shows portfolio items)', 'wpzoom-ai-connect' );
			case 'icon-block':
				return __( 'the Icon Block plugin (free on wordpress.org)', 'wpzoom-ai-connect' );
		}
		return (string) $need;
	}

	/* ------------------------------------------------------------------
	 * Rendering
	 * ---------------------------------------------------------------- */

	/**
	 * Renders sections with the demo's example copy, in the order given.
	 *
	 * @param string[] $ids       Section ids.
	 * @param array    $headlines id => headline that replaces the heading when the field has room for it.
	 * @return array {
	 *     @type array $markup       id => block markup, for the ids that rendered.
	 *     @type array $skipped      id => reason, for the ids that did not.
	 *     @type array $placeholders ids whose photos are the neutral placeholder.
	 *     @type array $site_media   ids whose photos came from the site's own Media Library.
	 * }
	 */
	public static function render( array $ids, array $headlines = array() ) {
		$out = array(
			'markup'       => array(),
			'skipped'      => array(),
			'placeholders' => array(),
			'site_media'   => array(),
		);
		$ids = array_values( array_unique( array_filter( array_map( 'sanitize_key', $ids ) ) ) );
		if ( ! $ids ) {
			return $out;
		}

		$meta     = WPZOOM_AI_Catalog::meta();
		$has      = self::site_has();
		$licensed = '' !== WPZOOM_AI_Catalog::premium_license();
		$wanted   = array();
		foreach ( $ids as $id ) {
			if ( ! isset( $meta[ $id ] ) ) {
				$out['skipped'][ $id ] = __( 'unknown section id (not in wpzoom/list-sections)', 'wpzoom-ai-connect' );
				continue;
			}
			if ( ! $licensed && isset( $meta[ $id ]['tier'] ) && 'premium' === $meta[ $id ]['tier'] ) {
				$out['skipped'][ $id ] = __( 'a premium section: it needs an active Inspiro Premium license on this site', 'wpzoom-ai-connect' );
				continue;
			}
			$unmet = array_diff( isset( $meta[ $id ]['needs'] ) ? (array) $meta[ $id ]['needs'] : array(), $has );
			if ( $unmet ) {
				$out['skipped'][ $id ] = sprintf(
					/* translators: %s: what the section needs, e.g. "the Icon Block plugin". */
					__( 'needs %s', 'wpzoom-ai-connect' ),
					implode( '; ', array_map( array( __CLASS__, 'describe_need' ), $unmet ) )
				);
				continue;
			}
			$wanted[] = $id;
		}
		if ( ! $wanted ) {
			return $out;
		}

		$full = WPZOOM_AI_Catalog::sections( $wanted );
		foreach ( $wanted as $id ) {
			if ( ! isset( $full[ $id ] ) ) {
				$out['skipped'][ $id ] = __( 'the section could not be fetched from ai.wpzoom.com right now', 'wpzoom-ai-connect' );
			}
		}
		if ( ! $full ) {
			return $out;
		}

		$images   = self::image_resolver();
		$renderer = new WPZOOM_AI_Section_Renderer(
			array(
				'palette'         => self::palette(),
				'links'           => self::links(),
				'resolve_image'   => $images['resolve'],
				'resolve_asset'   => array( __CLASS__, 'asset' ),
				'fallback_query'  => 'placeholder',
				'form_id'         => self::first_form_id(),
				'rebuild_buttons' => ! self::theme_has_buttons(),
			)
		);
		foreach ( $wanted as $id ) {
			if ( ! isset( $full[ $id ] ) ) {
				continue;
			}
			$section = $full[ $id ];
			$content = self::example_content( isset( $section['fields'] ) ? (array) $section['fields'] : array() );
			if ( ! empty( $headlines[ $id ] ) && isset( $section['fields']['heading'] )
				&& ( empty( $section['fields']['heading']['words'] ) || (int) $section['fields']['heading']['words'] <= 16 ) ) {
				$content['heading'] = (string) $headlines[ $id ];
			}
			$images['reset']();
			$markup = $renderer->render( $section, $content );
			if ( '' === trim( (string) $markup ) ) {
				$out['skipped'][ $id ] = __( 'nothing to render', 'wpzoom-ai-connect' );
				continue;
			}
			$out['markup'][ $id ] = $markup;
			$used                 = $images['used']();
			if ( $used['placeholder'] ) {
				$out['placeholders'][] = $id;
			}
			if ( $used['site'] ) {
				$out['site_media'][] = $id;
			}
		}
		return $out;
	}

	/**
	 * The demo's original copy for a section's fields, in the shape the renderer expects.
	 *
	 * @param array $fields Catalog field definitions.
	 * @return array
	 */
	public static function example_content( array $fields ) {
		$content = array();
		foreach ( $fields as $name => $f ) {
			$type = isset( $f['type'] ) ? $f['type'] : 'text';
			switch ( $type ) {
				case 'text':
					$content[ $name ] = isset( $f['example'] ) ? (string) $f['example'] : '';
					break;
				case 'button':
					$content[ $name ] = array(
						'label' => isset( $f['example'] ) ? (string) $f['example'] : '',
						'href'  => '#',
					);
					break;
				case 'image':
					$content[ $name ] = 'placeholder ' . $name;
					break;
				case 'list':
					$content[ $name ] = isset( $f['example'] ) ? (array) $f['example'] : array();
					break;
				case 'gallery':
					$count            = isset( $f['min'] ) ? max( 1, (int) $f['min'] ) : 3;
					$content[ $name ] = array_map(
						static function ( $i ) {
							return 'placeholder ' . $i;
						},
						range( 1, $count )
					);
					break;
				case 'items':
					$count            = isset( $f['count'] ) ? (int) $f['count'] : ( isset( $f['min'] ) ? (int) $f['min'] : 3 );
					$count            = max( isset( $f['min'] ) ? (int) $f['min'] : 1, min( isset( $f['max'] ) ? (int) $f['max'] : 6, $count ) );
					$item             = self::example_content( isset( $f['fields'] ) ? (array) $f['fields'] : array() );
					$content[ $name ] = array_fill( 0, max( 1, $count ), $item );
					break;
			}
		}
		return $content;
	}

	/** Page slug => URL for the renderer's "#page:<slug>" button targets and the default call-to-action link. */
	private static function links() {
		$links   = array( 'home' => home_url( '/' ) );
		$contact = get_page_by_path( 'contact' );
		if ( $contact && 'publish' === $contact->post_status ) {
			$links['contact'] = get_permalink( $contact );
		}
		return $links;
	}

	/** The form contact sections embed: the first published WPZOOM Forms form. */
	public static function first_form_id() {
		if ( ! post_type_exists( 'wpzf-form' ) ) {
			return 0;
		}
		$forms = get_posts(
			array(
				'post_type'   => 'wpzf-form',
				'post_status' => 'publish',
				'numberposts' => 1,
				'orderby'     => 'ID',
				'order'       => 'ASC',
				'fields'      => 'ids',
			)
		);
		return $forms ? (int) $forms[0] : 0;
	}

	/* ------------------------------------------------------------------
	 * Images
	 * ---------------------------------------------------------------- */

	/**
	 * Photo resolver for inserted sections: the site's own photos of the slot's orientation, cycling through the
	 * newest ones; otherwise one neutral placeholder per orientation, sideloaded once. Returns the resolver and
	 * two helpers that track, per section, which source was used.
	 *
	 * @return array{resolve:callable,reset:callable,used:callable}
	 */
	private static function image_resolver() {
		$media   = null;
		$cursors = array();
		$used    = array(
			'site'        => false,
			'placeholder' => false,
		);
		return array(
			'resolve' => static function ( $query, $orientation ) use ( &$media, &$cursors, &$used ) {
				if ( null === $media ) {
					$media = self::site_media();
				}
				$orientation = isset( self::PLACEHOLDER_SIZES[ $orientation ] ) ? $orientation : 'landscape';
				if ( ! empty( $media[ $orientation ] ) ) {
					$key                     = isset( $cursors[ $orientation ] ) ? $cursors[ $orientation ] : 0;
					$cursors[ $orientation ] = $key + 1;
					$used['site']            = true;
					return $media[ $orientation ][ $key % count( $media[ $orientation ] ) ];
				}
				$placeholder = self::placeholder( $orientation );
				if ( $placeholder ) {
					$used['placeholder'] = true;
				}
				return $placeholder;
			},
			'reset'   => static function () use ( &$used ) {
				$used = array(
					'site'        => false,
					'placeholder' => false,
				);
			},
			'used'    => static function () use ( &$used ) {
				return $used;
			},
		);
	}

	/**
	 * The site's newest photos by orientation (JPEG, PNG or WebP of at least 400px, not the plugin's own
	 * placeholders or sideloaded demo assets).
	 *
	 * @return array orientation => [ { id, url } ]
	 */
	public static function site_media() {
		$out = array(
			'landscape' => array(),
			'portrait'  => array(),
			'square'    => array(),
		);
		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => array( 'image/jpeg', 'image/png', 'image/webp' ),
				'numberposts'    => self::SITE_MEDIA_LIMIT * 2,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- a handful of recent attachments.
					array(
						'key'     => self::PLACEHOLDER_META,
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);
		$count = 0;
		foreach ( (array) $ids as $id ) {
			$meta = wp_get_attachment_metadata( (int) $id );
			if ( ! is_array( $meta ) || empty( $meta['width'] ) || empty( $meta['height'] ) || min( (int) $meta['width'], (int) $meta['height'] ) < 400 ) {
				continue;
			}
			$url = wp_get_attachment_image_url( (int) $id, 'full' );
			if ( ! $url ) {
				continue;
			}
			$ratio = (int) $meta['width'] / (int) $meta['height'];
			$key   = $ratio > 1.15 ? 'landscape' : ( $ratio < 0.87 ? 'portrait' : 'square' );
			$out[ $key ][] = array(
				'id'  => (int) $id,
				'url' => $url,
			);
			if ( ++$count >= self::SITE_MEDIA_LIMIT ) {
				break;
			}
		}
		return $out;
	}

	/**
	 * The neutral placeholder image for an orientation: a soft grey gradient made with GD and added to the Media
	 * Library once (never a hotlinked stock photo on a live page).
	 *
	 * @param string $orientation landscape|portrait|square.
	 * @return array|null { id, url }
	 */
	public static function placeholder( $orientation ) {
		$orientation = isset( self::PLACEHOLDER_SIZES[ $orientation ] ) ? $orientation : 'landscape';
		$stored      = get_option( self::PLACEHOLDERS_OPTION, array() );
		$stored      = is_array( $stored ) ? $stored : array();
		if ( ! empty( $stored[ $orientation ] ) && 'attachment' === get_post_type( (int) $stored[ $orientation ] ) ) {
			$url = wp_get_attachment_image_url( (int) $stored[ $orientation ], 'full' );
			if ( $url ) {
				return array(
					'id'  => (int) $stored[ $orientation ],
					'url' => $url,
				);
			}
		}
		if ( ! current_user_can( 'upload_files' ) ) {
			return null;
		}
		$file = self::draw_placeholder( $orientation );
		if ( ! $file ) {
			return null;
		}
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		$id = media_handle_sideload(
			array(
				'name'     => 'wpzoom-placeholder-' . $orientation . '.jpg',
				'tmp_name' => $file,
			),
			0,
			/* translators: %s: landscape, portrait or square. */
			sprintf( __( 'Placeholder image (%s)', 'wpzoom-ai-connect' ), $orientation ),
			array( 'post_content' => __( 'A neutral stand-in added by WPZOOM AI Connect. Replace it with your own photo.', 'wpzoom-ai-connect' ) )
		);
		if ( is_wp_error( $id ) ) {
			@unlink( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
			return null;
		}
		update_post_meta( $id, self::PLACEHOLDER_META, $orientation );
		update_post_meta( $id, '_wp_attachment_image_alt', __( 'Placeholder image', 'wpzoom-ai-connect' ) );
		$stored[ $orientation ] = (int) $id;
		update_option( self::PLACEHOLDERS_OPTION, $stored, false );
		$url = wp_get_attachment_image_url( (int) $id, 'full' );
		return $url ? array(
			'id'  => (int) $id,
			'url' => $url,
		) : null;
	}

	/**
	 * Draws the placeholder JPEG into a temporary file.
	 *
	 * @param string $orientation Orientation.
	 * @return string|false Path.
	 */
	private static function draw_placeholder( $orientation ) {
		if ( ! function_exists( 'imagecreatetruecolor' ) || ! function_exists( 'imagejpeg' ) ) {
			return false;
		}
		list( $w, $h ) = self::PLACEHOLDER_SIZES[ $orientation ];
		$img = imagecreatetruecolor( $w, $h );
		if ( ! $img ) {
			return false;
		}
		// A diagonal gradient from light to slightly darker grey: quiet in any layout, unmistakably a stand-in.
		$steps = $w + $h;
		for ( $i = 0; $i < $steps; $i += 2 ) {
			$t     = $i / $steps;
			$shade = (int) round( 233 - 28 * $t );
			$color = imagecolorallocate( $img, $shade, $shade, $shade + 3 );
			imageline( $img, $i, 0, 0, $i, $color );
			imageline( $img, $i + 1, 0, 0, $i + 1, $color );
		}
		// A soft lighter disc, so the image reads as an illustration rather than a broken file.
		$disc = imagecolorallocatealpha( $img, 255, 255, 255, 96 );
		imagefilledellipse( $img, (int) ( $w * 0.62 ), (int) ( $h * 0.42 ), (int) ( min( $w, $h ) * 0.55 ), (int) ( min( $w, $h ) * 0.55 ), $disc );
		$file = wp_tempnam( 'wpzoom-placeholder-' . $orientation . '.jpg' );
		if ( ! $file ) {
			imagedestroy( $img );
			return false;
		}
		$ok = imagejpeg( $img, $file, 82 );
		imagedestroy( $img );
		return $ok ? $file : false;
	}

	/**
	 * A demo's static asset (icon, logo) copied into this site's Media Library once and reused. Starter Sites'
	 * copy is reused when that plugin already made one.
	 *
	 * @param string $url Source URL on the demo server.
	 * @return array|null { id, url }
	 */
	public static function asset( $url ) {
		$url = (string) $url;
		$map = get_option( self::ASSETS_OPTION, array() );
		$map = is_array( $map ) ? $map : array();
		foreach ( array( $map, (array) get_option( self::STARTER_SITES_ASSETS_OPTION, array() ) ) as $source ) {
			if ( isset( $source[ $url ] ) && 'attachment' === get_post_type( (int) $source[ $url ] ) ) {
				return array(
					'id'  => (int) $source[ $url ],
					'url' => wp_get_attachment_url( (int) $source[ $url ] ),
				);
			}
		}
		if ( ! current_user_can( 'upload_files' ) || ! wp_http_validate_url( $url ) ) {
			return null;
		}
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		$id = media_sideload_image( esc_url_raw( $url ), 0, null, 'id' );
		if ( is_wp_error( $id ) ) {
			return null;
		}
		update_post_meta( (int) $id, self::PLACEHOLDER_META, 'asset' );
		$map[ $url ] = (int) $id;
		update_option( self::ASSETS_OPTION, $map, false );
		return array(
			'id'  => (int) $id,
			'url' => wp_get_attachment_url( (int) $id ),
		);
	}

	/* ------------------------------------------------------------------
	 * Palette and stylesheet
	 * ---------------------------------------------------------------- */

	/**
	 * The two colors the sections are built from, read from the active theme.
	 *
	 * Inspiro Premium: primary is the accent, secondary near-black (the Customizer's accent wins). Inspiro Lite:
	 * primary near-black, secondary the accent (the Customizer's color scheme wins). Any other theme: the accent
	 * is the theme palette's primary, accent, brand or contrast color, else its button background, else teal;
	 * the dark color is the theme's text color when it is dark, else near-black.
	 *
	 * @return array [ 'primary' => hex, 'secondary' => hex, 'premium' => bool, 'inspiro' => bool, 'source' => string ]
	 */
	public static function site_colors() {
		if ( null !== self::$colors ) {
			return self::$colors;
		}
		$premium = WPZOOM_AI_Catalog::is_premium_inspiro();
		$lite    = WPZOOM_AI_Catalog::is_inspiro_lite();
		$palette = self::theme_palette();

		if ( $premium || $lite ) {
			$colors = $premium
				? array( 'primary' => '#0bb4aa', 'secondary' => '#101010' )
				: array( 'primary' => '#101010', 'secondary' => '#0bb4aa' );
			foreach ( $palette as $slug => $hex ) {
				if ( isset( $colors[ $slug ] ) ) {
					$colors[ $slug ] = $hex;
				}
			}
			if ( $premium ) {
				$mod = sanitize_hex_color( (string) get_theme_mod( 'color-accent', '' ) );
				if ( $mod ) {
					$colors['primary'] = strtolower( $mod );
				}
			} else {
				$mod = function_exists( 'inspiro_get_theme_mod' ) ? inspiro_get_theme_mod( 'colorscheme_hex' ) : get_theme_mod( 'colorscheme_hex', '' );
				$mod = sanitize_hex_color( (string) $mod );
				if ( $mod && 'custom' === get_theme_mod( 'colorscheme', '' ) ) {
					$colors['secondary'] = strtolower( $mod );
				}
			}
			$source = $premium ? 'inspiro-premium' : 'inspiro-lite';
		} else {
			$accent = '';
			$source = 'default';
			foreach ( array( 'primary', 'accent', 'brand', 'contrast' ) as $slug ) {
				if ( isset( $palette[ $slug ] ) ) {
					$accent = $palette[ $slug ];
					$source = 'theme.json palette (' . $slug . ')';
					break;
				}
			}
			if ( '' === $accent ) {
				$button = self::resolve_preset( wp_get_global_styles( array( 'elements', 'button', 'color', 'background' ) ), $palette );
				if ( $button ) {
					$accent = $button;
					$source = 'theme.json button';
				}
			}
			$text = self::resolve_preset( wp_get_global_styles( array( 'color', 'text' ) ), $palette );
			$dark = $text && WPZOOM_AI_Catalog::luminance( $text ) < 100 ? $text : '#101010';
			if ( '' === $accent ) {
				$accent = '#0bb4aa';
			}
			$colors = array(
				'primary'   => $accent,
				'secondary' => $dark,
			);
		}

		$colors['premium'] = $premium;
		$colors['inspiro'] = $premium || $lite;
		$colors['source']  = $source;
		self::$colors      = $colors;
		return $colors;
	}

	/** The active theme's palette, slug => lowercase hex (entries that are not plain hex colors are left out). */
	private static function theme_palette() {
		$out     = array();
		$palette = function_exists( 'wp_get_global_settings' ) ? wp_get_global_settings( array( 'color', 'palette' ) ) : array();
		foreach ( array( 'theme', 'custom' ) as $origin ) {
			foreach ( ( is_array( $palette ) && ! empty( $palette[ $origin ] ) ) ? (array) $palette[ $origin ] : array() as $entry ) {
				if ( isset( $entry['slug'], $entry['color'] ) ) {
					$hex = sanitize_hex_color( (string) $entry['color'] );
					if ( $hex ) {
						$out[ (string) $entry['slug'] ] = strtolower( strlen( $hex ) === 4 ? '#' . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3] : $hex );
					}
				}
			}
		}
		return $out;
	}

	/** A theme.json color value as hex: a hex, or a var:preset|color|slug / var(--wp--preset--color--slug) reference. */
	private static function resolve_preset( $value, array $palette ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return '';
		}
		$hex = sanitize_hex_color( $value );
		if ( $hex ) {
			return strtolower( $hex );
		}
		if ( preg_match( '/^var:preset\|color\|([a-z0-9-]+)$/i', $value, $m ) || preg_match( '/^var\(--wp--preset--color--([a-z0-9-]+)\)$/i', $value, $m ) ) {
			return isset( $palette[ strtolower( $m[1] ) ] ) ? $palette[ strtolower( $m[1] ) ] : '';
		}
		return '';
	}

	/** The palette the renderer and the stylesheet share. */
	public static function palette() {
		$colors = self::site_colors();
		$dark   = $colors['primary'];
		// The darker of the two site colors carries dark sections and text.
		if ( WPZOOM_AI_Catalog::luminance( $colors['secondary'] ) < WPZOOM_AI_Catalog::luminance( $colors['primary'] ) ) {
			$dark = $colors['secondary'];
		}
		return WPZOOM_AI_Catalog::palette( $colors['primary'], $dark );
	}

	/**
	 * Whether the active theme styles buttons itself (theme.json styles.elements.button on a block theme): then
	 * the sections' buttons become plain core buttons the theme draws, instead of the catalog's button style.
	 *
	 * @return bool
	 */
	public static function theme_has_buttons() {
		if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
			return false;
		}
		$button = wp_get_global_styles( array( 'elements', 'button' ) );
		return is_array( $button ) && ! empty( $button );
	}

	/**
	 * The stylesheet for pages that hold catalog sections.
	 *
	 * On Inspiro the sections join the theme's own palette, so the catalog's per-theme preset overrides are
	 * dropped and only sections from the other Inspiro edition get primary/secondary swapped (the two editions
	 * swap their meaning). On any other theme the overrides stay (they define primary, secondary and the other
	 * Inspiro slugs from the palette) and the Inspiro presets the theme lacks are added, scoped to the sections.
	 *
	 * @return string
	 */
	public static function css() {
		static $css = null;
		if ( null !== $css ) {
			return $css;
		}
		$colors  = self::site_colors();
		$palette = self::palette();
		$css     = WPZOOM_AI_Catalog::css( $palette );

		if ( $colors['inspiro'] ) {
			$css     = preg_replace( '/\.iss-ai-cs--(?:lite|premium)\{[^}]*\}/', '', $css );
			$foreign = $colors['premium'] ? 'lite' : 'premium';
			$swap    = '--wp--preset--color--primary:' . $colors['secondary'] . ';--wp--preset--color--secondary:' . $colors['primary'];
			if ( ! $colors['premium'] ) {
				// Premium demos also use these two named colors, which Lite lacks.
				$swap .= ';--wp--preset--color--green:' . $colors['secondary'] . ';--wp--preset--color--brown:' . $colors['secondary'];
			}
			$css = '.iss-ai-cs--' . $foreign . '{' . $swap . '}' . $css;
		} else {
			$css = self::preset_css( $palette ) . $css;
		}
		if ( self::theme_has_buttons() ) {
			// The theme's buttons stay; the catalog's button rules target .iss-btn, which the renderer did not add.
			$css = preg_replace( '/:root\{--iss-btn[^}]*\}/', '', $css );
		}
		return $css;
	}

	/**
	 * Inspiro's preset variables and classes for the slugs the active theme does not register, scoped to the
	 * sections. Core writes `.has-<slug>-background-color` rules only for registered presets, so without these a
	 * section's `tertiary` panel or `x-small` padding would render as nothing.
	 *
	 * @param array $palette Role => hex.
	 * @return string
	 */
	private static function preset_css( array $palette ) {
		$settings = function_exists( 'wp_get_global_settings' ) ? wp_get_global_settings() : array();
		$slugs    = static function ( $group, $key ) use ( $settings ) {
			$out  = array();
			$list = isset( $settings[ $group ][ $key ] ) ? $settings[ $group ][ $key ] : array();
			foreach ( array( 'default', 'theme', 'custom' ) as $origin ) {
				foreach ( isset( $list[ $origin ] ) && is_array( $list[ $origin ] ) ? $list[ $origin ] : array() as $entry ) {
					if ( isset( $entry['slug'] ) ) {
						$out[] = (string) $entry['slug'];
					}
				}
			}
			return $out;
		};

		$vars  = '';
		$rules = '';

		$theme_colors = $slugs( 'color', 'palette' );
		$colors       = array_merge(
			self::INSPIRO_COLORS,
			array(
				'primary'       => $palette['dark'],
				'secondary'     => $palette['accent'],
				'header-footer' => $palette['dark'],
				'brown'         => $palette['accent'],
				'green'         => $palette['accent'],
			)
		);
		foreach ( $colors as $slug => $hex ) {
			if ( in_array( $slug, $theme_colors, true ) ) {
				continue;
			}
			if ( ! in_array( $slug, array( 'primary', 'secondary', 'header-footer', 'brown', 'green' ), true ) ) {
				$vars .= '--wp--preset--color--' . $slug . ':' . $hex . ';';
			}
			$rules .= '.iss-ai-cs .has-' . $slug . '-color{color:var(--wp--preset--color--' . $slug . ')!important}'
				. '.iss-ai-cs .has-' . $slug . '-background-color{background-color:var(--wp--preset--color--' . $slug . ')!important}'
				. '.iss-ai-cs .has-' . $slug . '-border-color{border-color:var(--wp--preset--color--' . $slug . ')!important}';
		}

		$theme_spacing = $slugs( 'spacing', 'spacingSizes' );
		foreach ( self::INSPIRO_SPACING as $slug => $size ) {
			if ( ! in_array( $slug, $theme_spacing, true ) ) {
				$vars .= '--wp--preset--spacing--' . $slug . ':' . $size . ';';
			}
		}

		$theme_sizes = $slugs( 'typography', 'fontSizes' );
		foreach ( self::INSPIRO_FONT_SIZES as $slug => $size ) {
			if ( in_array( $slug, $theme_sizes, true ) ) {
				continue;
			}
			$vars  .= '--wp--preset--font-size--' . $slug . ':' . $size . ';';
			$rules .= '.iss-ai-cs .has-' . $slug . '-font-size{font-size:var(--wp--preset--font-size--' . $slug . ')!important}';
		}

		$theme_families = $slugs( 'typography', 'fontFamilies' );
		foreach ( self::INSPIRO_FONT_FAMILIES as $slug => $family ) {
			if ( in_array( $slug, $theme_families, true ) ) {
				continue;
			}
			$vars  .= '--wp--preset--font-family--' . $slug . ':' . $family . ';';
			$rules .= '.iss-ai-cs .has-' . $slug . '-font-family{font-family:var(--wp--preset--font-family--' . $slug . ')!important}';
		}

		return ( '' !== $vars ? '.iss-ai-cs{' . $vars . '}' : '' ) . $rules;
	}

	/* ------------------------------------------------------------------
	 * Pages
	 * ---------------------------------------------------------------- */

	/**
	 * Remembers that a page holds catalog sections, so it gets the stylesheet. With Starter Sites active its own
	 * marker is set instead and its stylesheet applies.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function mark_page( $post_id ) {
		if ( class_exists( '\Inspiro\Starter_Sites\Ai\AiPageBuilder' ) ) {
			\Inspiro\Starter_Sites\Ai\AiPageBuilder::get_instance()->mark_page( (int) $post_id );
			return;
		}
		update_post_meta( (int) $post_id, self::PAGE_META_KEY, 1 );
	}

	/** Whether this plugin, not Starter Sites, styles the page. */
	private static function styles_page( $post_id ) {
		$post_id = (int) $post_id;
		if ( ! $post_id || ! get_post_meta( $post_id, self::PAGE_META_KEY, true ) ) {
			return false;
		}
		// Starter Sites prints its own stylesheet on pages it marked; one is enough.
		return ! ( class_exists( '\Inspiro\Starter_Sites\Ai\AiPageBuilder' ) && get_post_meta( $post_id, self::STARTER_SITES_META_KEY, true ) );
	}

	public static function print_css() {
		if ( ! is_singular() || ! self::styles_page( get_queried_object_id() ) ) {
			return;
		}
		echo "\n<style id=\"wpzoom-ai-catalog-css\">\n" . wp_strip_all_tags( self::css() ) . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from hex colors and fixed rules.
	}

	public static function enqueue_editor_css() {
		if ( ! is_admin() ) {
			return;
		}
		$post = get_post();
		if ( ! $post || ! in_array( $post->post_type, array( 'page', 'post' ), true ) || ! self::styles_page( $post->ID ) ) {
			return;
		}
		wp_register_style( 'wpzoom-ai-catalog', false, array(), WPZOOM_AI_CONNECT_VERSION );
		wp_enqueue_style( 'wpzoom-ai-catalog' );
		wp_add_inline_style( 'wpzoom-ai-catalog', self::css() );
	}
}
