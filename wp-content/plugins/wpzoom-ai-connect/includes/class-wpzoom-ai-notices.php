<?php
/**
 * Two admin notices, the wp-admin side of the onboarding emails (which only reach people who signed up for them):
 *
 * - Setup help: two days after activation, while no AI app has used the site yet. Says where setup stopped
 *   (a local site, no HTTPS, application passwords off, not connected, or connected but not added to an AI app).
 *   Dashboard and Plugins only: the AI Connect screen already shows the next step.
 * - Review: once an AI has used the site on three different days and the first of them is a week old. Dashboard,
 *   Plugins and the AI Connect screen.
 *
 * Administrators who see AI Connect only (WPZOOM_AI_Access). Each user's choices are kept in their user meta: setup help dismissed for good; the review
 * notice done for good ("Leave a review", "I already did") or snoozed for 30 days ("Maybe later", the close button).
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Notices {

	/** When the plugin was activated, or first loaded in wp-admin for sites that had it before the notices. */
	const INSTALLED_OPTION = 'wpzoom_ai_connect_installed';
	/** Per user: array( 'setup' => 1, 'review' => 'done' | snoozed-until epoch ). */
	const USER_META = 'wpzoom_ai_connect_notices';
	const ACTION    = 'wpzoom_ai_notice';

	const SETUP_AFTER   = 2 * DAY_IN_SECONDS;
	const REVIEW_DAYS   = 3;
	const REVIEW_AFTER  = 7 * DAY_IN_SECONDS;
	const REVIEW_SNOOZE = 30 * DAY_IN_SECONDS;

	const REVIEW_URL = 'https://wordpress.org/support/plugin/wpzoom-ai-connect/reviews/#new-post';
	const HELP_URL   = 'https://wordpress.org/support/plugin/wpzoom-ai-connect/';

	/** The notice picked for this screen: '' or array( 'id' => 'setup'|'review', 'variant' => string ). */
	private static $notice = '';

	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'remember_install' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'prepare' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render' ) );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
	}

	/** Sites that had the plugin before this version start the setup-help clock now, not at their old install date. */
	public static function remember_install(): void {
		if ( false === get_option( self::INSTALLED_OPTION, false ) ) {
			add_option( self::INSTALLED_OPTION, time(), '', false );
		}
	}

	/* ---------- which notice, if any ---------- */

	/** Decides the notice once the screen is known, and loads its styles only when there is one. */
	public static function prepare(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || is_network_admin() || ! WPZOOM_AI_Access::can_see() || ! function_exists( 'wp_register_ability' ) ) {
			return;
		}
		$own_page = 'toplevel_page_' . WPZOOM_AI_Admin::PAGE === $screen->id;
		if ( ! $own_page && ! in_array( $screen->id, array( 'dashboard', 'plugins' ), true ) ) {
			return;
		}
		self::$notice = self::pick( $own_page );
		if ( ! self::$notice ) {
			return;
		}
		wp_enqueue_style( 'wpzoom-ai-notices', plugins_url( 'assets/admin/notices.css', WPZOOM_AI_CONNECT_FILE ), array(), WPZOOM_AI_CONNECT_VERSION );
		wp_enqueue_script( 'wpzoom-ai-notices', plugins_url( 'assets/admin/notices.js', WPZOOM_AI_CONNECT_FILE ), array(), WPZOOM_AI_CONNECT_VERSION, true );
	}

	/** @return array{id:string, variant:string}|string */
	private static function pick( bool $own_page ) {
		$choices = self::choices();
		// The stored last-call time answers most page loads without listing the connections.
		$last        = (int) get_option( WPZOOM_AI_Scope::LAST_AI_CALL_OPTION, 0 );
		$connections = null;
		if ( ! $last ) {
			$connections = WPZOOM_AI_Admin::connections();
			$last        = WPZOOM_AI_Scope::last_ai_call( $connections );
		}

		if ( $last ) {
			$days   = WPZOOM_AI_Scope::ai_days();
			$review = $choices['review'] ?? 0;
			if ( 'done' !== $review && (int) $review <= time()
				&& $days['count'] >= self::REVIEW_DAYS && time() - $days['first'] >= self::REVIEW_AFTER ) {
				return array( 'id' => 'review', 'variant' => '' );
			}
			return '';
		}

		$installed = (int) get_option( self::INSTALLED_OPTION, time() );
		if ( $own_page || ! empty( $choices['setup'] ) || time() - $installed < self::SETUP_AFTER ) {
			return '';
		}
		return array( 'id' => 'setup', 'variant' => self::setup_variant( $connections ) );
	}

	/** Where setup stopped. Mirrors the checks on the AI Connect screen. */
	private static function setup_variant( array $connections ): string {
		if ( $connections ) {
			return 'add_app';
		}
		$home = home_url( '/' );
		if ( WPZOOM_AI_Admin::is_local( $home ) ) {
			return 'local';
		}
		if ( 0 !== strpos( $home, 'https://' ) ) {
			return 'https';
		}
		if ( ! function_exists( 'wp_is_application_passwords_available' ) || ! wp_is_application_passwords_available() ) {
			return 'app_passwords';
		}
		return 'connect';
	}

	private static function choices(): array {
		$saved = get_user_meta( get_current_user_id(), self::USER_META, true );
		return is_array( $saved ) ? $saved : array();
	}

	/* ---------- the notices ---------- */

	/** A link that records the choice (admin-post), then comes back here or goes on to wordpress.org. */
	private static function action_url( string $notice, string $choice ): string {
		return wp_nonce_url(
			add_query_arg( array( 'action' => self::ACTION, 'notice' => $notice, 'choice' => $choice ), admin_url( 'admin-post.php' ) ),
			self::ACTION
		);
	}

	public static function render(): void {
		if ( ! is_array( self::$notice ) ) {
			return;
		}
		if ( 'review' === self::$notice['id'] ) {
			self::box(
				'review',
				self::action_url( 'review', 'later' ),
				__( 'Enjoying WPZOOM AI Connect?', 'wpzoom-ai-connect' ),
				__( 'You have been talking to this site from your AI app for a while now. The plugin is new on WordPress.org, and a few honest words from someone who actually uses it help other site owners find it. A sentence or two is plenty.', 'wpzoom-ai-connect' ),
				array(
					array( __( 'Leave a review', 'wpzoom-ai-connect' ), self::action_url( 'review', 'review' ), 'primary', true ),
					array( __( 'Maybe later', 'wpzoom-ai-connect' ), self::action_url( 'review', 'later' ), '', false ),
					array( __( 'I already did', 'wpzoom-ai-connect' ), self::action_url( 'review', 'done' ), 'link', false ),
				),
				__( 'Something not working? Tell us first', 'wpzoom-ai-connect' )
			);
			return;
		}

		$page   = WPZOOM_AI_Admin::tab_url( 'start' );
		$finish = array( __( 'Finish setup', 'wpzoom-ai-connect' ), $page, 'primary', false );
		$video  = array( __( 'Watch how it works', 'wpzoom-ai-connect' ), WPZOOM_AI_Admin::VIDEO_URL, '', 'external' );
		switch ( self::$notice['variant'] ) {
			case 'local':
				$text    = sprintf(
					/* translators: %s: the site's address, e.g. mysite.local */
					__( 'This site runs on your own computer (%s), so Claude and ChatGPT cannot reach it: they talk to your site over the internet, through WPZOOM. Install AI Connect on a live site to use it.', 'wpzoom-ai-connect' ),
					(string) wp_parse_url( home_url(), PHP_URL_HOST )
				);
				$actions = array( $video );
				break;
			case 'https':
				$text    = __( 'Your site is not served over HTTPS yet, so AI apps cannot connect to it safely. Most hosts turn on SSL for free: ask yours, then connect the site from the AI Connect screen.', 'wpzoom-ai-connect' );
				$actions = array( array( __( 'Open AI Connect', 'wpzoom-ai-connect' ), $page, 'primary', false ) );
				break;
			case 'app_passwords':
				$text    = __( 'Application passwords are turned off on this site, so your AI app cannot sign in. A security plugin usually does this. Allow them, then connect the site from the AI Connect screen.', 'wpzoom-ai-connect' );
				$actions = array( array( __( 'Open AI Connect', 'wpzoom-ai-connect' ), $page, 'primary', false ) );
				break;
			case 'add_app':
				$text    = __( 'This site is connected, but no AI app has used it yet. The last step is adding WPZOOM to Claude or ChatGPT, and it is the one people miss most. It takes a minute and you do it once.', 'wpzoom-ai-connect' );
				$actions = array( array( __( 'Show me how', 'wpzoom-ai-connect' ), WPZOOM_AI_Admin::tab_url( 'start', 'wzai-guide' ), 'primary', false ), $video );
				break;
			default:
				$text    = __( 'You installed AI Connect but have not connected this site yet. It takes about two minutes: connect the site, then add WPZOOM to Claude or ChatGPT. After that you can ask your AI to change colors, add pages or check your site.', 'wpzoom-ai-connect' );
				$actions = array( $finish, $video );
		}
		self::box(
			'setup',
			self::action_url( 'setup', 'dismiss' ),
			__( 'Need a hand with WPZOOM AI Connect?', 'wpzoom-ai-connect' ),
			$text,
			$actions,
			__( 'Stuck? Ask us for help', 'wpzoom-ai-connect' )
		);
	}

	/**
	 * One notice. $actions: list of [label, url, style ('primary', '' for a plain button, 'link'), opens elsewhere?].
	 * An action that opens elsewhere and records a choice (the review link) is a new tab through admin-post.
	 */
	private static function box( string $id, string $close_url, string $title, string $text, array $actions, string $help ): void {
		?>
		<div class="notice notice-info is-dismissible wzai-admin-notice" data-wzai-notice="<?php echo esc_attr( $id ); ?>" data-wzai-close="<?php echo esc_url( $close_url ); ?>">
			<img class="wzai-admin-notice__mark" src="<?php echo esc_url( plugins_url( 'assets/admin/wpzoom-ai-icon.svg', WPZOOM_AI_CONNECT_FILE ) ); ?>" alt="" width="40" height="40">
			<div class="wzai-admin-notice__body">
				<p class="wzai-admin-notice__title"><strong><?php echo esc_html( $title ); ?></strong></p>
				<p><?php echo esc_html( $text ); ?></p>
				<p class="wzai-admin-notice__actions">
					<?php foreach ( $actions as $action ) : ?>
						<?php
						$class = 'link' === $action[2] ? 'wzai-admin-notice__link' : ( 'primary' === $action[2] ? 'button button-primary' : 'button' );
						$new   = ! empty( $action[3] );
						?>
						<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $action[1] ); ?>"<?php echo $new ? ' target="_blank" rel="noopener"' : ''; ?><?php echo false !== strpos( $action[1], 'admin-post.php' ) ? ' data-wzai-choice' : ''; ?>><?php echo esc_html( $action[0] ); ?></a>
					<?php endforeach; ?>
					<a class="wzai-admin-notice__link wzai-admin-notice__help" href="<?php echo esc_url( self::HELP_URL ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $help ); ?></a>
				</p>
			</div>
		</div>
		<?php
	}

	/* ---------- choices ---------- */

	/** Records a choice, then returns to the screen (or sends "Leave a review" on to wordpress.org). */
	public static function handle(): void {
		if ( ! WPZOOM_AI_Access::can_see() ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'wpzoom-ai-connect' ), 403 );
		}
		check_admin_referer( self::ACTION );
		$notice  = isset( $_GET['notice'] ) ? sanitize_key( wp_unslash( $_GET['notice'] ) ) : '';
		$choice  = isset( $_GET['choice'] ) ? sanitize_key( wp_unslash( $_GET['choice'] ) ) : '';
		$choices = self::choices();
		if ( 'setup' === $notice ) {
			$choices['setup'] = 1;
		} elseif ( 'review' === $notice ) {
			$choices['review'] = 'later' === $choice ? time() + self::REVIEW_SNOOZE : 'done';
		}
		update_user_meta( get_current_user_id(), self::USER_META, $choices );

		if ( isset( $_GET['js'] ) ) { // the notice's script already hid it
			status_header( 204 );
			exit;
		}
		if ( 'review' === $notice && 'review' === $choice ) {
			add_filter( 'allowed_redirect_hosts', array( __CLASS__, 'allow_wordpress_org' ) );
			wp_safe_redirect( self::REVIEW_URL );
			exit;
		}
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
		exit;
	}

	public static function allow_wordpress_org( array $hosts ): array {
		$hosts[] = 'wordpress.org';
		return $hosts;
	}
}
