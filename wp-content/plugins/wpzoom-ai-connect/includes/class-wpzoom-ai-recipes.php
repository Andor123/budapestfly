<?php
/**
 * Existing Recipe Card Blocks recipes, found, read and edited by an AI.
 *
 * A recipe is a wpzoom-recipe-card/block-recipe-card block; its data lives in the block's attributes inside
 * the post content (posts, pages, or the plugin's wpzoom_rcb recipe post type). A recipe is addressed by the
 * post that holds it and its position among that post's recipe cards (0 for the first). Edits rewrite the
 * attributes and save the post, which keeps a revision.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Recipes {

	const BLOCK = 'wpzoom-recipe-card/block-recipe-card';

	private static function unavailable() {
		return new WP_Error( 'wpzoom_no_recipes', __( 'Recipe cards are not available: install and activate Recipe Card Blocks by WPZOOM.', 'wpzoom-ai-connect' ) );
	}

	/** Plain text from a rich-text value: a string, or the editor's nested array of strings and elements. */
	private static function flat( $value ): string {
		if ( is_string( $value ) || is_numeric( $value ) ) {
			return (string) $value;
		}
		if ( is_array( $value ) ) {
			if ( isset( $value['props']['children'] ) ) {
				return self::flat( $value['props']['children'] );
			}
			return implode( '', array_map( array( __CLASS__, 'flat' ), $value ) );
		}
		return '';
	}

	/** Every recipe block in a block list, depth first (recipes can sit inside columns or groups). */
	private static function &collect( array &$blocks, array &$found ) {
		foreach ( $blocks as &$block ) {
			if ( self::BLOCK === ( $block['blockName'] ?? '' ) ) {
				$found[] = &$block;
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				self::collect( $block['innerBlocks'], $found );
			}
		}
		return $found;
	}

	private static function summary( array $a ): array {
		$detail = static function ( string $label ) use ( $a ) {
			foreach ( (array) ( $a['details'] ?? array() ) as $d ) {
				if ( isset( $d['label'] ) && 0 === strcasecmp( (string) $d['label'], $label ) ) {
					return trim( ( $d['value'] ?? '' ) . ' ' . ( $d['unit'] ?? '' ) );
				}
			}
			return '';
		};
		return array(
			'title'       => self::flat( $a['recipeTitle'] ?? '' ),
			'servings'    => $detail( 'Servings' ),
			'total_time'  => $detail( 'Total time' ),
			'ingredients' => count( array_filter( (array) ( $a['ingredients'] ?? array() ), static function ( $i ) { return empty( $i['isGroup'] ); } ) ),
			'steps'       => count( array_filter( (array) ( $a['steps'] ?? array() ), static function ( $s ) { return empty( $s['isGroup'] ); } ) ),
		);
	}

	private static function items( array $list, string $text_key, string $json_key ): array {
		$out = array();
		foreach ( $list as $item ) {
			$text = isset( $item[ $json_key ] ) && '' !== $item[ $json_key ] ? (string) $item[ $json_key ] : self::flat( $item[ $text_key ] ?? '' );
			$out[] = ! empty( $item['isGroup'] ) ? array( 'group' => wp_strip_all_tags( $text ) ) : array( 'text' => wp_strip_all_tags( $text ) );
		}
		return $out;
	}

	public static function all( array $in = array() ) {
		if ( ! WPZOOM_AI_Content::has_recipes() ) {
			return self::unavailable();
		}
		global $wpdb;
		$per_page = max( 1, min( 50, (int) ( $in['per_page'] ?? 20 ) ) );
		$page     = max( 1, (int) ( $in['page'] ?? 1 ) );
		$search   = trim( (string) ( $in['search'] ?? '' ) );
		$like     = '%' . $wpdb->esc_like( '<!-- wp:' . self::BLOCK ) . '%';
		$where    = $wpdb->prepare( "post_content LIKE %s AND post_status NOT IN ('trash','auto-draft','inherit') AND post_type NOT IN ('revision')", $like );
		if ( '' !== $search ) {
			$where .= $wpdb->prepare( ' AND (post_title LIKE %s OR post_content LIKE %s)', '%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- $where is built from prepare() above; no core API finds posts by block.
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE {$where}" );
		$ids   = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE {$where} ORDER BY post_modified DESC LIMIT %d OFFSET %d", $per_page, ( $page - 1 ) * $per_page ) );
		// phpcs:enable

		$items = array();
		foreach ( $ids as $id ) {
			$post = get_post( (int) $id );
			if ( ! $post || ! current_user_can( 'edit_post', $post->ID ) ) {
				continue;
			}
			$blocks  = parse_blocks( $post->post_content );
			$found   = array();
			$recipes = array();
			foreach ( self::collect( $blocks, $found ) as $index => $block ) {
				$recipes[] = array( 'index' => $index ) + self::summary( (array) $block['attrs'] );
			}
			$items[] = array(
				'post_id'   => $post->ID,
				'post_title'=> get_the_title( $post ),
				'post_type' => $post->post_type,
				'status'    => $post->post_status,
				'modified'  => $post->post_modified,
				'link'      => get_permalink( $post ),
				'recipes'   => $recipes,
			);
		}
		return array( 'total_posts' => $total, 'page' => $page, 'pages' => (int) ceil( $total / $per_page ), 'items' => $items );
	}

	/** The post and the parsed blocks with a reference to the wanted recipe block. */
	private static function locate( int $post_id, int $index ) {
		if ( ! WPZOOM_AI_Content::has_recipes() ) {
			return self::unavailable();
		}
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error( 'wpzoom_recipe_not_found', sprintf( /* translators: %d: post ID */ __( 'Post %d does not exist.', 'wpzoom-ai-connect' ), $post_id ) );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error( 'wpzoom_recipe_forbidden', __( 'This WordPress user cannot edit that post.', 'wpzoom-ai-connect' ) );
		}
		return array( 'post' => $post, 'blocks' => parse_blocks( $post->post_content ) );
	}

	public static function get( array $in ) {
		$index = (int) ( $in['index'] ?? 0 );
		$found = self::locate( (int) ( $in['post_id'] ?? 0 ), $index );
		if ( is_wp_error( $found ) ) {
			return $found;
		}
		$list = array();
		self::collect( $found['blocks'], $list );
		if ( ! isset( $list[ $index ] ) ) {
			return new WP_Error( 'wpzoom_recipe_not_found', sprintf( /* translators: 1: post ID, 2: number of recipe cards, 3: the index sent */ _n( 'Post %1$d has %2$d recipe card; index %3$d does not exist.', 'Post %1$d has %2$d recipe cards; index %3$d does not exist.', count( $list ), 'wpzoom-ai-connect' ), $found['post']->ID, count( $list ), $index ) );
		}
		$a       = (array) $list[ $index ]['attrs'];
		$details = array();
		foreach ( (array) ( $a['details'] ?? array() ) as $d ) {
			if ( isset( $d['label'] ) && '' !== (string) $d['label'] ) {
				$details[] = array( 'label' => (string) $d['label'], 'value' => (string) ( $d['value'] ?? '' ), 'unit' => (string) ( $d['unit'] ?? '' ) );
			}
		}
		preg_match_all( '#<li[^>]*>(.*?)</li>#s', (string) ( $a['notes'] ?? '' ), $notes );
		return array(
			'post_id'     => $found['post']->ID,
			'index'       => $index,
			'recipes_in_post' => count( $list ),
			'title'       => self::flat( $a['recipeTitle'] ?? '' ),
			'summary'     => wp_strip_all_tags( isset( $a['jsonSummary'] ) && '' !== $a['jsonSummary'] ? (string) $a['jsonSummary'] : self::flat( $a['summary'] ?? '' ) ),
			'image'       => (string) ( $a['image']['url'] ?? '' ),
			'course'      => array_values( (array) ( $a['course'] ?? array() ) ),
			'cuisine'     => array_values( (array) ( $a['cuisine'] ?? array() ) ),
			'difficulty'  => array_values( (array) ( $a['difficulty'] ?? array() ) ),
			'keywords'    => array_values( (array) ( $a['keywords'] ?? array() ) ),
			'details'     => $details,
			'ingredients' => self::items( (array) ( $a['ingredients'] ?? array() ), 'name', 'jsonName' ),
			'steps'       => self::items( (array) ( $a['steps'] ?? array() ), 'text', 'jsonText' ),
			'notes'       => array_map( static function ( $n ) { return trim( wp_strip_all_tags( $n ) ); }, $notes[1] ),
			'edit_url'    => get_edit_post_link( $found['post']->ID, 'raw' ),
		);
	}

	/** Ingredient or step entries in the block's format, from plain strings or { text } / { group } objects. */
	private static function to_items( array $items, string $text_key, string $json_key, string $prefix ): array {
		$out = array();
		$n   = 0;
		foreach ( $items as $item ) {
			$group = is_array( $item ) ? (string) ( $item['group'] ?? '' ) : '';
			$text  = is_array( $item ) ? (string) ( $item['text'] ?? '' ) : (string) $item;
			if ( '' !== $group ) {
				$group = sanitize_text_field( $group );
				$out[] = array( 'id' => $prefix . ( ++$n ), 'isGroup' => true, $text_key => array( $group ), $json_key => $group );
			}
			$text = sanitize_text_field( $text );
			if ( '' !== $text ) {
				$out[] = array( 'id' => $prefix . ( ++$n ), $text_key => array( $text ), $json_key => $text );
			}
		}
		return $out;
	}

	/** Find/replace inside each ingredient or step (both the rich value and the JSON copy). */
	private static function replace_in( array $list, string $text_key, string $json_key, array $edits, int &$hits ): array {
		foreach ( $list as &$item ) {
			foreach ( $edits as $edit ) {
				$find    = (string) ( $edit['find'] ?? '' );
				$replace = (string) ( $edit['replace'] ?? '' );
				if ( '' === $find ) {
					continue;
				}
				$text = isset( $item[ $json_key ] ) && '' !== $item[ $json_key ] ? (string) $item[ $json_key ] : self::flat( $item[ $text_key ] ?? '' );
				if ( false === strpos( $text, $find ) ) {
					continue;
				}
				$hits            += substr_count( $text, $find );
				$text             = str_replace( $find, $replace, $text );
				$item[ $json_key ] = $text;
				$item[ $text_key ] = array( $text );
			}
		}
		unset( $item );
		return $list;
	}

	public static function update( array $in ) {
		$post_id = (int) ( $in['post_id'] ?? 0 );
		$index   = (int) ( $in['index'] ?? 0 );
		$found   = self::locate( $post_id, $index );
		if ( is_wp_error( $found ) ) {
			return $found;
		}
		$blocks = $found['blocks'];
		$list   = array();
		self::collect( $blocks, $list );
		if ( ! isset( $list[ $index ] ) ) {
			return new WP_Error( 'wpzoom_recipe_not_found', sprintf( /* translators: 1: post ID, 2: number of recipe cards, 3: the index sent */ _n( 'Post %1$d has %2$d recipe card; index %3$d does not exist.', 'Post %1$d has %2$d recipe cards; index %3$d does not exist.', count( $list ), 'wpzoom-ai-connect' ), $post_id, count( $list ), $index ) );
		}
		$block   = &$list[ $index ];
		$a       = (array) $block['attrs'];
		$changed = array();

		if ( isset( $in['title'] ) ) {
			$a['recipeTitle'] = sanitize_text_field( (string) $in['title'] );
			$changed[]        = 'title';
		}
		if ( isset( $in['summary'] ) ) {
			$a['summary']     = sanitize_textarea_field( (string) $in['summary'] );
			$a['jsonSummary'] = $a['summary'];
			$changed[]        = 'summary';
		}
		foreach ( array( 'course', 'cuisine', 'difficulty', 'keywords' ) as $key ) {
			if ( isset( $in[ $key ] ) ) {
				$a[ $key ] = array_values( array_filter( array_map( 'sanitize_text_field', (array) $in[ $key ] ) ) );
				$changed[] = $key;
			}
		}
		if ( isset( $in['details'] ) && is_array( $in['details'] ) ) {
			foreach ( $in['details'] as $label => $value ) {
				$hit = false;
				foreach ( (array) ( $a['details'] ?? array() ) as $i => $d ) {
					if ( isset( $d['label'] ) && 0 === strcasecmp( (string) $d['label'], (string) $label ) ) {
						if ( is_array( $value ) ) {
							if ( isset( $value['value'] ) ) {
								$a['details'][ $i ]['value'] = sanitize_text_field( (string) $value['value'] );
							}
							if ( isset( $value['unit'] ) ) {
								$a['details'][ $i ]['unit'] = sanitize_text_field( (string) $value['unit'] );
							}
						} else {
							$a['details'][ $i ]['value'] = sanitize_text_field( (string) $value );
						}
						$hit = true;
					}
				}
				if ( ! $hit ) {
					return new WP_Error( 'wpzoom_recipe_detail', sprintf( /* translators: 1: detail label sent, 2: the recipe's detail labels, comma-separated */ __( 'This recipe has no "%1$s" detail. It has: %2$s.', 'wpzoom-ai-connect' ), $label, implode( ', ', array_filter( wp_list_pluck( (array) ( $a['details'] ?? array() ), 'label' ) ) ) ) );
				}
				$changed[] = 'details: ' . $label;
			}
		}
		if ( isset( $in['ingredients'] ) && is_array( $in['ingredients'] ) ) {
			$a['ingredients'] = self::to_items( $in['ingredients'], 'name', 'jsonName', 'ingredient-item-' );
			$changed[]        = 'ingredients';
		}
		if ( isset( $in['steps'] ) && is_array( $in['steps'] ) ) {
			$a['steps'] = self::to_items( $in['steps'], 'text', 'jsonText', 'direction-step-' );
			$changed[]  = 'steps';
		}
		if ( isset( $in['notes'] ) && is_array( $in['notes'] ) ) {
			$a['notes'] = implode( '', array_map( static function ( $n ) { return '<li>' . esc_html( sanitize_text_field( (string) $n ) ) . '</li>'; }, $in['notes'] ) );
			$changed[]  = 'notes';
		}
		if ( ! empty( $in['replacements'] ) && is_array( $in['replacements'] ) ) {
			$hits             = 0;
			$a['ingredients'] = self::replace_in( (array) ( $a['ingredients'] ?? array() ), 'name', 'jsonName', $in['replacements'], $hits );
			$a['steps']       = self::replace_in( (array) ( $a['steps'] ?? array() ), 'text', 'jsonText', $in['replacements'], $hits );
			if ( 0 === $hits ) {
				return new WP_Error( 'wpzoom_recipe_replace', __( 'None of the "find" texts appear in the ingredients or steps. Read the recipe with wpzoom/get-recipe and quote exactly.', 'wpzoom-ai-connect' ) );
			}
			$changed[] = "replacements ({$hits} found)";
		}
		if ( ! $changed ) {
			return new WP_Error( 'wpzoom_recipe_empty', __( 'Nothing to change: pass at least one field.', 'wpzoom-ai-connect' ) );
		}

		$block['attrs'] = $a;
		unset( $block );
		$result = wp_update_post( array( 'ID' => $post_id, 'post_content' => wp_slash( serialize_blocks( $blocks ) ) ), true );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return array(
			'post_id'  => $post_id,
			'index'    => $index,
			'changed'  => $changed,
			'link'     => get_permalink( $post_id ),
			'edit_url' => get_edit_post_link( $post_id, 'raw' ),
			'note'     => __( 'Saved. The previous version is kept as a revision of the post.', 'wpzoom-ai-connect' ),
		);
	}
}
