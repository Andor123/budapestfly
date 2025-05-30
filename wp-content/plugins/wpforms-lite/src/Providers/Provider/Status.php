<?php

namespace WPForms\Providers\Provider;

use stdClass;

/**
 * Class Status gives ability to check/work with provider statuses.
 * Might be used later to track Provider errors on data-delivery.
 *
 * @since 1.4.8
 */
class Status {

	/**
	 * Provider identifier, its slug.
	 *
	 * @since 1.4.8
	 *
	 * @var string
	 */
	private $provider;

	/**
	 * Form data and settings.
	 *
	 * @since 1.4.8
	 *
	 * @var array
	 */
	protected $form_data = [];

	/**
	 * Status constructor.
	 *
	 * @since 1.4.8
	 *
	 * @param string $provider Provider slug.
	 */
	public function __construct( $provider ) {

		$this->provider = sanitize_key( (string) $provider );
	}

	/**
	 * Provide an ability to statically init the object.
	 * Useful for inline-invocations.
	 *
	 * @example: Status::init( 'drip' )->is_ready();
	 *
	 * @since 1.4.8
	 * @since 1.5.9 Added a check on provider.
	 *
	 * @param string $provider Provider slug.
	 *
	 * @return Status
	 */
	public static function init( $provider ) {

		static $instance;

		if ( ! $instance || $provider !== $instance->provider ) {
			$instance = new self( $provider );
		}

		return $instance;
	}

	/**
	 * Check whether the defined provider is configured or not.
	 * "Configured" means has an account that might be checked/updated on Settings > Integrations.
	 *
	 * @since 1.4.8
	 *
	 * @return bool
	 */
	public function is_configured() {

		$options = wpforms_get_providers_options();

		/**
		 * Use this filter to change the configuration status of the provider.
		 * We need the filter for BC reasons.
		 *
		 * @since 1.4.8
		 *
		 * @param bool $is_configured Is the provider configured?
		 */
		$is_configured = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			"wpforms_providers_{$this->provider}_configured",
			! empty( $options[ $this->provider ] )
		);

		/**
		 * Use this filter to change the configuration status of the provider.
		 *
		 * @since 1.4.8
		 *
		 * @param bool   $is_configured Is the provider configured?
		 * @param string $provider      Provider slug.
		 */
		return apply_filters( 'wpforms_providers_status_is_configured', $is_configured, $this->provider ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Check whether the defined provider is connected to some form.
	 * "Connected" means it has a Connection in Form Builder > Providers > Provider tab.
	 *
	 * @since 1.4.8
	 *
	 * @param int $form_id Form ID to check the status against.
	 *
	 * @return bool
	 */
	public function is_connected( $form_id ) {

		$is_connected = false;

		$revisions = wpforms()->obj( 'revisions' );
		$revision  = $revisions ? $revisions->get_revision() : null;

		if ( $revision ) {
			$this->form_data = wpforms_decode( $revision->post_content );
		} else {
			$this->form_data = wpforms()->obj( 'form' )->get( (int) $form_id, [ 'content_only' => true ] );
		}

		if ( ! empty( $this->form_data['providers'][ $this->provider ] ) ) {
			$is_connected = $this->check_valid_connections();
		}

		/**
		 * Use this filter to change the connection status of the provider.
		 *
		 * @since 1.4.8
		 *
		 * @param bool   $is_connected Is the provider connected to the form?
		 * @param string $provider     Provider slug.
		 */
		return (bool) apply_filters( 'wpforms_providers_status_is_connected', $is_connected, $this->provider ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Is the current provider ready to be used?
	 * It means both configured and connected.
	 *
	 * @since 1.4.8
	 *
	 * @param int $form_id Form ID to check the status against.
	 *
	 * @return bool
	 */
	public function is_ready( $form_id ) {

		return $this->is_configured() && $this->is_connected( $form_id );
	}

	/**
	 * Check if connections belong to an existing account.
	 *
	 * @since 1.8.8
	 *
	 * @return bool
	 */
	private function check_valid_connections(): bool {

		$account_ids = array_keys( wpforms_get_providers_options( $this->provider ) );

		// BC for the Salesforce addon that uses `resource_owner_id` key instead of `account_id` value.
		if ( $this->provider === 'salesforce' ) {
			$account_ids = array_column( wpforms_get_providers_options( 'salesforce' ), 'resource_owner_id' );
		}

		// Account id is generated by the `uniqid` function that sometimes returns an integer value.
		$account_ids = array_map( 'strval', $account_ids );

		$connection_accounts_ids = array_column( $this->form_data['providers'][ $this->provider ], 'account_id' );

		// BC for the Drip addon that uses `option_id` key for storing a connection provider.
		if ( $this->provider === 'drip' ) {
			$connection_accounts_ids = array_column( $this->form_data['providers'][ $this->provider ], 'option_id' );
		}

		foreach ( $connection_accounts_ids as $account ) {
			if ( in_array( (string) $account, $account_ids, true ) ) {
				return true;
			}
		}

		return false;
	}
}
