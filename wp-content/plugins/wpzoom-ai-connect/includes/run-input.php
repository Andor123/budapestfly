<?php
/**
 * Lets an ability that WordPress routes as DELETE take its input from a JSON body as well as from the query string.
 *
 * Core's run controller (WP_REST_Abilities_V1_Run_Controller) sends a destructive and idempotent ability, such as
 * wpzoom/update-theme-settings or wpzoom/update-seo, over DELETE and reads its input from the query string only.
 * PHP cannot carry a key with brackets in a query string: a theme setting id like
 * "generate_settings[background_color]" (GeneratePress, Astra, OceanWP) arrives as "generate_settings[background_color".
 * Long inputs (recipe steps, Elementor changes) also run into URL length limits. So the WPZOOM connector sends the
 * same input twice on DELETE, in the query string and as a JSON body {"input": ...}; when the body is there it wins.
 * Any other client may do the same. GET (read-only) abilities are unchanged.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Copies a JSON body's "input" over the query-string input of a DELETE run request, before core reads it.
 *
 * @param mixed           $response Result to send to the client; null when nothing short-circuited the request.
 * @param array           $handler  Route handler.
 * @param WP_REST_Request $request  The request.
 * @return mixed The response, unchanged.
 */
function wpzoom_ai_connect_run_input_from_body( $response, $handler, $request ) {
	if ( null !== $response || ! ( $request instanceof WP_REST_Request ) || 'DELETE' !== $request->get_method() ) {
		return $response;
	}
	if ( ! preg_match( '#^/wp-abilities/v1/abilities/[a-zA-Z0-9\-/]+/run$#', $request->get_route() ) ) {
		return $response;
	}
	$json = $request->get_json_params();
	if ( ! is_array( $json ) || ! array_key_exists( 'input', $json ) ) {
		return $response;
	}
	$query          = $request->get_query_params();
	$query['input'] = $json['input'];
	$request->set_query_params( $query );
	return $response;
}
add_filter( 'rest_request_before_callbacks', 'wpzoom_ai_connect_run_input_from_body', 5, 3 );
