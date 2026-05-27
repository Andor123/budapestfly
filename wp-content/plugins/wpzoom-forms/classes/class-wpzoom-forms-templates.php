<?php
/**
 * Thin facade around templates/templates.php.
 *
 * The templates file is legacy and stores block markup. We reuse it as-is
 * (single source of truth) and parse the markup into a v2 schema on demand
 * via the Migration class.
 *
 * @package WPZOOM_Forms
 */

defined( 'ABSPATH' ) || exit;

class WPZOOM_Forms_Templates {

	private static $cache = null;

	private static function load() {
		if ( self::$cache !== null ) return self::$cache;
		$file = WPZOOM_FORMS_PATH . 'templates/templates.php';
		self::$cache = file_exists( $file ) ? (array) include $file : array();
		return self::$cache;
	}

	/** All templates as raw data. */
	public static function all() {
		return self::load();
	}

	/** A single template by id, or null. */
	public static function get( $id ) {
		foreach ( self::load() as $t ) {
			if ( isset( $t['id'] ) && $t['id'] === $id ) return $t;
		}
		return null;
	}
}
