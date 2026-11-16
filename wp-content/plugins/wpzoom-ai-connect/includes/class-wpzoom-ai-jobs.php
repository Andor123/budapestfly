<?php
/**
 * Background jobs for abilities that take longer than an HTTP request should (demo import, AI page
 * build). A job is queued in an option, run by WP-Cron (spawned right away), and polled with the
 * `wpzoom/job-status` ability. Handlers are registered per job type and receive a progress callback.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Jobs {

	const HOOK       = 'wpzoom_ai_connect_run_job';
	const INDEX      = 'wpzoom_ai_jobs';
	const KEEP       = 30;
	const STALE_SECS = 1800; // A running job with no update for 30 minutes is treated as dead.

	/** @var array<string, callable> type => handler( array $args, callable $progress, array $job ): mixed|WP_Error */
	private static $handlers = array();

	public static function init(): void {
		add_action( self::HOOK, array( __CLASS__, 'run' ) );
	}

	public static function register_handler( string $type, callable $handler ): void {
		self::$handlers[ $type ] = $handler;
	}

	private static function key( string $id ): string {
		return 'wpzoom_ai_job_' . $id;
	}

	/**
	 * Queues a job and asks WP-Cron to start it now.
	 *
	 * @return array The job record (status "queued").
	 */
	public static function create( string $type, array $args, string $title ): array {
		$id  = substr( wp_generate_password( 16, false, false ), 0, 12 );
		$now = time();
		$job = array(
			'id'       => $id,
			'type'     => $type,
			'title'    => $title,
			'args'     => $args,
			'status'   => 'queued',
			'phase'    => 'queued',
			'progress' => 0,
			'messages' => array(),
			'result'   => null,
			'error'    => null,
			'user_id'  => get_current_user_id(),
			'created'  => $now,
			'updated'  => $now,
		);
		update_option( self::key( $id ), $job, false );

		$index   = (array) get_option( self::INDEX, array() );
		$index[] = $id;
		while ( count( $index ) > self::KEEP ) {
			$old = array_shift( $index );
			delete_option( self::key( (string) $old ) );
		}
		update_option( self::INDEX, $index, false );

		wp_schedule_single_event( $now, self::HOOK, array( $id ) );
		if ( ! defined( 'DISABLE_WP_CRON' ) || ! DISABLE_WP_CRON ) {
			spawn_cron( $now + 1 );
		}
		return $job;
	}

	public static function get( string $id ): ?array {
		$job = get_option( self::key( sanitize_key( $id ) ) );
		return is_array( $job ) ? $job : null;
	}

	/** Only the user who started a job, or an administrator, may see it. */
	public static function can_view( array $job ): bool {
		$owner = (int) ( $job['user_id'] ?? 0 );
		return ( $owner && get_current_user_id() === $owner ) || current_user_can( 'manage_options' );
	}

	/** What an AI should see: everything except the internal args. */
	public static function describe( array $job ): array {
		$out = $job;
		unset( $out['args'], $out['user_id'] );
		$out['created'] = gmdate( 'c', $job['created'] );
		$out['updated'] = gmdate( 'c', $job['updated'] );
		if ( 'running' === $job['status'] && time() - $job['updated'] > self::STALE_SECS ) {
			$out['status'] = 'stalled';
			$out['hint']   = __( 'No progress for 30 minutes; the server may have killed the process. Check the site and start again.', 'wpzoom-ai-connect' );
		}
		if ( 'queued' === $job['status'] && time() - $job['created'] > 120 ) {
			$out['hint'] = __( 'Still queued after two minutes: WP-Cron may be disabled on this site. Visiting any page of the site, or a system cron hitting wp-cron.php, starts it.', 'wpzoom-ai-connect' );
		}
		return $out;
	}

	private static function save( array $job ): void {
		$job['updated'] = time();
		update_option( self::key( $job['id'] ), $job, false );
	}

	/** Cron callback. */
	public static function run( string $id ): void {
		$job = self::get( $id );
		if ( ! $job || 'queued' !== $job['status'] ) {
			return;
		}
		if ( ! isset( self::$handlers[ $job['type'] ] ) ) {
			$job['status'] = 'failed';
			$job['error']  = sprintf( /* translators: %s: job type */ __( 'No handler for job type %s', 'wpzoom-ai-connect' ), $job['type'] );
			self::save( $job );
			return;
		}

		ignore_user_abort( true );
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, Squiz.PHP.DiscouragedFunctions.Discouraged -- long-running demo import: the cron job imports a whole starter site (content, media, widgets, settings) and must outlive max_execution_time.
		}
		wp_raise_memory_limit( 'admin' );
		if ( $job['user_id'] ) {
			wp_set_current_user( (int) $job['user_id'] );
		}

		$job['status'] = 'running';
		$job['phase']  = 'starting';
		self::save( $job );

		$progress = static function ( string $phase, string $message = '', ?int $percent = null ) use ( &$job ) {
			$job['phase'] = $phase;
			if ( null !== $percent ) {
				$job['progress'] = max( 0, min( 100, $percent ) );
			}
			if ( '' !== $message ) {
				$job['messages'][] = array( 't' => gmdate( 'H:i:s' ), 'm' => $message );
				$job['messages']   = array_slice( $job['messages'], -40 );
			}
			self::save( $job );
		};

		try {
			$result = call_user_func( self::$handlers[ $job['type'] ], $job['args'], $progress, $job );
			if ( is_wp_error( $result ) ) {
				$job['status'] = 'failed';
				$job['error']  = $result->get_error_message();
			} else {
				$job['status']   = 'done';
				$job['phase']    = 'done';
				$job['progress'] = 100;
				$job['result']   = $result;
			}
		} catch ( Throwable $e ) {
			$job['status'] = 'failed';
			$job['error']  = get_class( $e ) . ': ' . $e->getMessage();
		}
		self::save( $job );
	}
}
