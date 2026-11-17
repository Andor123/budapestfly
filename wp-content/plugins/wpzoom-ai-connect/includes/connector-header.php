<?php
/**
 * Tells the WPZOOM connector which versions this site runs: on every REST response to a request made with the
 * WPZOOM AI Connect application password, an `X-WPZOOM-AI-Connect: version=0.6.1; wp=6.9; php=8.3` header.
 * The connector's admin dashboard uses it to see which plugin, WordPress and PHP versions errors come from.
 * Other requests (visitors, the block editor, other applications) never get it.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpzoom_ai_connect_version_header( $response ) {
	if ( $response instanceof WP_REST_Response && WPZOOM_AI_Cache::from_ai_connect() ) {
		$response->header( 'X-WPZOOM-AI-Connect', sprintf( 'version=%s; wp=%s; php=%s', WPZOOM_AI_CONNECT_VERSION, get_bloginfo( 'version' ), PHP_VERSION ) );
	}
	return $response;
}
add_filter( 'rest_post_dispatch', 'wpzoom_ai_connect_version_header', 20 );

/** User meta: application password uuid => the WPZOOM account email that connection belongs to. */
const WPZOOM_AI_CONNECT_ACCOUNTS_META = 'wpzoom_ai_connect_accounts';

/**
 * Remembers which WPZOOM account a connection belongs to. The connector sends it in an `X-WPZOOM-AI-Account` header
 * with every request made with the connection's application password, from the moment it is approved, and the
 * AI Connect screen shows it next to the connection: that is the email to sign in with in Claude or ChatGPT.
 * Requests without the WPZOOM AI Connect application password are ignored.
 *
 * @param mixed           $response Result to send.
 * @param WP_REST_Server  $server   Server instance.
 * @param WP_REST_Request $request  Request used to generate the response.
 * @return mixed
 */
function wpzoom_ai_connect_note_account( $response, $server, $request ) {
	if ( ! $request instanceof WP_REST_Request || ! function_exists( 'rest_get_authenticated_app_password' ) || ! WPZOOM_AI_Scope::is_connector_request() ) {
		return $response;
	}
	$email   = sanitize_email( (string) $request->get_header( 'x_wpzoom_ai_account' ) );
	$uuid    = (string) rest_get_authenticated_app_password();
	$user_id = get_current_user_id();
	if ( ! is_email( $email ) || '' === $uuid || ! $user_id ) {
		return $response;
	}
	$accounts = get_user_meta( $user_id, WPZOOM_AI_CONNECT_ACCOUNTS_META, true );
	$accounts = is_array( $accounts ) ? $accounts : array();
	if ( ( $accounts[ $uuid ] ?? '' ) === $email ) {
		return $response;
	}
	$accounts[ $uuid ] = $email;
	// Drop connections that were removed since.
	$current  = wp_list_pluck( WP_Application_Passwords::get_user_application_passwords( $user_id ), 'uuid' );
	$accounts = array_intersect_key( $accounts, array_flip( $current ) );
	update_user_meta( $user_id, WPZOOM_AI_CONNECT_ACCOUNTS_META, $accounts );
	return $response;
}
add_filter( 'rest_post_dispatch', 'wpzoom_ai_connect_note_account', 20, 3 );

/** The WPZOOM account email a connection belongs to, or '' when the connector has not said yet. */
function wpzoom_ai_connect_account_of( int $user_id, string $uuid ): string {
	$accounts = get_user_meta( $user_id, WPZOOM_AI_CONNECT_ACCOUNTS_META, true );
	return is_array( $accounts ) && isset( $accounts[ $uuid ] ) ? (string) $accounts[ $uuid ] : '';
}
