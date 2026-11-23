<?php
/**
 * Who sees AI Connect in wp-admin: every administrator (the default), or only the administrators chosen on
 * the Settings tab of the AI Connect screen. For agencies working on a client's site: the others don't get the
 * menu, the screen, the "Get started" link or the plugin's notices.
 *
 * The plugin stays listed on the Plugins screen, and a connection's application password stays on the connected
 * user's profile, so the site owner can always see that an AI connection exists and remove it.
 *
 * In wp-config.php, define( 'WPZOOM_AI_CONNECT_ADMINS', 'agency-dev, 12, dev@example.com' ) sets the list by login,
 * user ID or email and locks the setting on the screen. The filter wpzoom_ai_connect_user_can_see has the last word.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Access {

	/** The chosen administrators' user IDs; empty means every administrator. */
	const OPTION   = 'wpzoom_ai_connect_admins';
	const CONSTANT = 'WPZOOM_AI_CONNECT_ADMINS';

	/** Whether the list comes from wp-config.php (the screen then shows it without a form). */
	public static function locked(): bool {
		return defined( self::CONSTANT );
	}

	/**
	 * The administrators who see AI Connect, as user IDs. Users who were deleted or are no longer administrators
	 * drop out; when nobody is left, every administrator sees it again, so the screen can never be lost.
	 *
	 * @return int[] Empty when every administrator sees it.
	 */
	public static function chosen(): array {
		if ( self::locked() ) {
			$ids = array();
			foreach ( array_filter( array_map( 'trim', explode( ',', (string) constant( self::CONSTANT ) ) ) ) as $who ) {
				$user = ctype_digit( $who ) ? get_user_by( 'id', (int) $who ) : ( is_email( $who ) ? get_user_by( 'email', $who ) : get_user_by( 'login', $who ) );
				if ( $user ) {
					$ids[] = (int) $user->ID;
				}
			}
		} else {
			$ids = array_map( 'intval', (array) get_option( self::OPTION, array() ) );
		}
		$ids = array_values( array_unique( array_filter( $ids, static function ( $id ) {
			return $id > 0 && user_can( $id, 'manage_options' );
		} ) ) );
		return $ids;
	}

	/** Whether a user (default: the current one) sees the AI Connect menu, screen, links and notices. */
	public static function can_see( int $user_id = 0 ): bool {
		$user_id = $user_id ? $user_id : get_current_user_id();
		$can     = $user_id && user_can( $user_id, 'manage_options' );
		if ( $can ) {
			$chosen = self::chosen();
			$can    = ! $chosen || in_array( $user_id, $chosen, true );
		}
		/**
		 * Filters whether a user sees WPZOOM AI Connect in wp-admin (menu, screen, plugin link, notices).
		 *
		 * @since 0.13.0
		 *
		 * @param bool $can     Whether they see it: administrators, limited to the chosen ones when the list is set.
		 * @param int  $user_id The user.
		 */
		return (bool) apply_filters( 'wpzoom_ai_connect_user_can_see', $can, $user_id );
	}

	/**
	 * Saves the chosen administrators. The person saving is always kept in the list, so they cannot hide the
	 * screen from themselves. An empty list means every administrator.
	 *
	 * @param int[] $ids User IDs.
	 */
	public static function save( array $ids ): void {
		$ids = array_map( 'intval', $ids );
		if ( $ids ) {
			$ids[] = get_current_user_id();
		}
		$ids = array_values( array_unique( array_filter( $ids, static function ( $id ) {
			return $id > 0 && user_can( $id, 'manage_options' );
		} ) ) );
		update_option( self::OPTION, $ids, false );
	}

	/**
	 * The site's administrators (anyone who can manage options), for the checklist.
	 *
	 * @return WP_User[]
	 */
	public static function administrators(): array {
		return get_users(
			array(
				'capability' => 'manage_options',
				'orderby'    => 'display_name',
				'number'     => 100,
			)
		);
	}
}
