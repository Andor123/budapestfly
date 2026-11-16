<?php
/**
 * Application-password fallback for hosts that strip the Authorization header before PHP sees it.
 *
 * WordPress turns `Authorization: Basic …` into PHP_AUTH_USER / PHP_AUTH_PW very early (wp-includes/load.php),
 * before plugins load. Some hosts (and some CGI setups) drop that header entirely, so the WPZOOM AI Connect
 * connector sends the same value a second time as `X-WPZOOM-Authorization`. When core found nothing, this
 * populates the PHP_AUTH_* variables from the fallback header so application passwords keep working.
 *
 * Only Basic credentials are accepted, and only when no credentials are present already; nothing is
 * overridden. The values still go through WordPress's own application-password validation.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpzoom_ai_connect_populate_auth_fallback() {
	if ( isset( $_SERVER['PHP_AUTH_USER'] ) || isset( $_SERVER['PHP_AUTH_PW'] ) ) {
		return;
	}
	if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) || isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) {
		return; // Core already had a chance; do not second-guess it.
	}
	$header = isset( $_SERVER['HTTP_X_WPZOOM_AUTHORIZATION'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WPZOOM_AUTHORIZATION'] ) ) : '';
	if ( '' === $header || 0 !== stripos( $header, 'Basic ' ) ) {
		return;
	}
	$decoded = base64_decode( trim( substr( $header, 6 ) ), true );
	if ( false === $decoded || false === strpos( $decoded, ':' ) ) {
		return;
	}
	list( $user, $password ) = explode( ':', $decoded, 2 );
	if ( '' === $user || '' === $password ) {
		return;
	}
	$_SERVER['PHP_AUTH_USER'] = $user;
	$_SERVER['PHP_AUTH_PW']   = $password;
}
wpzoom_ai_connect_populate_auth_fallback();
