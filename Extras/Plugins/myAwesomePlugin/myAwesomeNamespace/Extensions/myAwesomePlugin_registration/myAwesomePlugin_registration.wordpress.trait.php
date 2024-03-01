<?php
namespace myAwesomeNamespace\Traits;

/**
 * EarthAsylum Consulting {eac} Software Registration - software registration API trait for wordpress transients
 *
 * uses common code from myAwesomePlugin_registration_interface trait
 *
 * @category	WordPress Plugin
 * @package		{eac}SoftwareRegistry
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2021 EarthAsylum Consulting <www.EarthAsylum.com>
 * @version		1.x
 */

trait myAwesomePlugin_registration_wordpress
{
	use myAwesomePlugin_registration_interface;


	/**
	 * add WordPress registry hooks
	 *
	 * @return bool
	 */
	public function addSoftwareRegistryHooks()
	{
		/**
		 * action {productid}_registry_refresh to run when refreshing registration
		 * @see scheduleRegistryRefresh()
		 * @return	void
		 */
		\add_action( self::SOFTWARE_REGISTRY_PRODUCTID.'_registry_refresh', 	array($this, 'refreshRegistration') );

		/**
		 * filter {productid}_is_registered check for valid registration
		 * @return	bool
		 */
		\add_filter( self::SOFTWARE_REGISTRY_PRODUCTID.'_is_registered', 		array($this, 'isValidRegistration') );

		/**
		 * filter {productid}_registration_key get the current registration key
		 * @return	string|bool
		 */
		\add_filter( self::SOFTWARE_REGISTRY_PRODUCTID.'_registration_key', 	array($this, 'getRegistrationKey') );

		/**
		 * filter {productid}_registration get the current registration
		 * @return	object|bool
		 */
		\add_filter( self::SOFTWARE_REGISTRY_PRODUCTID.'_registration', 		array($this, 'getCurrentRegistration') );

		/**
		 * filter {productid}_registry_value get or check the value of a registry key
		 * @return	object|bool
		 */
		\add_filter( self::SOFTWARE_REGISTRY_PRODUCTID.'_registry_value',
			function($default,$keyName,$value=null,$compare='=')
			{
				$value = $this->isRegistryValue($keyName,$value,$compare);
				return $value ?: $default;
			},
		10, 4);
	}


	/**
	 * get the next refresh event
	 *
	 * @return object|bool scheduled event or false
	 */
	public function nextRegistryRefreshEvent(string $registrationKey=null)
	{
		if ($registrationKey = $this->getRegistrationKey($registrationKey))
		{
			if (!$this->interfaceIsMainSite())
			{
				switch_to_blog( get_main_site_id() );
			}

			$event = wp_get_scheduled_event( self::SOFTWARE_REGISTRY_REFRESH, [$registrationKey] );

			if (function_exists('\restore_current_blog'))
			{
				\restore_current_blog();
			}

			return $event;
		}
		return false;
	}


	/**
	 * check/verify the next refresh event
	 *
	 * @return void
	 */
	public function checkRegistryRefreshEvent(string $registrationKey=null)
	{
		if (!defined('REST_REQUEST') && !defined('XMLRPC_REQUEST'))
		{
			if ($registrationKey = $this->getRegistrationKey($registrationKey))
			{
				if (! $this->nextRegistryRefreshEvent($registrationKey))
				{
					$this->refreshRegistration($registrationKey);
				}
			}
		}
	}


	/**
	 * schedule the next refresh event, forces refresh from registration server
	 *
	 * in __construct() or init code, add...
	 * add_action( 'myAwesomePlugin_registry_refresh', array($this, 'refreshRegistration') ); // passes registry_key
	 * @see addSoftwareRegistryHooks()
	 *
	 * @param int $secondsFromNow time in seconds in the future
	 * @param string $schedule hourly, daily, twicedaily, weekly
	 * @param object $registration (registry_*) values
	 * @return bool
	 */
	public function scheduleRegistryRefresh(int $secondsFromNow, string $schedule, $registration)
	{
		if (!$this->interfaceIsMainSite())
		{
			switch_to_blog( get_main_site_id() );
		}

		$eventName = self::SOFTWARE_REGISTRY_REFRESH;
		if (wp_next_scheduled( $eventName, [$registration->registry_key] ) !== false)
		{
			wp_unschedule_hook($eventName);
			//wp_clear_scheduled_hook( $eventName, [$registration->registry_key] );
		}
		//wp_schedule_event( time()+($secondsFromNow), $schedule, $eventName, [$registration->registry_key] );
		wp_schedule_single_event( time()+($secondsFromNow), $eventName, [$registration->registry_key] );

		if (function_exists('\restore_current_blog'))
		{
			\restore_current_blog();
		}
	}


	/**
	 * get the current registration key
	 *
	 * @param string registration key
	 * @return string
	 */
	public function getRegistrationKey(string $registrationKey=null)
	{
		if (!empty($registrationKey))
		{
			return $registrationKey;
		}
		if ($registrationKey = ($this->interfaceIsNetworkEnabled())
				? \get_site_option(self::SOFTWARE_REGISTRY_OPTION)
				: \get_option(self::SOFTWARE_REGISTRY_OPTION)
			)
		{
			return $registrationKey;
		}
		return null;
	}


	/**
	 * get registration cache
	 *
	 * @return	array
	 */
	public function getRegistrationCache()
	{
		return ($this->interfaceIsNetworkEnabled())
				? \get_site_transient(self::SOFTWARE_REGISTRY_TRANSIENT)
				: \get_transient(self::SOFTWARE_REGISTRY_TRANSIENT);
	}


	/**
	 * set registration cache
	 *
	 * @param object $registration
	 * @return	void
	 */
	public function setRegistrationCache($registration)
	{
		if ($this->interfaceIsNetworkEnabled())
		{
			// save the registration key
			\update_site_option(self::SOFTWARE_REGISTRY_OPTION,$registration->registration->registry_key);
			// save the registration data
			\set_site_transient(self::SOFTWARE_REGISTRY_TRANSIENT,$registration,$registration->registrar->cacheTime);
		}
		else
		{
			// save the registration key
			\update_option(self::SOFTWARE_REGISTRY_OPTION,$registration->registration->registry_key);
			// save the registration data
			\set_transient(self::SOFTWARE_REGISTRY_TRANSIENT,$registration,$registration->registrar->cacheTime);
		}

		/**
		 * action {productid}_update_registration
		 * @param array $registration registration object
		 */
		\do_action(self::SOFTWARE_REGISTRY_PRODUCTID.'_update_registration', $registration);
	}


	/**
	 * purge registration cache
	 *
	 * @return	void
	 */
	public function purgeRegistrationCache()
	{
		if ($this->interfaceIsNetworkEnabled())
		{
			// delete the registration key
			\delete_site_option(self::SOFTWARE_REGISTRY_OPTION);
			// delete the registration data
			\delete_site_transient(self::SOFTWARE_REGISTRY_TRANSIENT);
		}
		else
		{
			// delete the registration key
			\delete_option(self::SOFTWARE_REGISTRY_OPTION);
			// delete the registration data
			\delete_transient(self::SOFTWARE_REGISTRY_TRANSIENT);
		}

		/**
		 * action {productid}_purge_registration
		 */
		\do_action(self::SOFTWARE_REGISTRY_PRODUCTID.'_purge_registration');
	}


	/**
	 * API remote request - remote http request (wp_remote_request or curl)
	 *
	 * @param	string	$endpoint create, activate, deactivate, verify
	 * @param	string	$remoteUrl remote Url
	 * @param	array 	$request api request
	 * @return	object api response (decoded)
	 */
	public function api_remote_request($endpoint,$remoteUrl,$request)
	{
		/**
		 * filter {productid}_api_remote_request
		 * @param	array $request ['method'=>, 'timeout'=>, 'redirection'=>, 'sslverify'=>, 'headers'=>, 'body'=>]
		 * @param	string $endpoint create, activate, deactivate, verify, refresh, revise
		 * @return	array request body
		 */
		$request = \apply_filters(self::SOFTWARE_REGISTRY_PRODUCTID.'_api_remote_request', $request, $endpoint);

		$result = wp_remote_request($remoteUrl,$request);
		$body 	= json_decode(wp_remote_retrieve_body($result));
		if (!empty($body) && isset($body->code) && isset($body->message)) {
			$result = new \wp_error($body->code,$body->message,$body->data);
		}

		if (is_wp_error($result))
		{
			$code 	= $result->get_error_data() ?: [];
			$code	= $code->status ?? $result->get_error_code();
			$msg 	= $result->get_error_message();
			$error 	= json_decode('{"status":{"code":"'.$code.'","message":"'.addslashes($msg).'"},'.
								 '"error":{"code":"'.$code.'","message":"'.addslashes($msg).'"}}');

			/**
			 * action {productid}_api_remote_error
			 * @param	object {status:{}, error: {}}
			 * @param	array $request ['method'=>, 'timeout'=>, 'redirection'=>, 'sslverify'=>, 'headers'=>, 'body'=>]
			 * @param	string $endpoint create, activate, deactivate, verify, refresh, revise
			 */
			\do_action(self::SOFTWARE_REGISTRY_PRODUCTID.'_api_remote_error', $error, $request, $endpoint);

			return $error;
		}

		$result = $body;

		/**
		 * action {productid}_api_remote_response
		 * @param	array $body response body
		 * @param	array $request ['method'=>, 'timeout'=>, 'redirection'=>, 'sslverify'=>, 'headers'=>, 'body'=>]
		 * @param	string $endpoint create, activate, deactivate, verify, refresh, revise
		 */
		\do_action(self::SOFTWARE_REGISTRY_PRODUCTID.'_api_remote_response', $body, $request, $endpoint);
		/**
		 * action {productid}_api_remote_$endpoint
		 * @param	array $body response body
		 * @param	array $request ['method'=>, 'timeout'=>, 'redirection'=>, 'sslverify'=>, 'headers'=>, 'body'=>]
		 * @param	string $endpoint create, activate, deactivate, verify, refresh, revise
		 */
		\do_action(self::SOFTWARE_REGISTRY_PRODUCTID.'_api_remote_'.$endpoint, $body, $request, $endpoint);

		return $body;
	}


	/**
	 * is API error
	 *
	 * @param	string	$apiResponse
	 * @return	bool
	 */
	public function is_api_error($apiResponse)
	{
		return ($apiResponse->status->code != '200');
	}


	/**
	 * main_site() when network-enabled
	 *
	 * @return	bool
	 */
	private function interfaceIsMainSite(): bool
	{
		static $is_main_site = null;
		if (is_null($is_main_site))
		{
			if ( ! $this->interfaceIsNetworkEnabled() )
			{
				$is_main_site = true;
			}
			else
			{
				$is_main_site = is_main_site();
			}
    	}
		return $is_main_site;
	}


	/**
	 * is plugin network-enabled
	 *
	 * @return	bool
	 */
	private function interfaceIsNetworkEnabled(): bool
	{
		static $is_network_enabled = null;
		if (is_null($is_network_enabled))
		{
			if ( ! is_multisite() )
			{
				$is_network_enabled = false;
			}
			else
			{
				$slug = explode('/', str_replace(WP_PLUGIN_DIR . '/', '', __DIR__))[0];
				$slug = "{$slug}/".self::SOFTWARE_REGISTRY_PRODUCTID.".php"; // this could be wrong
				$plugins = \get_site_option( 'active_sitewide_plugins' );
				$is_network_enabled =  (isset( $plugins[ $slug ] ));
    		}
    	}
		return $is_network_enabled;
	}
}
?>
