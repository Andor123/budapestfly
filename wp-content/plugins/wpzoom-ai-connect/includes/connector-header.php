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
