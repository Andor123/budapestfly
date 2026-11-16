<?php
/**
 * Which custom post types the WPZOOM AI Connect connection may reach, chosen by the site owner on the AI Connect
 * screen: no access, read only, or read and edit (option wpzoom_ai_connect_post_types).
 *
 * The choice is enforced here, on the site, for requests made with the connection's application password
 * (WPZOOM_AI_Scope): REST routes of the type and the per-post capabilities. The connector only asks which types are
 * open so it can offer them. Posts, pages and WordPress's other built-in types are outside this setting.
 *
 * A type the owner has not chosen for yet is closed, because custom post types can hold private data such as form
 * submissions or orders. WPZOOM's own types are open by default: dedicated tools already work on them.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Post_Types {

	const OPTION = 'wpzoom_ai_connect_post_types';

	const NONE = 'none';
	const READ = 'read';
	const EDIT = 'edit';

	/** Types with dedicated WPZOOM tools, open until the owner closes them. */
	const OPEN_BY_DEFAULT = array( 'portfolio_item', 'slider', 'wpzoom_rcb', 'wpzf-form', 'elementor_library' );

	/**
	 * Types readable until the owner decides: WooCommerce products are the public catalog, and the first store owners
	 * asked their AI about them within minutes. Orders, coupons and the rest stay closed; editing products needs the
	 * owner's choice on the AI Connect screen.
	 */
	const READ_BY_DEFAULT = array( 'product' );

	/**
	 * Other plugins' abilities that read or change a post type's items without going through its REST routes or
	 * per-post capabilities, so the owner's choice is enforced on them here: ability => [ post type, is a read ].
	 * WooCommerce's order abilities, for one, check type-level capabilities, and with HPOS an order is not a post.
	 */
	const ABILITY_TYPES = array(
		'woocommerce/orders-query'        => array( 'shop_order', true ),
		'woocommerce/order-notes-query'   => array( 'shop_order', true ),
		'woocommerce/order-add-note'      => array( 'shop_order', false ),
		'woocommerce/order-update-status' => array( 'shop_order', false ),
		'woocommerce/products-query'      => array( 'product', true ),
		'woocommerce/product-create'      => array( 'product', false ),
		'woocommerce/product-update'      => array( 'product', false ),
		'woocommerce/product-delete'      => array( 'product', false ),
	);

	/** Per-post capabilities that only look at a post. */
	const READ_CAPS  = array( 'read_post', 'read_page' );
	const WRITE_CAPS = array( 'edit_post', 'edit_page', 'delete_post', 'delete_page', 'publish_post' );

	/** Whether this setting applies to a post type: every registered type that is not built into WordPress. */
	private static function governed( string $post_type ): bool {
		$type = get_post_type_object( $post_type );
		return $type && ! $type->_builtin;
	}

	/**
	 * Types that follow another type's choice: WooCommerce keeps a placeholder post for each order stored in its own
	 * tables (HPOS), and refunds are orders too.
	 */
	const TYPE_ALIASES = array( 'shop_order_placehold' => 'shop_order', 'shop_order_refund' => 'shop_order' );

	/** The access a post type has through the connection. */
	public static function mode( string $post_type ): string {
		$post_type = self::TYPE_ALIASES[ $post_type ] ?? $post_type;
		if ( ! self::governed( $post_type ) ) {
			return self::EDIT;
		}
		$saved = get_option( self::OPTION, array() );
		$mode  = is_array( $saved ) ? ( $saved[ $post_type ] ?? null ) : null;
		if ( in_array( $mode, array( self::NONE, self::READ, self::EDIT ), true ) ) {
			return $mode;
		}
		if ( in_array( $post_type, self::OPEN_BY_DEFAULT, true ) ) {
			return self::EDIT;
		}
		return in_array( $post_type, self::READ_BY_DEFAULT, true ) ? self::READ : self::NONE;
	}

	/** Whether an access mode allows a read or a write. */
	public static function mode_allows( string $mode, bool $read ): bool {
		return self::EDIT === $mode || ( self::READ === $mode && $read );
	}

	/** Whether the current request may write to this type. Requests not made with the connection are not limited here. */
	public static function may_write( string $post_type ): bool {
		return ! WPZOOM_AI_Scope::is_connector_request() || self::mode_allows( self::mode( $post_type ), false );
	}

	/** The access of the custom post type a REST route belongs to, or null when it does not belong to one. */
	public static function mode_for_route( string $route ): ?string {
		foreach ( get_post_types( array( '_builtin' => false, 'show_in_rest' => true ), 'objects' ) as $type ) {
			$prefix = '/' . trim( $type->rest_namespace ?: 'wp/v2', '/' ) . '/' . ( $type->rest_base ?: $type->name );
			if ( $route === $prefix || 0 === strpos( $route, $prefix . '/' ) ) {
				return self::mode( $type->name );
			}
		}
		return null;
	}

	/**
	 * Whether the setting refuses a per-post capability, so tools that work on a post by id respect it as well as the
	 * REST routes do. A read-only type keeps edit_post on read requests: WordPress asks for it to return a post with
	 * context=edit, and the request cannot change anything.
	 */
	public static function denies_cap( string $cap, array $args, bool $read_request ): bool {
		$is_read  = in_array( $cap, self::READ_CAPS, true );
		$is_write = in_array( $cap, self::WRITE_CAPS, true );
		if ( ( ! $is_read && ! $is_write ) || empty( $args[0] ) || ! is_numeric( $args[0] ) ) {
			return false;
		}
		$post = get_post( (int) $args[0] );
		return $post && ! self::mode_allows( self::mode( $post->post_type ), $is_read || $read_request );
	}

	/**
	 * The custom post types for the AI Connect screen. A type closed to the REST API cannot be reached by the generic
	 * tools, so it is only offered when WPZOOM has dedicated tools for it.
	 *
	 * @return array[] Each with type, label, mode and available.
	 */
	public static function choices(): array {
		$out = array();
		foreach ( get_post_types( array( '_builtin' => false ), 'objects' ) as $type ) {
			if ( ! $type->public && ! $type->show_ui ) {
				continue;
			}
			$out[] = array(
				'type'      => $type->name,
				'label'     => $type->labels->name,
				'mode'      => self::mode( $type->name ),
				'available' => $type->show_in_rest || in_array( $type->name, self::OPEN_BY_DEFAULT, true ) || in_array( $type->name, array_column( self::ability_types(), 0 ), true ),
			);
		}
		usort( $out, static function ( $a, $b ) { return strcasecmp( $a['label'], $b['label'] ); } );
		return $out;
	}

	/**
	 * Saves the owner's choices. Types not in the form keep their current access.
	 *
	 * @param array $posted Post type => mode.
	 */
	public static function save( array $posted ): void {
		$saved = get_option( self::OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();
		foreach ( self::choices() as $choice ) {
			$mode = $posted[ $choice['type'] ] ?? null;
			if ( $choice['available'] && in_array( $mode, array( self::NONE, self::READ, self::EDIT ), true ) ) {
				$saved[ $choice['type'] ] = $mode;
			}
		}
		update_option( self::OPTION, $saved, false );
	}

	/* ----- other plugins' abilities ----- */

	/** ABILITY_TYPES, extendable by other plugins. */
	public static function ability_types(): array {
		/**
		 * Filters which abilities read or change which post type, so the AI Connect screen's choice governs them.
		 *
		 * @since 0.12.1
		 *
		 * @param array $map Ability name => array( post type, true for a read ).
		 */
		return (array) apply_filters( 'wpzoom_ai_connect_ability_post_types', self::ABILITY_TYPES );
	}

	/**
	 * The post type an ability works on and whether it only reads, or null when the choice does not govern it.
	 * WooCommerce abilities not in the map are placed by their name, so a new one is not open before it is known:
	 * orders and refunds, coupons, products; anything else (customers...) maps to a type that is always closed.
	 */
	private static function ability_target( string $name ): ?array {
		$map = self::ability_types();
		if ( isset( $map[ $name ] ) && is_array( $map[ $name ] ) ) {
			return array( (string) $map[ $name ][0], (bool) $map[ $name ][1] );
		}
		if ( 0 !== strpos( $name, 'woocommerce/' ) ) {
			return null;
		}
		$read = (bool) preg_match( '/-(query|get|list|search|read)$/', $name );
		foreach ( array( '/order|refund/' => 'shop_order', '/coupon/' => 'shop_coupon', '/product|variation/' => 'product' ) as $pattern => $type ) {
			if ( preg_match( $pattern, $name ) ) {
				return array( $type, $read );
			}
		}
		return array( '', $read );
	}

	/**
	 * wp_register_ability_args: wraps the permission check of the abilities ability_target() places, so the owner's
	 * choice applies however the ability is run (REST, MCP adapter, PHP). Other callers are not limited here.
	 */
	public static function gate_ability_args( $args, $name ) {
		if ( ! is_array( $args ) || ! is_string( $name ) || null === self::ability_target( $name ) || ! isset( $args['permission_callback'] ) || ! is_callable( $args['permission_callback'] ) ) {
			return $args;
		}
		$original                    = $args['permission_callback'];
		$args['permission_callback'] = static function ( ...$params ) use ( $original, $name ) {
			$refusal = self::ability_refusal( $name );
			return $refusal ? $refusal : call_user_func_array( $original, $params );
		};
		return $args;
	}

	/** Why the connection may not run an ability, or null when it may. */
	public static function ability_refusal( string $name ): ?WP_Error {
		$target = self::ability_target( $name );
		if ( null === $target || ! WPZOOM_AI_Scope::is_connector_request() ) {
			return null;
		}
		list( $type, $read ) = $target;
		$object = '' !== $type ? get_post_type_object( $type ) : null;
		$mode   = $object ? self::mode( $type ) : self::NONE;
		if ( self::mode_allows( $mode, $read ) ) {
			return null;
		}
		$what = $object ? $object->labels->name : __( 'This store data', 'wpzoom-ai-connect' );
		return new WP_Error(
			'wpzoom_ai_type_closed',
			$object
				? sprintf(
					/* translators: 1: post type name such as Orders, 2: closed or read-only */
					__( '%1$s are %2$s for this AI connection. The site owner chooses what the AI may do with them on the AI Connect screen in WordPress (AI Connect in the admin menu).', 'wpzoom-ai-connect' ),
					$what,
					self::READ === $mode ? __( 'read-only', 'wpzoom-ai-connect' ) : __( 'closed', 'wpzoom-ai-connect' )
				)
				: __( 'This store data is closed to the AI connection.', 'wpzoom-ai-connect' ),
			array( 'status' => 403 )
		);
	}

	/** The custom post types open through the REST API, as the generic content tools need them. */
	public static function list_open(): array {
		$out = array();
		foreach ( get_post_types( array( '_builtin' => false, 'show_in_rest' => true ), 'objects' ) as $type ) {
			$mode = self::mode( $type->name );
			if ( self::NONE === $mode ) {
				continue;
			}
			$taxonomies = array();
			foreach ( get_object_taxonomies( $type->name, 'objects' ) as $taxonomy ) {
				// Only routes the connection may call: a taxonomy under its own namespace is out of its scope.
				if ( $taxonomy->show_in_rest && 'wp/v2' === ( $taxonomy->rest_namespace ?: 'wp/v2' ) ) {
					$taxonomies[] = array(
						'taxonomy'  => $taxonomy->name,
						'label'     => $taxonomy->labels->name,
						'rest_base' => $taxonomy->rest_base ?: $taxonomy->name,
					);
				}
			}
			$out[] = array(
				'type'           => $type->name,
				'label'          => $type->labels->name,
				'access'         => $mode,
				'rest_base'      => $type->rest_base ?: $type->name,
				'rest_namespace' => trim( $type->rest_namespace ?: 'wp/v2', '/' ),
				'hierarchical'   => (bool) $type->hierarchical,
				'supports'       => array_keys( get_all_post_type_supports( $type->name ) ),
				'taxonomies'     => $taxonomies,
			);
		}
		return $out;
	}
}
