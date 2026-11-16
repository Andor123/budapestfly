<?php
/**
 * Finds common accessibility problems on a published page, read the way a visitor's browser gets it (header, menus
 * and footer included), and names the fix and the tool that makes it for each. Changes nothing itself: every fix goes
 * through a tool that already exists, so the AI explains it and the user confirms.
 *
 * Seven checks, chosen because they are cheap to find in HTML and an existing tool can fix them: image_alt,
 * link_text, heading_order, form_labels, page_language, iframe_title, and color_contrast, which compares the theme's
 * own text and background color settings (WPZOOM framework Customizer settings, or block-theme global styles) and the
 * colors set on blocks in the page content (has-…-color classes and inline colors, resolved through the palette the
 * page itself prints) and, on pages built with Elementor, its widgets' colors read from Elementor's own data
 * (WPZOOM_AI_Elementor::color_pairs()), rather than guessing from CSS. Keyboard traps, focus order, ARIA misuse, captions and anything needing a browser are out of
 * scope; every result says an automated check does not cover everything.
 *
 * analyze() takes HTML and returns findings, so the checks are tested with fixture HTML; check() fetches the page.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Accessibility {

	const CHECKS = array( 'image_alt', 'link_text', 'heading_order', 'form_labels', 'page_language', 'iframe_title', 'color_contrast' );

	/** A check's label in the findings. */
	private static function label( string $check ): string {
		$labels = array(
			'image_alt'      => __( 'Images without alt text', 'wpzoom-ai-connect' ),
			'link_text'      => __( 'Links and buttons without useful text', 'wpzoom-ai-connect' ),
			'heading_order'  => __( 'Heading order problems', 'wpzoom-ai-connect' ),
			'form_labels'    => __( 'Form fields without a label', 'wpzoom-ai-connect' ),
			'page_language'  => __( 'Page language missing', 'wpzoom-ai-connect' ),
			'iframe_title'   => __( 'Embeds without a title', 'wpzoom-ai-connect' ),
			'color_contrast' => __( 'Low contrast in the theme\'s colors', 'wpzoom-ai-connect' ),
		);
		return $labels[ $check ] ?? $check;
	}

	/** A check's count as a phrase for the summary, singular or plural. */
	private static function phrase( string $check, int $count ): string {
		switch ( $check ) {
			case 'image_alt':
				return sprintf( /* translators: %d: number of images */ _n( '%d image without alt text', '%d images without alt text', $count, 'wpzoom-ai-connect' ), $count );
			case 'link_text':
				return sprintf( /* translators: %d: number of links and buttons */ _n( '%d link or button without useful text', '%d links or buttons without useful text', $count, 'wpzoom-ai-connect' ), $count );
			case 'heading_order':
				return sprintf( /* translators: %d: number of heading problems */ _n( '%d heading problem', '%d heading problems', $count, 'wpzoom-ai-connect' ), $count );
			case 'form_labels':
				return sprintf( /* translators: %d: number of form fields */ _n( '%d form field without a label', '%d form fields without a label', $count, 'wpzoom-ai-connect' ), $count );
			case 'page_language':
				return __( 'no valid page language', 'wpzoom-ai-connect' );
			case 'iframe_title':
				return sprintf( /* translators: %d: number of embeds */ _n( '%d embed without a title', '%d embeds without a title', $count, 'wpzoom-ai-connect' ), $count );
			case 'color_contrast':
				return sprintf( /* translators: %d: number of color pairs */ _n( '%d text and background color pair with low contrast', '%d text and background color pairs with low contrast', $count, 'wpzoom-ai-connect' ), $count );
		}
		return '';
	}

	/** The note every result carries. */
	private static function note(): string {
		return __( 'An automated check finds common problems; it does not cover everything a person with a screen reader or keyboard would meet.', 'wpzoom-ai-connect' );
	}

	/** Items listed per check; count always gives the true number. */
	const MAX_ITEMS = 20;

	/** Link texts that do not say where a link goes. */
	const GENERIC_LINK_TEXT = array( 'click here', 'read more', 'here', 'link' );

	/** Input types that need no label. */
	const UNLABELLED_INPUTS = array( 'hidden', 'submit', 'button', 'image', 'reset' );

	/** Contrast: normal text needs 4.5:1; headings and titles (24px or larger in our themes) 3:1. */
	const RATIO_TEXT  = 4.5;
	const RATIO_LARGE = 3.0;

	/** What the checks know besides the HTML: post, partial. Empty when analyze() is given HTML alone. */
	private static $context = array();

	/** Colors the page itself declares (palette slugs, the body's text and background), read from its CSS. */
	private static $page_colors = array( 'palette' => array(), 'text' => '', 'background' => '' );

	/** Text elements whose colors may be set on their block or a parent block. */
	const TEXT_ELEMENTS = './/p | .//h1 | .//h2 | .//h3 | .//h4 | .//h5 | .//h6 | .//li | .//blockquote | .//figcaption | .//td | .//th | .//dt | .//dd | .//pre | .//cite | .//a[contains(concat(" ", normalize-space(@class), " "), " wp-block-button__link ")]';

	/* ---------- the ability ---------- */

	/**
	 * wpzoom/check-accessibility: fetches a published page as visitors get it and runs the checks.
	 *
	 * @param int|string $post_id The page to check.
	 * @param string[]   $checks  Check names; all when empty.
	 * @return array|WP_Error
	 */
	public static function check( $post_id, array $checks = array() ) {
		if ( ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) ) {
			return new WP_Error( 'wpzoom_no_dom', __( 'This server\'s PHP lacks the DOM extension (php-xml), which the check needs to read the page. The host can enable it.', 'wpzoom-ai-connect' ), array( 'status' => 500 ) );
		}
		$unknown = array_diff( $checks, self::CHECKS );
		if ( $unknown ) {
			return new WP_Error( 'wpzoom_unknown_check', sprintf( /* translators: 1: the unknown check names, 2: the check names */ __( 'Unknown check: %1$s. The checks are: %2$s.', 'wpzoom-ai-connect' ), implode( ', ', $unknown ), implode( ', ', self::CHECKS ) ), array( 'status' => 400 ) );
		}
		$post = get_post( (int) $post_id );
		if ( ! $post || in_array( $post->post_status, array( 'trash', 'auto-draft', 'inherit' ), true ) ) {
			return new WP_Error( 'wpzoom_not_found', sprintf( /* translators: %d: post ID */ __( 'No post or page with id %d.', 'wpzoom-ai-connect' ), (int) $post_id ), array( 'status' => 404 ) );
		}
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return new WP_Error( 'wpzoom_forbidden', __( 'You cannot edit this page, so it is not checked here.', 'wpzoom-ai-connect' ), array( 'status' => 403 ) );
		}
		if ( 'publish' !== $post->post_status || '' !== (string) $post->post_password || ! is_post_publicly_viewable( $post ) ) {
			return new WP_Error( 'wpzoom_not_public', __( 'Publish the page first; the check reads it as visitors do.', 'wpzoom-ai-connect' ) . ( '' !== (string) $post->post_password ? ' ' . __( 'Password-protected pages are not checked.', 'wpzoom-ai-connect' ) : '' ), array( 'status' => 400 ) );
		}

		$url     = get_permalink( $post );
		$fetched = self::fetch( $post );
		$partial = is_wp_error( $fetched );
		$html    = $partial ? self::content_page( $post ) : $fetched;
		$result  = self::analyze( $html, $checks, array( 'post' => $post, 'partial' => $partial, 'url' => $url ) );

		$head = array(
			'post_id' => $post->ID,
			'url'     => $url,
			'title'   => html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, 'UTF-8' ),
		);
		if ( $partial ) {
			$head['partial']        = true;
			$head['partial_reason'] = sprintf( /* translators: %s: why the page could not be fetched */ __( 'The server could not fetch its own page (%s), so only the page content was checked, without the theme\'s header, menus and footer.', 'wpzoom-ai-connect' ), $fetched->get_error_message() );
		}
		return $head + $result;
	}

	/** The page as a visitor gets it, or an error when the server cannot fetch it (loopback blocked, not 200, empty). */
	private static function fetch( WP_Post $post ) {
		// The query argument makes page caches (WP Rocket, LiteSpeed, SiteGround) serve a fresh copy.
		$response = wp_safe_remote_get(
			add_query_arg( 'wpzoom-ai-check', '1', get_permalink( $post ) ),
			array(
				'timeout'     => 25, // slow uncached pages still arrive; the ability is read-only and the AI waits anyway
				'redirection' => 3,
				'user-agent'  => 'WPZOOM AI Connect/' . WPZOOM_AI_CONNECT_VERSION,
			)
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = (string) wp_remote_retrieve_body( $response );
		if ( 200 !== $code ) {
			return new WP_Error( 'wpzoom_fetch_status', sprintf( /* translators: %d: HTTP status code. Shown in brackets after "The server could not fetch its own page". */ __( 'it answered HTTP %d', 'wpzoom-ai-connect' ), $code ) );
		}
		if ( strlen( $body ) < 500 ) {
			return new WP_Error( 'wpzoom_fetch_empty', /* translators: Shown in brackets after "The server could not fetch its own page". */ __( 'the answer was nearly empty', 'wpzoom-ai-connect' ) );
		}
		return $body;
	}

	/** The post content as WordPress renders it, for when the page itself cannot be fetched. */
	private static function content_page( WP_Post $post ): string {
		$backup          = $GLOBALS['post'] ?? null;
		$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- the_content filters read the global post; restored below.
		setup_postdata( $post );
		$content = (string) apply_filters( 'the_content', $post->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core's filter.
		wp_reset_postdata();
		$GLOBALS['post'] = $backup; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- after the reset, which sets the global itself.
		return '<!DOCTYPE html><html><head><title></title></head><body><main><div class="entry-content">' . $content . '</div></main></body></html>';
	}

	/* ---------- analysis (HTML in, findings out) ---------- */

	/**
	 * Runs the checks on a page's HTML.
	 *
	 * @param string   $html    The page.
	 * @param string[] $checks  Check names; all when empty.
	 * @param array    $context Optional: post (WP_Post, for in_content and the right tool), partial (bool), url.
	 * @return array findings, passed, skipped, summary, note.
	 */
	public static function analyze( string $html, array $checks = array(), array $context = array() ): array {
		$checks        = $checks ? array_values( array_intersect( self::CHECKS, $checks ) ) : self::CHECKS;
		self::$context = $context;
		$xpath         = self::parse( $html );
		$findings      = array();
		$passed        = array();
		$skipped       = array();

		foreach ( $checks as $name ) {
			if ( 'page_language' === $name && ! empty( $context['partial'] ) ) {
				$skipped[] = array( 'check' => $name, 'reason' => __( 'The server could not fetch its own page, so the page\'s html tag was not seen.', 'wpzoom-ai-connect' ) );
				continue;
			}
			$method = 'check_' . $name;
			$out    = self::$method( $xpath );
			if ( isset( $out['skip'] ) ) {
				$skipped[] = array( 'check' => $name, 'reason' => $out['skip'] );
				continue;
			}
			if ( ! empty( $out['skipped'] ) ) {
				$skipped[] = array( 'check' => $name ) + $out['skipped'];
			}
			$items = $out['items'];
			if ( ! $items ) {
				$passed[] = $name;
				continue;
			}
			$findings[] = array(
				'check' => $name,
				'label' => self::label( $name ),
				'count' => count( $items ),
				'items' => array_slice( self::collapse( $items ), 0, self::MAX_ITEMS ),
			);
		}
		self::$context = array();
		usort( $findings, static function ( $a, $b ) { return $b['count'] <=> $a['count']; } );

		return array(
			'findings' => $findings,
			'passed'   => $passed,
			'skipped'  => $skipped,
			'summary'  => self::summary( $findings, $checks, $skipped, (string) ( $context['url'] ?? '' ) ),
			'note'     => self::note(),
		);
	}

	/** The same element printed many times (a widget's repeated link) is one item with "repeats", so it does not crowd out the rest. */
	private static function collapse( array $items ): array {
		$unique = array();
		foreach ( $items as $item ) {
			$key = ( $item['where'] ?? '' ) . '|' . ( $item['snippet'] ?? '' ) . '|' . ( $item['fix'] ?? '' );
			if ( isset( $unique[ $key ] ) ) {
				$unique[ $key ]['repeats'] = ( $unique[ $key ]['repeats'] ?? 1 ) + 1;
				continue;
			}
			$unique[ $key ] = $item;
		}
		return array_values( $unique );
	}

	private static function summary( array $findings, array $checks, array $skipped, string $url ): string {
		if ( ! $findings ) {
			$ran = count( $checks ) - count( array_unique( array_column( array_filter( $skipped, static function ( $s ) { return ! isset( $s['part'] ); } ), 'check' ) ) );
			if ( 0 === $ran ) {
				return '' !== $url
					? sprintf( /* translators: %s: page URL */ __( 'No check could run on %s; skipped says why.', 'wpzoom-ai-connect' ), $url )
					: __( 'No check could run; skipped says why.', 'wpzoom-ai-connect' );
			}
			if ( count( self::CHECKS ) === $ran ) {
				return '' !== $url
					? sprintf( /* translators: %s: page URL */ __( 'No problems found by these seven checks on %s.', 'wpzoom-ai-connect' ), $url )
					: __( 'No problems found by these seven checks.', 'wpzoom-ai-connect' );
			}
			if ( 1 === $ran ) {
				return '' !== $url
					? sprintf( /* translators: %s: page URL */ __( 'No problems found by this check on %s.', 'wpzoom-ai-connect' ), $url )
					: __( 'No problems found by this check.', 'wpzoom-ai-connect' );
			}
			return '' !== $url
				? sprintf( /* translators: 1: number of checks, 2: page URL */ _n( 'No problems found by %1$d check on %2$s.', 'No problems found by these %1$d checks on %2$s.', $ran, 'wpzoom-ai-connect' ), $ran, $url )
				: sprintf( /* translators: %d: number of checks */ _n( 'No problems found by %d check.', 'No problems found by these %d checks.', $ran, 'wpzoom-ai-connect' ), $ran );
		}
		$parts = array();
		foreach ( $findings as $finding ) {
			$parts[] = self::phrase( $finding['check'], $finding['count'] );
		}
		$found = ucfirst( implode( ', ', $parts ) );
		return '' !== $url
			? sprintf( /* translators: 1: the problems found, as a comma-separated list, 2: page URL */ __( '%1$s on %2$s.', 'wpzoom-ai-connect' ), $found, $url )
			: $found . '.';
	}

	/** One DOMXPath over the page, with scripts, styles, noscript and templates removed. */
	private static function parse( string $html ): DOMXPath {
		$previous = libxml_use_internal_errors( true );
		$document = new DOMDocument();
		// The XML prefix keeps UTF-8 text intact in libxml's HTML parser.
		$document->loadHTML( '<?xml encoding="utf-8" ?>' . ( '' === trim( $html ) ? '<html><body></body></html>' : $html ), LIBXML_NOWARNING | LIBXML_NOERROR );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		$xpath = new DOMXPath( $document );
		$css   = '';
		foreach ( $xpath->query( '//style' ) as $style ) {
			$css .= $style->textContent . "\n";
		}
		self::$page_colors = self::colors_from_css( $css );
		foreach ( iterator_to_array( $xpath->query( '//script|//style|//noscript|//template' ) ) as $node ) {
			$node->parentNode->removeChild( $node );
		}
		return $xpath;
	}

	/* ---------- the seven checks ---------- */

	/** image_alt: img with no alt attribute. alt="" (decorative), role presentation and tracking pixels pass. */
	private static function check_image_alt( DOMXPath $xpath ): array {
		$items = array();
		foreach ( $xpath->query( './/img', self::scope( $xpath ) ) as $img ) {
			if ( $img->hasAttribute( 'alt' ) || self::hidden( $img ) || '' !== self::aria_name( $xpath, $img ) ) {
				continue;
			}
			if ( in_array( strtolower( $img->getAttribute( 'role' ) ), array( 'presentation', 'none' ), true ) ) {
				continue;
			}
			$width  = $img->getAttribute( 'width' );
			$height = $img->getAttribute( 'height' );
			if ( '' !== $width && '' !== $height && (int) $width <= 1 && (int) $height <= 1 ) {
				continue; // a tracking pixel
			}
			$src        = $img->getAttribute( 'src' ) ?: $img->getAttribute( 'data-src' );
			$attachment = self::attachment_id( $img, $src );
			$place      = self::placement( $img, array( $attachment ? 'wp-image-' . $attachment : '', self::file_stem( $src ) ) );
			if ( ! $place['in_content'] && $attachment && in_array( $place['edit_with'], array( 'template', 'report', 'menu' ), true ) ) {
				$place = array( 'in_content' => false, 'edit_with' => 'media' );
			}
			$item = self::item( $img, $place );
			if ( $attachment ) {
				$item = array_slice( $item, 0, 2, true ) + array( 'attachment_id' => $attachment ) + array_slice( $item, 2, null, true );
			}
			$item['fix'] = __( 'Add alt text that says what the picture shows (alt="" if it is only decoration).', 'wpzoom-ai-connect' ) . ' '
				. self::how( $place['edit_with'], __( 'It is in the page content: update_content with a replacement on this img tag.', 'wpzoom-ai-connect' ) )
				. ( $attachment && 'media' !== $place['edit_with'] ? ' ' . __( 'The image\'s Alternative Text in the Media Library covers it wherever else it is used.', 'wpzoom-ai-connect' ) : '' );
			$items[] = $item;
		}
		return array( 'items' => $items );
	}

	/** link_text: links and buttons with no accessible name, or only "click here", "read more", "here", "link". Whole page. */
	private static function check_link_text( DOMXPath $xpath ): array {
		$items = array();
		foreach ( $xpath->query( '//body//a[@href] | //body//button' ) as $el ) {
			if ( self::hidden( $el ) ) {
				continue;
			}
			$name  = self::accessible_name( $xpath, $el );
			$plain = strtolower( trim( preg_replace( '/[^\p{L}\p{N} ]+/u', '', $name ) ) );
			if ( '' === $name ) {
				$problem = __( 'It has no text, aria-label or title, so a screen reader announces only "link" or "button".', 'wpzoom-ai-connect' );
			} elseif ( in_array( $plain, self::GENERIC_LINK_TEXT, true ) ) {
				$problem = sprintf( /* translators: %s: the link's text */ __( 'Its text is only "%s", which does not say where it goes.', 'wpzoom-ai-connect' ), $name );
			} else {
				continue;
			}
			$place = self::placement( $el, array( $el->getAttribute( 'href' ), $name ) );
			$item  = self::item( $el, $place );
			if ( '' !== $name ) {
				$item = array_slice( $item, 0, 2, true ) + array( 'text' => $name ) + array_slice( $item, 2, null, true );
			}
			$item['fix'] = $problem . ' ' . __( 'Give it words that say where it goes, or an aria-label.', 'wpzoom-ai-connect' ) . ' ' . self::how( $place['edit_with'], __( 'It is in the page content: update_content with a replacement on this tag.', 'wpzoom-ai-connect' ) );
			$items[]     = $item;
		}
		return array( 'items' => $items );
	}

	/** heading_order: no h1 or more than one on the page, and a skipped level (h2 then h4) in the main content. */
	private static function check_heading_order( DOMXPath $xpath ): array {
		$items = array();
		$scope = self::scope( $xpath );
		$h1s   = array();
		foreach ( $xpath->query( '//body//h1' ) as $h1 ) {
			if ( ! self::hidden( $h1 ) ) {
				$h1s[] = $h1;
			}
		}
		if ( ! $h1s ) {
			$items[] = array(
				'where'      => 'body',
				'snippet'    => '',
				'in_content' => false,
				'edit_with'  => 'report',
				'fix'        => __( 'The page has no level-1 heading. Usually the page title is one; if the theme hides the title on this page, make the first heading in the content an h1 (update_content, block markup <!-- wp:heading {"level":1} -->).', 'wpzoom-ai-connect' ),
			);
		}
		foreach ( array_slice( $h1s, 1 ) as $h1 ) {
			$place       = self::placement( $h1, array( self::text_of( $h1 ) ) );
			$item        = self::item( $h1, $place );
			$item['fix'] = sprintf( /* translators: %s: the heading's text */ __( 'A second h1 ("%s"). Keep one h1 per page and make this one an h2, keeping the text.', 'wpzoom-ai-connect' ), self::short( self::text_of( $h1 ), 60 ) ) . ' ' . self::how( $place['edit_with'], __( 'It is in the page content: update_content with a replacement with block markup <!-- wp:heading --> and an <h2> tag.', 'wpzoom-ai-connect' ) );
			$items[]     = $item;
		}
		// Levels in reading order: the main content's headings, after any h1 that comes before it (the page title).
		$previous = 0;
		foreach ( $xpath->query( '//body//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]' ) as $heading ) {
			$inside = self::inside( $heading, $scope );
			if ( self::hidden( $heading ) || ( ! $inside && 'h1' !== strtolower( $heading->tagName ) ) ) {
				continue;
			}
			$level = (int) substr( strtolower( $heading->tagName ), 1 );
			if ( $inside && $previous && $level > $previous + 1 ) {
				$want        = $previous + 1;
				$place       = self::placement( $heading, array( self::text_of( $heading ) ) );
				$item        = self::item( $heading, $place );
				$markup      = 2 === $want ? '<!-- wp:heading -->' : sprintf( '<!-- wp:heading {"level":%d} -->', $want );
				$item['fix'] = sprintf( /* translators: 1: this heading's level, 2: the previous heading's level, 3: the level to use */ __( 'An h%1$d follows an h%2$d, skipping a level. Change it to an h%3$d and keep the text.', 'wpzoom-ai-connect' ), $level, $previous, $want ) . ' ' . self::how( $place['edit_with'], sprintf( /* translators: 1: heading block markup, 2: heading level */ __( 'It is in the page content: update_content with a replacement with block markup %1$s and an <h%2$d> tag.', 'wpzoom-ai-connect' ), $markup, $want ) );
				$items[]     = $item;
			}
			$previous = $level;
		}
		return array( 'items' => $items );
	}

	/** form_labels: inputs, selects and textareas without a label, in the main content. */
	private static function check_form_labels( DOMXPath $xpath ): array {
		$items = array();
		foreach ( $xpath->query( './/input | .//select | .//textarea', self::scope( $xpath ) ) as $field ) {
			$tag = strtolower( $field->tagName );
			if ( 'input' === $tag && in_array( strtolower( $field->getAttribute( 'type' ) ?: 'text' ), self::UNLABELLED_INPUTS, true ) ) {
				continue;
			}
			if ( self::hidden( $field ) || self::labelled( $xpath, $field ) ) {
				continue;
			}
			$name   = $field->getAttribute( 'name' );
			$plugin = self::form_plugin( $field );
			$place  = $plugin ? array( 'in_content' => false, 'edit_with' => 'report' ) : self::placement( $field, array( $name ? 'name="' . $name . '"' : '', $field->getAttribute( 'id' ) ? 'id="' . $field->getAttribute( 'id' ) . '"' : '' ) );
			$item   = self::item( $field, $place );
			if ( '' !== $name ) {
				$item = array_slice( $item, 0, 2, true ) + array( 'field' => $name ) + array_slice( $item, 2, null, true );
			}
			$what = __( 'Add a label that says what to enter (a <label for> tied to the field, or an aria-label).', 'wpzoom-ai-connect' );
			if ( $plugin ) {
				$item['form']   = $plugin[0];
				$item['fix']    = $what . ' ' . sprintf( /* translators: 1: form plugin name, 2: where its forms are edited in WordPress admin */ __( 'This form comes from %1$s: change the field\'s label in %2$s; these tools cannot edit it.', 'wpzoom-ai-connect' ), $plugin[0], $plugin[1] );
			} else {
				$item['fix'] = $what . ' ' . self::how( $place['edit_with'], __( 'It is in the page content: update_content with a replacement on this field.', 'wpzoom-ai-connect' ) );
			}
			$items[] = $item;
		}
		return array( 'items' => $items );
	}

	/** page_language: the html tag has no lang, or one that is not a language tag (en, en-US, pt-BR). */
	private static function check_page_language( DOMXPath $xpath ): array {
		$html = $xpath->query( '/html' )->item( 0 );
		$lang = $html instanceof DOMElement ? trim( $html->getAttribute( 'lang' ) ) : '';
		if ( preg_match( '/^[a-z]{2,3}(-[a-z0-9]{2,8})*$/i', $lang ) ) {
			return array( 'items' => array() );
		}
		return array(
			'items' => array(
				array(
					'where'      => 'html',
					'snippet'    => $html instanceof DOMElement ? self::snippet( $html ) : '<html>',
					'in_content' => false,
					'edit_with'  => 'report',
					'fix'        => ( '' === $lang ? __( 'The page does not say its language, so screen readers may read it with the wrong voice.', 'wpzoom-ai-connect' ) : sprintf( /* translators: %s: the page's lang attribute */ __( 'The page language "%s" is not a language code, so screen readers may read it with the wrong voice.', 'wpzoom-ai-connect' ), $lang ) ) . ' ' . __( 'The user sets it in Settings → General → Site Language (the connection cannot change Settings); if it is set and still missing, the theme\'s header lacks language_attributes().', 'wpzoom-ai-connect' ),
				),
			),
		);
	}

	/** iframe_title: iframes (YouTube, Vimeo, maps) without a title, in the main content. */
	private static function check_iframe_title( DOMXPath $xpath ): array {
		$items = array();
		foreach ( $xpath->query( './/iframe', self::scope( $xpath ) ) as $frame ) {
			if ( self::hidden( $frame ) || '' !== trim( $frame->getAttribute( 'title' ) ) || '' !== self::aria_name( $xpath, $frame ) ) {
				continue;
			}
			if ( '0' === $frame->getAttribute( 'width' ) || '0' === $frame->getAttribute( 'height' ) ) {
				continue;
			}
			$src   = $frame->getAttribute( 'src' ) ?: $frame->getAttribute( 'data-src' );
			$video = preg_match( '~(?:embed/|video/|youtu\.be/|[?&]v=)([\w-]{6,})~', $src, $m ) ? $m[1] : '';
			$place = self::placement( $frame, array( $video, $src ) );
			$item  = self::item( $frame, $place );
			$item['fix'] = __( 'Add a title naming what it shows, e.g. title="Studio tour video".', 'wpzoom-ai-connect' ) . ' '
				. self::how( $place['edit_with'], __( 'It is in the page content: update_content with a replacement on this iframe.', 'wpzoom-ai-connect' ) )
				. ( 'content' === $place['edit_with'] ? ' ' . __( 'A YouTube or Vimeo embed block gets its title from the video, so embedding it again usually adds one.', 'wpzoom-ai-connect' ) : '' );
			$items[] = $item;
		}
		return array( 'items' => $items );
	}

	/** color_contrast: the theme's own text and background color settings, compared in pairs. */
	private static function check_color_contrast( DOMXPath $xpath ): array {
		// The page's own text and background come from the CSS the page prints (theme.json styles, GeneratePress's and
		// Astra's inline CSS). A stored setting is not used for them: a theme can keep a value it does not render
		// (Inspiro PRO stores color-background #111 while the page is light), and comparing against it invents findings.
		// When the page's CSS does not say, text on the page background is listed as not compared.
		if ( function_exists( 'generate_get_global_colors' ) ) {
			// GeneratePress may write its dynamic CSS to a file instead of the page, leaving var(--base-2) unresolved:
			// its global colors by slug fill the gap.
			foreach ( (array) generate_get_global_colors() as $color ) {
				if ( is_array( $color ) && isset( $color['slug'], $color['color'] ) && is_string( $color['color'] ) ) {
					self::$page_colors['palette'][ strtolower( (string) $color['slug'] ) ] = $color['color'];
				}
			}
		}
		$root  = array(
			'text'       => self::resolve_preset( self::$page_colors['text'], self::$page_colors['palette'] ),
			'background' => self::resolve_preset( self::$page_colors['background'], self::$page_colors['palette'] ),
		);
		$pairs = null;
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() && function_exists( 'wp_get_global_styles' ) ) {
			$pairs = self::block_theme_pairs( (array) wp_get_global_styles(), self::palette() );
			$tool  = 'global_styles';
		} elseif ( class_exists( 'WPZOOM_AI_Theme_Settings' ) ) {
			$colors = WPZOOM_AI_Theme_Settings::colors(); // the Customizer's color settings on any theme that has them
			if ( $colors ) {
				$pairs = self::framework_pairs( $colors, $root['background'] );
				$tool  = 'theme_settings';
			}
		}

		list( $content, $unknown ) = self::content_pairs( $xpath, $root );

		// Elementor keeps widget colors in its own settings and CSS files, not in the page markup. For a page built
		// with Elementor they are read from Elementor's data; otherwise (HTML alone, templates) the result says so
		// rather than let the page look clean.
		$post      = self::$context['post'] ?? null;
		$elementor = $post instanceof WP_Post && class_exists( 'WPZOOM_AI_Elementor' ) ? WPZOOM_AI_Elementor::color_pairs( $post->ID, $root ) : null;
		$builder   = $xpath->query( '//*[@data-elementor-type="wp-page" or @data-elementor-type="wp-post" or contains(concat(" ", normalize-space(@class), " "), " elementor-widget ")]' )->length > 0;
		$templates = $xpath->query( '//*[@data-elementor-type="header" or @data-elementor-type="footer" or @data-elementor-type="single-page" or @data-elementor-type="single-post" or @data-elementor-type="section"]' )->length > 0;
		$not_seen  = '';
		if ( null === $elementor && $builder ) {
			$not_seen = ' ' . __( 'Colors set in Elementor widgets are kept in Elementor\'s settings, not in the page, so they were not compared; check them in the Elementor editor (each widget\'s Style tab).', 'wpzoom-ai-connect' );
		} elseif ( $templates ) {
			$not_seen = ' ' . __( 'Elementor headers, footers and other templates are separate documents, so their widget colors were not compared.', 'wpzoom-ai-connect' );
		}
		$elementor_items = array();
		$has_elementor   = null !== $elementor && $elementor['pairs'];
		if ( null !== $elementor ) {
			$elementor_items = self::elementor_items( $elementor['pairs'], $unknown );
		}
		if ( null === $pairs && ! $content && ! $unknown && ! $has_elementor ) {
			return array( 'skip' => __( 'The theme does not expose its colors as settings, and no block in the page content sets its own colors.', 'wpzoom-ai-connect' ) . $not_seen );
		}
		$items = array_merge( $elementor_items, self::content_items( $content, $unknown ) );
		$out   = array( 'items' => $items );
		if ( null !== $pairs ) {
			$theme        = self::evaluate_pairs( $pairs, $tool );
			$out['items'] = array_merge( $items, $theme['items'] );
			$unknown      = array_merge( $unknown, $theme['skipped']['pairs'] ?? array() );
		}
		$reasons = $not_seen ? array( trim( $not_seen ) ) : array();
		if ( null === $pairs ) {
			$reasons[] = __( 'The theme does not expose its colors as settings, so only colors set on blocks in the page content were compared.', 'wpzoom-ai-connect' );
		}
		if ( '' === $root['background'] ) {
			$reasons[] = __( 'The page does not print its background color in its CSS, so text that sits on the page background was not compared.', 'wpzoom-ai-connect' );
		}
		if ( $unknown ) {
			$reasons[] = __( 'These use colors that are not plain (semi-transparent, a gradient, a CSS variable) or text over an image or gradient, so they were not compared.', 'wpzoom-ai-connect' );
		}
		if ( $reasons ) {
			$out['skipped'] = array( 'part' => true, 'reason' => implode( ' ', $reasons ) ) + ( $unknown ? array( 'pairs' => array_slice( $unknown, 0, 10 ) ) : array() );
		}
		return $out;
	}

	/**
	 * Text in the page content whose color or background is set on its block or a parent block (has-…-color classes or
	 * inline styles), with the colors it ends up with. Text that only uses the page's own colors is left to the theme
	 * pairs. Text over an image or gradient (a Cover block, a background image) is listed as not compared.
	 *
	 * @param array $root The page's text and background colors.
	 * @return array [ pairs, not compared ]
	 */
	private static function content_pairs( DOMXPath $xpath, array $root ): array {
		$scope   = self::scope( $xpath );
		$palette = self::$page_colors['palette'];
		$pairs   = array();
		$unknown = array();
		$seen    = array();
		foreach ( $xpath->query( self::TEXT_ELEMENTS, $scope ) as $el ) {
			if ( self::hidden( $el ) || ! self::has_own_text( $el ) ) {
				continue;
			}
			$text = self::color_from( $el, $scope, 'text' );
			$back = self::color_from( $el, $scope, 'background' );
			if ( ! $text['node'] && ! $back['node'] ) {
				continue; // the page's own colors: the theme pairs cover them
			}
			$key = ( $text['node'] ? spl_object_id( $text['node'] ) : 0 ) . '-' . ( $back['node'] ? spl_object_id( $back['node'] ) : 0 );
			if ( isset( $seen[ $key ] ) ) {
				continue; // the same colored block again (list items, table cells)
			}
			$seen[ $key ] = true;
			if ( 'image' === $back['value'] ) {
				$unknown[] = sprintf( /* translators: %s: where the element is, as a CSS path */ __( 'text over an image or gradient at %s', 'wpzoom-ai-connect' ), self::where( $el ) );
				continue;
			}
			$text_value = $text['node'] ? self::resolve_preset( $text['value'], $palette ) : $root['text'];
			$back_value = $back['node'] ? self::resolve_preset( $back['value'], $palette ) : $root['background'];
			if ( '' === $text_value ) {
				$unknown[] = sprintf( /* translators: %s: where the element is, as a CSS path */ __( 'the theme\'s text color (not known here) at %s', 'wpzoom-ai-connect' ), self::where( $el ) );
				continue;
			}
			if ( '' === $back_value ) {
				$unknown[] = sprintf( /* translators: %s: where the element is, as a CSS path */ __( 'text on the page background (not known here) at %s', 'wpzoom-ai-connect' ), self::where( $el ) );
				continue;
			}
			$tag     = strtolower( $el->tagName );
			$large   = in_array( $tag, array( 'h1', 'h2', 'h3' ), true ) || preg_match( '/(^|\s)has-(x-large|xx-large|huge|larger)-font-size(\s|$)/', $el->getAttribute( 'class' ) );
			$pairs[] = array(
				'element'    => $el,
				'source'     => $text['node'] ?: $back['node'],
				'text'       => $text_value,
				'background' => $back_value,
				'needles'    => array_filter( array( $text['label'], $back['label'] ) ),
				'threshold'  => $large ? self::RATIO_LARGE : self::RATIO_TEXT,
			);
		}
		return array( $pairs, $unknown );
	}

	/**
	 * Elementor pairs under their threshold, as items. These are fixed in the Elementor editor: the Elementor tool
	 * changes content, not colors, so edit_with is report.
	 */
	private static function elementor_items( array $pairs, array &$unknown ): array {
		$items = array();
		foreach ( $pairs as $pair ) {
			$where = sprintf( '.elementor-element-%s', $pair['element_id'] );
			if ( 'image' === $pair['background']['value'] ) {
				$unknown[] = sprintf( /* translators: 1: the background, e.g. "a background image, gradient or video", 2: widget name, 3: Elementor element ID */ __( 'text on %1$s at the %2$s widget (Elementor element %3$s)', 'wpzoom-ai-connect' ), $pair['background']['label'], $pair['widget'], $pair['element_id'] );
				continue;
			}
			$text = self::parse_color( self::resolve_preset( $pair['text']['value'], self::$page_colors['palette'] ) );
			$back = self::parse_color( self::resolve_preset( $pair['background']['value'], self::$page_colors['palette'] ) );
			if ( ! $text || ! $back ) {
				$unknown[] = sprintf( /* translators: 1: text color, 2: background color, 3: widget name, 4: Elementor element ID */ __( '%1$s on %2$s at the %3$s widget (Elementor element %4$s)', 'wpzoom-ai-connect' ), $pair['text']['value'], $pair['background']['value'], $pair['widget'], $pair['element_id'] );
				continue;
			}
			$threshold = $pair['large'] ? self::RATIO_LARGE : self::RATIO_TEXT;
			$ratio     = self::ratio_of( $text, $back );
			if ( $ratio >= $threshold ) {
				continue;
			}
			$text_global = (string) $pair['text']['global'];
			$back_global = (string) ( $pair['background']['global'] ?? '' );
			$globals     = '';
			if ( '' !== $text_global && '' !== $back_global ) {
				$globals = sprintf( /* translators: 1: Elementor global color name used by the text, 2: global color name used by the background */ __( 'The text uses the global color "%1$s", and the background uses the global color "%2$s": changing a global color in Site Settings → Global Colors changes it everywhere it is used.', 'wpzoom-ai-connect' ), $text_global, $back_global );
			} elseif ( '' !== $text_global ) {
				$globals = sprintf( /* translators: %s: Elementor global color name */ __( 'The text uses the global color "%s": changing a global color in Site Settings → Global Colors changes it everywhere it is used.', 'wpzoom-ai-connect' ), $text_global );
			} elseif ( '' !== $back_global ) {
				$globals = sprintf( /* translators: %s: Elementor global color name */ __( 'The background uses the global color "%s": changing a global color in Site Settings → Global Colors changes it everywhere it is used.', 'wpzoom-ai-connect' ), $back_global );
			}
			$large       = self::RATIO_LARGE === $threshold;
			$darker_text = self::luminance( $text ) < self::luminance( $back );
			$items[] = array(
				'where'      => $where,
				'snippet'    => sprintf( '<div data-id="%s" data-element_type="widget">', $pair['element_id'] ),
				'element_id' => $pair['element_id'],
				'widget'     => $pair['widget'],
				'settings'   => array_filter( array( 'text' => $pair['text']['setting'], 'background' => $pair['background']['setting'] ?? ( '' !== $pair['background']['from'] ? sprintf( /* translators: %s: Elementor element ID */ __( 'element %s', 'wpzoom-ai-connect' ), $pair['background']['from'] ) : '' ) ) ),
				'colors'     => sprintf( /* translators: 1: text color, 2: background color */ __( '%1$s on %2$s', 'wpzoom-ai-connect' ), $pair['text']['value'], $pair['background']['value'] ),
				'ratio'      => self::format_ratio( $ratio ),
				'needs'      => self::format_ratio( $threshold ),
				'in_content' => false,
				'edit_with'  => 'report',
				'fix'        => implode(
					' ',
					array_filter(
						array(
							$large
								/* translators: 1: text color's name, 2: text color, 3: background's name, 4: contrast ratio, 5: ratio needed */
								? sprintf( __( '%1$s (%2$s) on %3$s is %4$s; heading-size text needs %5$s.', 'wpzoom-ai-connect' ), ucfirst( $pair['text']['label'] ), $pair['text']['value'], $pair['background']['label'], self::format_ratio( $ratio ), self::format_ratio( $threshold ) )
								/* translators: 1: text color's name, 2: text color, 3: background's name, 4: contrast ratio, 5: ratio needed */
								: sprintf( __( '%1$s (%2$s) on %3$s is %4$s; text needs %5$s.', 'wpzoom-ai-connect' ), ucfirst( $pair['text']['label'] ), $pair['text']['value'], $pair['background']['label'], self::format_ratio( $ratio ), self::format_ratio( $threshold ) ),
							$darker_text
								/* translators: 1: widget name, 2: Elementor element ID */
								? sprintf( __( 'In the Elementor editor, select the %1$s widget (element %2$s) and choose a darker text color or a lighter background in its Style tab, keeping to the brand\'s range, and run it past the user.', 'wpzoom-ai-connect' ), $pair['widget'], $pair['element_id'] )
								/* translators: 1: widget name, 2: Elementor element ID */
								: sprintf( __( 'In the Elementor editor, select the %1$s widget (element %2$s) and choose a lighter text color or a darker background in its Style tab, keeping to the brand\'s range, and run it past the user.', 'wpzoom-ai-connect' ), $pair['widget'], $pair['element_id'] ),
							$globals,
							__( 'wpzoom_update_elementor_page changes texts, links and images, not colors, so the user makes this change.', 'wpzoom-ai-connect' ),
						)
					)
				),
			);
		}
		return $items;
	}

	/** The content pairs under their threshold, as items; pairs with colors that are not plain go to $unknown. */
	private static function content_items( array $pairs, array &$unknown ): array {
		$items = array();
		foreach ( $pairs as $pair ) {
			$text = self::parse_color( $pair['text'] );
			$back = self::parse_color( $pair['background'] );
			if ( ! $text || ! $back ) {
				$unknown[] = sprintf( '%s on %s at %s', $pair['text'], $pair['background'], self::where( $pair['element'] ) );
				continue;
			}
			$ratio = self::ratio_of( $text, $back );
			if ( $ratio >= $pair['threshold'] ) {
				continue;
			}
			$place           = self::placement( $pair['source'], $pair['needles'] );
			$item            = self::item( $pair['element'], $place );
			$item['snippet'] = self::snippet( $pair['source'] );
			$item['colors']  = sprintf( /* translators: 1: text color, 2: background color */ __( '%1$s on %2$s', 'wpzoom-ai-connect' ), $pair['text'], $pair['background'] );
			$item['ratio']   = self::format_ratio( $ratio );
			$item['needs']   = self::format_ratio( $pair['threshold'] );
			$item['fix']     = implode(
				' ',
				array(
					self::RATIO_LARGE === $pair['threshold']
						/* translators: 1: text color, 2: background color, 3: contrast ratio, 4: ratio needed */
						? sprintf( __( 'This text is %1$s on %2$s, %3$s; heading-size text needs %4$s.', 'wpzoom-ai-connect' ), $pair['text'], $pair['background'], $item['ratio'], $item['needs'] )
						/* translators: 1: text color, 2: background color, 3: contrast ratio, 4: ratio needed */
						: sprintf( __( 'This text is %1$s on %2$s, %3$s; text needs %4$s.', 'wpzoom-ai-connect' ), $pair['text'], $pair['background'], $item['ratio'], $item['needs'] ),
					self::luminance( $text ) < self::luminance( $back )
						? __( 'In the block\'s Color settings choose a darker text color or a lighter background, keeping to the brand\'s range, and run it past the user.', 'wpzoom-ai-connect' )
						: __( 'In the block\'s Color settings choose a lighter text color or a darker background, keeping to the brand\'s range, and run it past the user.', 'wpzoom-ai-connect' ),
					self::how( $place['edit_with'], __( 'It is in the page content: update_content with a replacement changing the color in the block comment ("textColor", "backgroundColor" or "style":{"color":…}) together with the matching has-…-color class or style attribute.', 'wpzoom-ai-connect' ) ),
				)
			);
			$items[] = $item;
		}
		return $items;
	}

	/**
	 * The color an element ends up with from its own block or the nearest parent block inside the content: an inline
	 * style, else a has-{slug}-color / has-{slug}-background-color class. "image" for a background image or gradient.
	 *
	 * @param string $kind text or background.
	 * @return array value, node (the element that sets it, null for the page's own), label (what to find in the content).
	 */
	private static function color_from( DOMElement $el, DOMNode $scope, string $kind ): array {
		for ( $node = $el; $node instanceof DOMElement; $node = $node->parentNode ) {
			$style = strtolower( $node->getAttribute( 'style' ) );
			$class = ' ' . preg_replace( '/\s+/', ' ', $node->getAttribute( 'class' ) ) . ' ';
			if ( 'background' === $kind ) {
				if ( preg_match( '/background-image\s*:|background\s*:[^;]*(url\(|gradient)/', $style ) || preg_match( '/ (has-[a-z0-9-]*gradient-background|has-background-gradient|wp-block-cover|wp-block-cover-image) /', $class ) ) {
					return array( 'value' => 'image', 'node' => $node, 'label' => '' );
				}
				if ( preg_match( '/(?:^|;)\s*background(?:-color)?\s*:\s*([^;]+)/', $style, $m ) ) {
					return array( 'value' => trim( $m[1] ), 'node' => $node, 'label' => trim( $m[1] ) );
				}
				if ( preg_match( '/ has-([a-z0-9-]+)-background-color /', $class, $m ) ) {
					return array( 'value' => 'var:preset|color|' . $m[1], 'node' => $node, 'label' => 'has-' . $m[1] . '-background-color' );
				}
			} else {
				if ( preg_match( '/(?:^|;)\s*color\s*:\s*([^;]+)/', $style, $m ) ) {
					return array( 'value' => trim( $m[1] ), 'node' => $node, 'label' => trim( $m[1] ) );
				}
				if ( preg_match_all( '/ has-([a-z0-9-]+)-color /', $class, $all ) ) {
					foreach ( $all[1] as $slug ) {
						if ( ! preg_match( '/(^|-)(background|border|text|link)$/', $slug ) ) {
							return array( 'value' => 'var:preset|color|' . $slug, 'node' => $node, 'label' => 'has-' . $slug . '-color' );
						}
					}
				}
			}
			if ( $node->isSameNode( $scope ) ) {
				break;
			}
		}
		return array( 'value' => '', 'node' => null, 'label' => '' );
	}

	/** Whether an element holds text of its own (not only through child blocks that are checked themselves). */
	private static function has_own_text( DOMElement $el ): bool {
		foreach ( $el->childNodes as $child ) {
			if ( XML_TEXT_NODE === $child->nodeType && '' !== trim( $child->nodeValue ) ) {
				return true;
			}
			if ( $child instanceof DOMElement && in_array( strtolower( $child->tagName ), array( 'a', 'strong', 'em', 'b', 'i', 'span', 'mark', 'code', 'small', 'sup', 'sub' ), true ) && '' !== self::text_of( $child ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * The palette (--wp--preset--color--slug: value) and the body's text and background colors from the page's CSS,
	 * which WordPress prints from theme.json on block themes and on classic themes that have one.
	 */
	public static function colors_from_css( string $css ): array {
		$palette = array();
		// Every custom property that holds a color, by its full name (GeneratePress's --base-2, Astra's
		// --ast-global-color-0), and the WordPress presets by their slug as well, for has-{slug}-color classes.
		if ( preg_match_all( '/--([a-z0-9-]+)\s*:\s*(#[0-9a-f]{3,8}|rgba?\([^)]*\)|white|black)\s*[;}]/i', $css, $found, PREG_SET_ORDER ) ) {
			foreach ( $found as $match ) {
				$palette[ strtolower( $match[1] ) ] = trim( $match[2] );
			}
		}
		if ( preg_match_all( '/--wp--preset--color--([a-z0-9-]+)\s*:\s*([^;}]+)/i', $css, $found, PREG_SET_ORDER ) ) {
			foreach ( $found as $match ) {
				$palette[ strtolower( $match[1] ) ] = trim( $match[2] );
			}
		}
		$body = array( 'color' => '', 'background-color' => '' );
		// Only a rule for body itself (body, html body, body.custom-background, :where(body)): not ".dark body" or a rule
		// inside @media, whose "{" precedes the selector, so a dark-mode or print rule does not become the page's colors.
		if ( preg_match_all( '/(?:^|[};\n])\s*(?::where\(\s*)?(?:html\s+)?body(?:\.[\w-]+)*\)?\s*\{([^}]*)\}/i', $css, $rules ) ) {
			foreach ( $rules[1] as $declarations ) {
				foreach ( array_keys( $body ) as $property ) {
					if ( preg_match( '/(?:^|;)\s*' . $property . '\s*:\s*([^;]+)/i', $declarations, $d ) ) {
						$body[ $property ] = trim( str_ireplace( '!important', '', $d[1] ) );
					}
				}
			}
		}
		return array(
			'palette'    => $palette,
			'text'       => self::resolve_preset( $body['color'], $palette ),
			'background' => self::resolve_preset( $body['background-color'], $palette ),
		);
	}

	/** A palette reference (var:preset|color|x, var(--wp--preset--color--x) or any var(--x) the page defines) as its color; other values as they are. */
	private static function resolve_preset( string $value, array $palette ): string {
		if ( preg_match( '/^var:preset\|color\|([a-z0-9-]+)$/i', $value, $m ) ) {
			return $palette[ strtolower( $m[1] ) ] ?? $value;
		}
		if ( preg_match( '/^var\(\s*--([a-z0-9-]+)\s*(?:,\s*([^)]+))?\)$/i', $value, $m ) ) {
			$name = strtolower( $m[1] );
			$found = $palette[ $name ] ?? $palette[ (string) preg_replace( '/^wp--preset--color--/', '', $name ) ] ?? trim( $m[2] ?? '' );
			return '' !== $found && $found !== $value ? self::resolve_preset( $found, $palette ) : $value;
		}
		return $value;
	}

	/* ---------- contrast ---------- */

	/**
	 * Compares each pair and returns the ones under their threshold as items, plus the pairs that use colors that are
	 * not plain (semi-transparent, gradients, CSS variables) in "skipped".
	 *
	 * @param array  $pairs From framework_pairs() or block_theme_pairs().
	 * @param string $tool  theme_settings or global_styles.
	 */
	public static function evaluate_pairs( array $pairs, string $tool ): array {
		$items   = array();
		$unknown = array();
		foreach ( $pairs as $pair ) {
			$text  = self::parse_color( self::resolve_preset( $pair['text']['value'], self::$page_colors['palette'] ) );
			$back  = self::parse_color( self::resolve_preset( $pair['background']['value'], self::$page_colors['palette'] ) );
			$names = sprintf( /* translators: 1: text color setting ID, 2: text color, 3: background color setting ID, 4: background color */ __( '%1$s (%2$s) on %3$s (%4$s)', 'wpzoom-ai-connect' ), $pair['text']['id'], $pair['text']['value'], $pair['background']['id'], $pair['background']['value'] );
			if ( ! $text || ! $back ) {
				$unknown[] = $names;
				continue;
			}
			$ratio = self::ratio_of( $text, $back );
			if ( $ratio >= $pair['threshold'] ) {
				continue;
			}
			$darker_text = self::luminance( $text ) < self::luminance( $back );
			$setting_ids = sprintf( /* translators: 1: text color setting ID, 2: background color setting ID */ __( '%1$s or %2$s', 'wpzoom-ai-connect' ), $pair['text']['id'], $pair['background']['id'] );
			$tool_name   = 'theme_settings' === $tool ? 'wpzoom_update_theme_settings' : 'update_global_styles';
			$items[]     = array(
				'where'      => $pair['area'],
				'settings'   => array( 'text' => $pair['text']['id'], 'background' => $pair['background']['id'] ),
				'colors'     => sprintf( /* translators: 1: text color, 2: background color */ __( '%1$s on %2$s', 'wpzoom-ai-connect' ), $pair['text']['value'], $pair['background']['value'] ),
				'ratio'      => self::format_ratio( $ratio ),
				'needs'      => self::format_ratio( $pair['threshold'] ),
				'in_content' => false,
				'edit_with'  => $tool,
				'fix'        => ( self::RATIO_LARGE === $pair['threshold']
					/* translators: 1: text color's name, 2: background color's name, 3: contrast ratio, 4: ratio needed */
					? sprintf( __( '%1$s on %2$s is %3$s; heading-size text needs %4$s.', 'wpzoom-ai-connect' ), $pair['text']['label'], $pair['background']['label'], self::format_ratio( $ratio ), self::format_ratio( $pair['threshold'] ) )
					/* translators: 1: text color's name, 2: background color's name, 3: contrast ratio, 4: ratio needed */
					: sprintf( __( '%1$s on %2$s is %3$s; text needs %4$s.', 'wpzoom-ai-connect' ), $pair['text']['label'], $pair['background']['label'], self::format_ratio( $ratio ), self::format_ratio( $pair['threshold'] ) )
				) . ' ' . ( $darker_text
					/* translators: 1: the tool that changes the setting, 2: the setting IDs */
					? sprintf( __( 'Use a darker shade of the text color, or a lighter background, keeping to the brand\'s range, and run the new color past the user: %1$s (%2$s).', 'wpzoom-ai-connect' ), $tool_name, $setting_ids )
					/* translators: 1: the tool that changes the setting, 2: the setting IDs */
					: sprintf( __( 'Use a lighter shade of the text color, or a darker background, keeping to the brand\'s range, and run the new color past the user: %1$s (%2$s).', 'wpzoom-ai-connect' ), $tool_name, $setting_ids )
				),
			);
		}
		$out = array( 'items' => $items );
		if ( $unknown ) {
			$out['skipped'] = array(
				'part'   => true,
				'reason' => __( 'These pairs use colors that are not plain (semi-transparent, a gradient or a CSS variable), so they were not compared.', 'wpzoom-ai-connect' ),
				'pairs'  => array_slice( $unknown, 0, 10 ),
			);
		}
		return $out;
	}

	/** Settings that are not text on a background: icons, borders and the like. */
	const NOT_TEXT = '/icon|hamburger|cart|arrow|spinner|border|separator|divider|shadow|overlay|accent|brand|underline|outline|rating|star/i';

	/** States the check does not see. */
	const STATE = '/hover|current|active|focus|visited/i';

	const BACKGROUND = '/background|(^|[-_\s])bg([-_\s]|$)/i';

	/** Areas that may sit on an image or a dark band: never compared with the page background. */
	const OWN_BACKDROP = '/image|slide|featured|hero|cover|header|footer|sidebar|menu|widget|video|banner|preloader|top-?bar|dropdown|portfolio/i';

	/** Words in setting ids that say nothing about which area a color belongs to. */
	const GENERIC_WORDS = array( 'color', 'colors', 'colour', 'text', 'background', 'bg', 'link', 'links', 'title', 'heading', 'headings', 'font', 'main', 'default' );

	/** Text that is heading-sized in our themes (3:1 is enough). */
	const LARGE = '/title|heading|headings|logo|(^|[-_])h[1-3]([-_]|$)/i';

	/**
	 * Text and background pairs from the theme's Customizer color settings. Within a Customizer section a text color
	 * goes with the background whose name it contains (footer-text-color with footer-background-color); with no such
	 * background, the page background as the page renders it, but only for areas that do not have a backdrop of their
	 * own (header, footer, slides, menus...). A generic "background" setting is not taken as the page background: a
	 * theme can store one it does not render. Icons, borders and hover states are left out.
	 *
	 * @param array  $colors  WPZOOM_AI_Theme_Settings::colors(): id, label, section, section_title, value.
	 * @param string $page_bg The page background read from the page's CSS; '' when unknown (those pairs are skipped).
	 */
	public static function framework_pairs( array $colors, string $page_bg = '' ): array {
		$sections = array();
		foreach ( $colors as $color ) {
			$hay = $color['id'] . ' ' . $color['label'];
			if ( preg_match( self::STATE, $hay ) || preg_match( self::NOT_TEXT, $hay ) ) {
				continue;
			}
			$color['tokens'] = self::tokens( $color['id'] );
			$kind            = preg_match( self::BACKGROUND, $hay ) ? 'backgrounds' : 'texts';
			$sections[ $color['section'] ][ $kind ][] = $color;
		}
		$page = '' !== $page_bg ? array( 'id' => 'page', 'label' => __( 'the page background', 'wpzoom-ai-connect' ), 'value' => $page_bg ) : null;

		$pairs = array();
		foreach ( $sections as $section ) {
			foreach ( $section['texts'] ?? array() as $text ) {
				if ( '' === trim( $text['value'] ) ) {
					continue; // the theme's CSS default, not known here
				}
				$best = null;
				foreach ( $section['backgrounds'] ?? array() as $bg ) {
					if ( array_diff( $bg['tokens'], $text['tokens'] ) ) {
						continue; // a more specific background (button background for a post title): not this text's
					}
					if ( ! $best || count( $bg['tokens'] ) > count( $best['tokens'] ) ) {
						$best = $bg;
					}
				}
				if ( $best && '' === trim( $best['value'] ) ) {
					$best = null; // a background left empty shows what is behind it
				}
				$own = preg_match( self::OWN_BACKDROP, $text['id'] . ' ' . $text['label'] . ' ' . $text['section_title'] );
				$bg  = $best ?: ( $own ? null : $page );
				if ( ! $bg ) {
					continue;
				}
				$pairs[] = array(
					'text'       => array( 'id' => $text['id'], 'label' => $text['label'], 'value' => $text['value'] ),
					'background' => array( 'id' => $bg['id'], 'label' => $bg['label'], 'value' => $bg['value'] ),
					'threshold'  => preg_match( self::LARGE, $text['id'] . ' ' . $text['label'] ) ? self::RATIO_LARGE : self::RATIO_TEXT,
					'area'       => $text['section_title'],
				);
			}
		}
		return $pairs;
	}

	/**
	 * Text and background pairs from a block theme's global styles: text, links, headings and buttons on the root
	 * background (buttons on their own background when they have one).
	 *
	 * @param array $styles  wp_get_global_styles().
	 * @param array $palette Palette slug => color.
	 */
	public static function block_theme_pairs( array $styles, array $palette = array() ): array {
		$get = static function ( array $path ) use ( $styles ) {
			$value = $styles;
			foreach ( $path as $key ) {
				if ( ! is_array( $value ) || ! isset( $value[ $key ] ) ) {
					return '';
				}
				$value = $value[ $key ];
			}
			return is_string( $value ) ? $value : '';
		};
		$resolve = static function ( string $value ) use ( $palette ) {
			if ( preg_match( '/^var:preset\|color\|([a-z0-9-]+)$/i', $value, $m ) || preg_match( '/^var\(\s*--wp--preset--color--([a-z0-9-]+)\s*\)$/i', $value, $m ) ) {
				return $palette[ $m[1] ] ?? $value;
			}
			return $value;
		};
		$root = $resolve( $get( array( 'color', 'background' ) ) );
		$root = array( 'id' => 'styles.color.background', 'label' => __( 'Background', 'wpzoom-ai-connect' ), 'value' => '' !== $root ? $root : '#ffffff' );

		$pairs = array();
		$add   = static function ( string $id, string $label, string $value, array $bg, float $threshold ) use ( &$pairs, $resolve ) {
			$value = $resolve( $value );
			if ( '' === $value ) {
				return;
			}
			$pairs[] = array(
				'text'       => array( 'id' => $id, 'label' => $label, 'value' => $value ),
				'background' => $bg,
				'threshold'  => $threshold,
				'area'       => __( 'Styles', 'wpzoom-ai-connect' ),
			);
		};
		$add( 'styles.color.text', __( 'Text', 'wpzoom-ai-connect' ), $get( array( 'color', 'text' ) ), $root, self::RATIO_TEXT );
		$add( 'styles.elements.link.color.text', __( 'Links', 'wpzoom-ai-connect' ), $get( array( 'elements', 'link', 'color', 'text' ) ), $root, self::RATIO_TEXT );
		$add( 'styles.elements.heading.color.text', __( 'Headings', 'wpzoom-ai-connect' ), $get( array( 'elements', 'heading', 'color', 'text' ) ), $root, self::RATIO_LARGE );
		$button_bg = $resolve( $get( array( 'elements', 'button', 'color', 'background' ) ) );
		$add( 'styles.elements.button.color.text', __( 'Button text', 'wpzoom-ai-connect' ), $get( array( 'elements', 'button', 'color', 'text' ) ), '' !== $button_bg ? array( 'id' => 'styles.elements.button.color.background', 'label' => __( 'Button background', 'wpzoom-ai-connect' ), 'value' => $button_bg ) : $root, self::RATIO_TEXT );
		return $pairs;
	}

	/** The block theme's palette, slug => color (custom over theme over default). */
	private static function palette(): array {
		$out = array();
		if ( ! function_exists( 'wp_get_global_settings' ) ) {
			return $out;
		}
		$palette = (array) wp_get_global_settings( array( 'color', 'palette' ) );
		foreach ( array( 'default', 'theme', 'custom' ) as $origin ) {
			foreach ( (array) ( $palette[ $origin ] ?? array() ) as $entry ) {
				if ( isset( $entry['slug'], $entry['color'] ) ) {
					$out[ (string) $entry['slug'] ] = (string) $entry['color'];
				}
			}
		}
		return $out;
	}

	/**
	 * A plain color as [r, g, b] (0-255): #rgb, #rrggbb, #rrggbbff, rgb() and rgba() with alpha 1, white and black.
	 * Null for anything semi-transparent, a gradient, a CSS variable or an empty value.
	 */
	public static function parse_color( string $value ): ?array {
		$value = strtolower( trim( $value ) );
		$names = array( 'white' => '#ffffff', 'black' => '#000000' );
		$value = $names[ $value ] ?? $value;
		if ( preg_match( '/^#([0-9a-f]{3})$/', $value, $m ) ) {
			$value = '#' . $m[1][0] . $m[1][0] . $m[1][1] . $m[1][1] . $m[1][2] . $m[1][2];
		}
		if ( preg_match( '/^#([0-9a-f]{6})(ff)?$/', $value, $m ) ) {
			return array_map( 'hexdec', str_split( $m[1], 2 ) );
		}
		if ( preg_match( '/^rgba?\(\s*(\d{1,3})\s*[,\s]\s*(\d{1,3})\s*[,\s]\s*(\d{1,3})\s*(?:[,\/]\s*([\d.]+%?)\s*)?\)$/', $value, $m ) ) {
			if ( isset( $m[4] ) && '' !== $m[4] ) {
				$alpha = '%' === substr( $m[4], -1 ) ? (float) $m[4] / 100 : (float) $m[4];
				if ( $alpha < 1 ) {
					return null;
				}
			}
			$rgb = array( (int) $m[1], (int) $m[2], (int) $m[3] );
			return max( $rgb ) <= 255 ? $rgb : null;
		}
		return null;
	}

	/** WCAG 2 relative luminance of [r, g, b]. */
	private static function luminance( array $rgb ): float {
		$channel = static function ( int $value ): float {
			$c = $value / 255;
			return $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		};
		return 0.2126 * $channel( $rgb[0] ) + 0.7152 * $channel( $rgb[1] ) + 0.0722 * $channel( $rgb[2] );
	}

	private static function ratio_of( array $a, array $b ): float {
		$la = self::luminance( $a );
		$lb = self::luminance( $b );
		return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
	}

	/** The contrast ratio of two plain colors, or null when either is not plain. Compare it unrounded. */
	public static function contrast_ratio( string $a, string $b ): ?float {
		$ca = self::parse_color( $a );
		$cb = self::parse_color( $b );
		return $ca && $cb ? self::ratio_of( $ca, $cb ) : null;
	}

	/** One decimal, rounded down, so a pair that fails never reads as reaching the threshold (#777 on white: 4.4:1). */
	public static function format_ratio( float $ratio ): string {
		return sprintf( '%.1f:1', floor( $ratio * 10 + 1e-9 ) / 10 );
	}

	/** The meaningful words of a setting id: color-footer-text-color → [footer]. */
	private static function tokens( string $id ): array {
		$words = preg_split( '/[^a-z0-9]+/', strtolower( $id ), -1, PREG_SPLIT_NO_EMPTY );
		return array_values( array_diff( $words, self::GENERIC_WORDS ) );
	}

	/* ---------- where an element is, and which tool edits it ---------- */

	/** The main content area: main, else #content, #primary or [role=main], else body. */
	private static function scope( DOMXPath $xpath ): DOMNode {
		foreach ( array( '//main', "//*[@id='content' or @id='primary' or @role='main']", '//body' ) as $query ) {
			$node = $xpath->query( $query )->item( 0 );
			if ( $node ) {
				return $node;
			}
		}
		return $xpath->document->documentElement;
	}

	/** The element that holds the post's own content: .entry-content or .wp-block-post-content, else the main area. */
	private static function content_root( DOMXPath $xpath ): DOMNode {
		$scope = self::scope( $xpath );
		$node  = $xpath->query( ".//*[contains(concat(' ', normalize-space(@class), ' '), ' entry-content ') or contains(concat(' ', normalize-space(@class), ' '), ' wp-block-post-content ')]", $scope )->item( 0 );
		return $node ?: $scope;
	}

	/**
	 * in_content (the post's own content, changed with update_content) and edit_with: content, elementor, menu,
	 * template (block-theme header and footer), or report (theme parts, widgets, patterns or plugin output these
	 * tools cannot edit). An element counts as content only when it sits in the content area and one of the needles
	 * (its src, href, text...) occurs in the post's content.
	 */
	private static function placement( DOMElement $el, array $needles ): array {
		$xpath = new DOMXPath( $el->ownerDocument );
		$post  = self::$context['post'] ?? null;
		if ( $post instanceof WP_Post ) {
			$elementor = $xpath->query( sprintf( '//*[@data-elementor-id="%d"]', $post->ID ) )->item( 0 );
			if ( $elementor && self::inside( $el, $elementor ) ) {
				return array( 'in_content' => false, 'edit_with' => 'elementor' );
			}
		}
		if ( self::inside( $el, self::content_root( $xpath ) ) ) {
			if ( ! $post instanceof WP_Post ) {
				return array( 'in_content' => true, 'edit_with' => 'content' );
			}
			foreach ( $needles as $needle ) {
				$needle = trim( (string) $needle );
				if ( strlen( $needle ) >= 3 && ( false !== strpos( $post->post_content, $needle ) || false !== strpos( $post->post_content, esc_attr( $needle ) ) ) ) {
					return array( 'in_content' => true, 'edit_with' => 'content' );
				}
			}
			return array( 'in_content' => false, 'edit_with' => 'report' ); // a pattern, shortcode or plugin output
		}
		for ( $node = $el; $node instanceof DOMElement; $node = $node->parentNode ) {
			$class = ' ' . $node->getAttribute( 'class' ) . ' ';
			if ( 'nav' === strtolower( $node->tagName ) || false !== strpos( $class, ' menu-item ' ) || false !== strpos( $class, ' wp-block-navigation ' ) ) {
				return array( 'in_content' => false, 'edit_with' => 'menu' );
			}
		}
		$block_theme = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
		return array( 'in_content' => false, 'edit_with' => $block_theme ? 'template' : 'report' );
	}

	/**
	 * How to make the change, by tool.
	 *
	 * @param string $edit_with  The tool, from placement().
	 * @param string $in_content The whole sentence for a change in the page content (it names what to replace).
	 */
	private static function how( string $edit_with, string $in_content ): string {
		switch ( $edit_with ) {
			case 'content':
				return $in_content;
			case 'elementor':
				return __( 'It is in the page\'s Elementor layout: wpzoom_update_elementor_page.', 'wpzoom-ai-connect' );
			case 'menu':
				return __( 'It is a menu link: update_menu_item (list_menus finds it).', 'wpzoom-ai-connect' );
			case 'template':
				return __( 'It is in the header, footer or another template part: update_template.', 'wpzoom-ai-connect' );
			case 'media':
				return __( 'It is outside the page content: set the image\'s Alternative Text in the Media Library, which covers it wherever it is used.', 'wpzoom-ai-connect' );
			default:
				return __( 'It comes from the theme, a widget, a pattern or a plugin rather than the page text, which these tools cannot edit: tell the user where it is (Appearance → Customize or Widgets, or the plugin\'s settings).', 'wpzoom-ai-connect' );
		}
	}

	/** The common fields of an item: where, snippet, in_content, edit_with. */
	private static function item( DOMElement $el, array $place ): array {
		return array(
			'where'      => self::where( $el ),
			'snippet'    => self::snippet( $el ),
			'in_content' => $place['in_content'],
			'edit_with'  => $place['edit_with'],
		);
	}

	/** A short CSS-like path a person can find in the inspector: up to four steps, from the nearest id or landmark. */
	private static function where( DOMElement $el ): string {
		$parts = array();
		for ( $node = $el; $node instanceof DOMElement && count( $parts ) < 4; $node = $node->parentNode ) {
			$tag = strtolower( $node->tagName );
			if ( in_array( $tag, array( 'html', 'body' ), true ) ) {
				break;
			}
			$id = $node->getAttribute( 'id' );
			if ( '' !== $id && $node !== $el && ! preg_match( '/\d{3,}|^(post|page)-\d+/', $id ) ) {
				array_unshift( $parts, '#' . $id );
				break;
			}
			array_unshift( $parts, $tag . ( '' !== $id && $node === $el ? '#' . $id : self::first_class( $node ) ) );
			if ( in_array( $tag, array( 'main', 'header', 'footer', 'nav', 'aside' ), true ) ) {
				break;
			}
		}
		return implode( ' > ', $parts );
	}

	private static function first_class( DOMElement $node ): string {
		foreach ( preg_split( '/\s+/', trim( $node->getAttribute( 'class' ) ), -1, PREG_SPLIT_NO_EMPTY ) as $class ) {
			// Skip classes tied to an id or a state (post-182, wp-image-5, is-layout-flow): they change from page to page.
			if ( ! preg_match( '/^(wp-image-\d+|size-|attachment-|is-|has-|wp-elements-|elementor-element-|elementor-\d)|-\d+$/', $class ) ) {
				return '.' . $class;
			}
		}
		return '';
	}

	/** The opening tag, long URLs shortened to their file name, cut at 160 characters. */
	private static function snippet( DOMElement $el ): string {
		$tag = '<' . strtolower( $el->tagName );
		foreach ( $el->attributes as $attr ) {
			// A style attribute is only kept when it sets a color, which a contrast finding needs to show.
			if ( in_array( $attr->name, array( 'srcset', 'sizes', 'decoding', 'loading', 'fetchpriority' ), true ) || ( 'style' === $attr->name && false === stripos( $attr->value, 'color' ) ) ) {
				continue;
			}
			$value = $attr->value;
			if ( in_array( $attr->name, array( 'src', 'href', 'data-src' ), true ) && strlen( $value ) > 60 ) {
				$path  = (string) ( function_exists( 'wp_parse_url' ) ? wp_parse_url( $value, PHP_URL_PATH ) : parse_url( $value, PHP_URL_PATH ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- fallback when analyze() runs without WordPress.
				$value = '…/' . basename( '' !== $path ? $path : $value );
			}
			$tag .= ' ' . $attr->name . '="' . htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ) . '"';
		}
		return self::short( $tag . '>', 160 );
	}

	private static function short( string $text, int $max ): string {
		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $text, 'UTF-8' ) : strlen( $text );
		if ( $length <= $max ) {
			return $text;
		}
		return ( function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $max - 1, 'UTF-8' ) : substr( $text, 0, $max - 1 ) ) . '…';
	}

	/* ---------- names, labels and visibility ---------- */

	private static function inside( DOMNode $node, DOMNode $ancestor ): bool {
		for ( $n = $node; $n; $n = $n->parentNode ) {
			if ( $n->isSameNode( $ancestor ) ) {
				return true;
			}
		}
		return false;
	}

	/** Hidden from everyone: the element or an ancestor is aria-hidden, has the hidden attribute or display:none. */
	private static function hidden( DOMElement $el ): bool {
		for ( $node = $el; $node instanceof DOMElement; $node = $node->parentNode ) {
			if ( 'true' === strtolower( $node->getAttribute( 'aria-hidden' ) ) || $node->hasAttribute( 'hidden' ) ) {
				return true;
			}
			if ( preg_match( '/display\s*:\s*none|visibility\s*:\s*hidden/i', $node->getAttribute( 'style' ) ) ) {
				return true;
			}
		}
		return false;
	}

	/** Text a screen reader reads from an element's content: skips aria-hidden parts, reads img alt and svg titles. */
	private static function text_of( DOMNode $node ): string {
		$text = '';
		foreach ( $node->childNodes as $child ) {
			if ( XML_TEXT_NODE === $child->nodeType ) {
				$text .= $child->nodeValue;
				continue;
			}
			if ( ! $child instanceof DOMElement || 'true' === strtolower( $child->getAttribute( 'aria-hidden' ) ) ) {
				continue;
			}
			$tag = strtolower( $child->tagName );
			if ( 'img' === $tag ) {
				$text .= ' ' . ( $child->getAttribute( 'aria-label' ) ?: $child->getAttribute( 'alt' ) ) . ' ';
			} elseif ( 'svg' === $tag ) {
				$title = $child->getElementsByTagName( 'title' )->item( 0 );
				$text .= ' ' . ( $child->getAttribute( 'aria-label' ) ?: ( $title ? $title->textContent : '' ) ) . ' ';
			} elseif ( '' !== trim( $child->getAttribute( 'aria-label' ) ) ) {
				$text .= ' ' . $child->getAttribute( 'aria-label' ) . ' ';
			} else {
				$text .= ' ' . self::text_of( $child ) . ' ';
			}
		}
		return trim( preg_replace( '/\s+/u', ' ', $text ) );
	}

	/** aria-labelledby (the referenced elements' text), else aria-label. */
	private static function aria_name( DOMXPath $xpath, DOMElement $el ): string {
		$ids = preg_split( '/\s+/', trim( $el->getAttribute( 'aria-labelledby' ) ), -1, PREG_SPLIT_NO_EMPTY );
		$parts = array();
		foreach ( $ids as $id ) {
			$target = $xpath->query( sprintf( '//*[@id="%s"]', str_replace( '"', '', $id ) ) )->item( 0 );
			if ( $target ) {
				$parts[] = self::text_of( $target );
			}
		}
		$name = trim( implode( ' ', $parts ) );
		return '' !== $name ? $name : trim( $el->getAttribute( 'aria-label' ) );
	}

	/** The accessible name of a link or button: aria-labelledby, aria-label, its content, then title. */
	private static function accessible_name( DOMXPath $xpath, DOMElement $el ): string {
		$name = self::aria_name( $xpath, $el );
		if ( '' === $name ) {
			$name = self::text_of( $el );
		}
		return '' !== $name ? $name : trim( $el->getAttribute( 'title' ) );
	}

	/** Whether a form field has a label: label[for], a wrapping label, aria-labelledby, aria-label or a title. */
	private static function labelled( DOMXPath $xpath, DOMElement $field ): bool {
		if ( '' !== self::aria_name( $xpath, $field ) || '' !== trim( $field->getAttribute( 'title' ) ) ) {
			return true;
		}
		$id = $field->getAttribute( 'id' );
		if ( '' !== $id ) {
			foreach ( $xpath->query( sprintf( '//label[@for="%s"]', str_replace( '"', '', $id ) ) ) as $label ) {
				if ( '' !== self::text_of( $label ) ) {
					return true;
				}
			}
		}
		for ( $node = $field->parentNode; $node instanceof DOMElement; $node = $node->parentNode ) {
			if ( 'label' === strtolower( $node->tagName ) ) {
				return '' !== self::text_of( $node );
			}
		}
		return false;
	}

	/** The form plugin a field belongs to, and where its forms are edited; null for a plain HTML form. */
	private static function form_plugin( DOMElement $field ): ?array {
		$known = array(
			'wpzf'         => array( 'WPZOOM Forms', 'WPZOOM Forms → All Forms' ),
			'wpcf7'        => array( 'Contact Form 7', 'Contact → Contact Forms' ),
			'wpforms'      => array( 'WPForms', 'WPForms → All Forms' ),
			'gform'        => array( 'Gravity Forms', 'Forms' ),
			'srfm'         => array( 'SureForms', 'SureForms → All Forms' ),
			'nf-form'      => array( 'Ninja Forms', 'Ninja Forms → Dashboard' ),
			'frm_forms'    => array( 'Formidable Forms', 'Formidable → Forms' ),
			'search-form'  => array( __( 'the theme\'s search form', 'wpzoom-ai-connect' ), __( 'the theme (or the Search block)', 'wpzoom-ai-connect' ) ),
		);
		for ( $node = $field->parentNode; $node instanceof DOMElement; $node = $node->parentNode ) {
			$hay = strtolower( $node->getAttribute( 'class' ) . ' ' . $node->getAttribute( 'id' ) . ( 'search' === strtolower( $node->getAttribute( 'role' ) ) ? ' search-form' : '' ) );
			foreach ( $known as $key => $plugin ) {
				if ( false !== strpos( $hay, $key ) ) {
					return $plugin;
				}
			}
		}
		return null;
	}

	/** The attachment an img shows: its wp-image-123 class, else its URL (size suffix stripped, -scaled tried). */
	private static function attachment_id( DOMElement $img, string $src ): int {
		if ( preg_match( '/(?:^|\s)wp-image-(\d+)(?:\s|$)/', $img->getAttribute( 'class' ), $m ) ) {
			return (int) $m[1];
		}
		if ( '' === $src || ! function_exists( 'attachment_url_to_postid' ) ) {
			return 0;
		}
		$src  = strtok( $src, '?#' );
		$base = preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', $src );
		foreach ( array_unique( array( $src, $base, preg_replace( '/(\.[a-z0-9]+)$/i', '-scaled$1', $base ) ) ) as $candidate ) {
			$id = (int) attachment_url_to_postid( $candidate );
			if ( $id ) {
				return $id;
			}
		}
		return 0;
	}

	/** An image's file name without its size suffix: …/team-2024-300x200.jpg → team-2024. */
	private static function file_stem( string $src ): string {
		$file = basename( (string) strtok( $src, '?#' ) );
		return (string) preg_replace( '/(-\d+x\d+)?(-scaled)?\.[a-z0-9]+$/i', '', $file );
	}
}
