<?php
/**
 * Available updates, read only: which themes and plugins have a newer version and what would block it.
 *
 * Nothing is installed or updated here. The user updates in Dashboard → Updates; the AI can only tell them what is
 * waiting there. Themes are listed only to users who can update themes, plugins only to users who can update
 * plugins.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Updates {

	private static function load_admin(): void {
		require_once ABSPATH . 'wp-admin/includes/update.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/theme.php';
	}

	/** Asks WordPress.org and the premium update channels again (they cache for up to 12 hours), for what the user may update. */
	private static function refresh( bool $themes, bool $plugins ): void {
		if ( $themes ) {
			delete_site_transient( 'update_themes' );
			wp_update_themes();
		}
		if ( $plugins ) {
			delete_site_transient( 'update_plugins' );
			wp_update_plugins();
		}
	}

	private static function source( string $package ): string {
		if ( '' === $package ) {
			return __( 'no download', 'wpzoom-ai-connect' );
		}
		$host = (string) wp_parse_url( $package, PHP_URL_HOST );
		return false !== strpos( $host, 'wordpress.org' ) ? 'WordPress.org' : $host;
	}

	/** Why an update without a download package cannot be installed, in plain words. */
	private static function no_package_reason( string $type, string $name, string $author ): string {
		if ( 'theme' === $type && false !== stripos( $author, 'wpzoom' ) && class_exists( 'WPZOOM_AI_Diagnostics' ) ) {
			$license = WPZOOM_AI_Diagnostics::license_status();
			if ( empty( $license['license_valid'] ) ) {
				return implode(
					' ',
					array_filter(
						array(
							sprintf( /* translators: 1: theme name, 2: license status */ __( '%1$s updates need an active WPZOOM license on this site (status: %2$s).', 'wpzoom-ai-connect' ), $name, $license['license'] ?? __( 'unknown', 'wpzoom-ai-connect' ) ),
							(string) ( $license['meaning'] ?? '' ),
							sprintf( /* translators: %s: where to manage the license, a URL */ __( 'Activate or renew it at %s, then try again.', 'wpzoom-ai-connect' ), $license['manage_url'] ?? __( 'the theme\'s License page', 'wpzoom-ai-connect' ) ),
						)
					)
				);
			}
		}
		return sprintf( /* translators: %s: theme or plugin name */ __( 'The update for %s has no download, which usually means its license is missing or expired, or the vendor delivers updates elsewhere. Check Dashboard → Updates or the vendor\'s site.', 'wpzoom-ai-connect' ), $name );
	}

	/** Problems that block an update before it starts: requirements and file access. */
	private static function blockers( array $update ): array {
		$out = array();
		if ( ! empty( $update['requires_php'] ) && version_compare( PHP_VERSION, (string) $update['requires_php'], '<' ) ) {
			$out[] = sprintf( /* translators: 1: required PHP version, 2: the site's PHP version */ __( 'needs PHP %1$s, the site runs %2$s (ask the host to upgrade PHP first)', 'wpzoom-ai-connect' ), $update['requires_php'], PHP_VERSION );
		}
		if ( ! empty( $update['requires_wp'] ) && version_compare( get_bloginfo( 'version' ), (string) $update['requires_wp'], '<' ) ) {
			$out[] = sprintf( /* translators: 1: required WordPress version, 2: the site's WordPress version */ __( 'needs WordPress %1$s, the site runs %2$s (update WordPress first in Dashboard → Updates)', 'wpzoom-ai-connect' ), $update['requires_wp'], get_bloginfo( 'version' ) );
		}
		return $out;
	}

	/** @return array{0: array, 1: array} themes and plugins with the update data WordPress has. */
	private static function items(): array {
		$theme_updates  = get_site_transient( 'update_themes' );
		$plugin_updates = get_site_transient( 'update_plugins' );

		$themes = array();
		foreach ( wp_get_themes() as $stylesheet => $theme ) {
			$u        = isset( $theme_updates->response[ $stylesheet ] ) ? (array) $theme_updates->response[ $stylesheet ] : null;
			$update   = $u ? array(
				'new_version'  => (string) ( $u['new_version'] ?? '' ),
				'download'     => ! empty( $u['package'] ),
				'source'       => self::source( (string) ( $u['package'] ?? '' ) ),
				'requires_wp'  => (string) ( $u['requires'] ?? '' ),
				'requires_php' => (string) ( $u['requires_php'] ?? '' ),
			) : null;
			$themes[] = array(
				'type'    => 'theme',
				'slug'    => (string) $stylesheet,
				'name'    => $theme->get( 'Name' ),
				'author'  => wp_strip_all_tags( (string) $theme->get( 'Author' ) ),
				'version' => $theme->get( 'Version' ),
				'active'  => get_stylesheet() === $stylesheet,
				'parent'  => get_template() === $stylesheet && get_stylesheet() !== $stylesheet,
				'update'  => $update,
			);
		}

		$plugins = array();
		foreach ( get_plugins() as $file => $data ) {
			$u        = isset( $plugin_updates->response[ $file ] ) ? (array) $plugin_updates->response[ $file ] : null;
			$update   = $u ? array(
				'new_version'  => (string) ( $u['new_version'] ?? '' ),
				'download'     => ! empty( $u['package'] ),
				'source'       => self::source( (string) ( $u['package'] ?? '' ) ),
				'requires_wp'  => (string) ( $u['requires'] ?? '' ),
				'requires_php' => (string) ( $u['requires_php'] ?? '' ),
			) : null;
			$plugins[] = array(
				'type'    => 'plugin',
				'slug'    => (string) $file,
				'name'    => (string) $data['Name'],
				'author'  => wp_strip_all_tags( (string) $data['Author'] ),
				'version' => (string) $data['Version'],
				'active'  => is_plugin_active( $file ),
				'update'  => $update,
			);
		}
		return array( $themes, $plugins );
	}

	/** wpzoom/list-updates */
	public static function all( array $in = array() ) {
		self::load_admin();
		$can_themes  = current_user_can( 'update_themes' );
		$can_plugins = current_user_can( 'update_plugins' );
		if ( ! empty( $in['refresh'] ) ) {
			self::refresh( $can_themes, $can_plugins );
		}
		list( $themes, $plugins ) = self::items();
		$all     = ! empty( $in['include_all'] );
		$pending = array();
		foreach ( array_merge( $can_themes ? $themes : array(), $can_plugins ? $plugins : array() ) as $item ) {
			if ( ! $item['update'] && ! $all && ! ( 'theme' === $item['type'] && ( $item['active'] || $item['parent'] ) ) ) {
				continue;
			}
			if ( $item['update'] ) {
				$why = array();
				if ( ! $item['update']['download'] ) {
					$why[] = self::no_package_reason( $item['type'], $item['name'], $item['author'] );
				}
				$why = array_merge( $why, self::blockers( $item['update'] ) );
				if ( $why ) {
					$item['update']['blocked'] = implode( ' ', $why );
				}
			}
			unset( $item['author'] );
			$pending[] = $item;
		}

		$core   = get_core_updates( array( 'dismissed' => false ) );
		$latest = null;
		foreach ( (array) $core as $offer ) {
			if ( isset( $offer->response ) && 'upgrade' === $offer->response ) {
				$latest = $offer->current ?? $offer->version ?? null;
				break;
			}
		}
		$count = count( array_filter( $pending, static function ( $i ) { return ! empty( $i['update'] ); } ) );
		return array(
			'wordpress'       => array( 'version' => get_bloginfo( 'version' ), 'update' => $latest, 'note' => $latest ? __( 'WordPress itself is updated in Dashboard → Updates.', 'wpzoom-ai-connect' ) : __( 'Up to date.', 'wpzoom-ai-connect' ) ),
			'php'             => PHP_VERSION,
			'updates'         => $count,
			'items'           => $pending,
			'last_checked'    => ( $t = get_site_transient( 'update_plugins' ) ) && ! empty( $t->last_checked ) ? gmdate( 'Y-m-d H:i', (int) $t->last_checked ) . ' UTC' : 'unknown',
			'listed'          => array( 'themes' => $can_themes, 'plugins' => $can_plugins ),
			'update_url'      => self_admin_url( 'update-core.php' ),
			'note'            => $count ? __( 'This connector does not install updates. Tell the user what is waiting and send them to Dashboard → Updates (update_url) to install it; items with "blocked" cannot be updated until the reason is fixed.', 'wpzoom-ai-connect' ) : __( 'Everything listed is up to date. Pass refresh: true to ask the update servers again.', 'wpzoom-ai-connect' ),
		);
	}

	/** For the checkup: updates WordPress already knows about for what the site actually runs (no network calls). */
	public static function outdated(): array {
		self::load_admin();
		list( $themes, $plugins ) = self::items();
		$out = array();
		foreach ( array_merge( $themes, $plugins ) as $item ) {
			$used = 'theme' === $item['type'] ? ( $item['active'] || $item['parent'] ) : $item['active'];
			if ( $used && $item['update'] ) {
				$out[] = sprintf( '%s %s → %s%s', $item['name'], $item['version'], $item['update']['new_version'], $item['update']['download'] ? '' : ' (needs a license)' );
			}
		}
		return $out;
	}
}
