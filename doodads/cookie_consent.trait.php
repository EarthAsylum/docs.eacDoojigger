<?php
namespace EarthAsylumConsulting\Traits;

/**
 * cookie_consent trait - Advanced set cookies using WP Consent API
 *
 * @category	WordPress Plugin
 * @package		{eac}Doojigger\Traits
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2024 EarthAsylum Consulting <www.EarthAsylum.com>
 * @version		24.1108.1
 */
trait cookie_consent
{
	/**
	 * @var bool has cookie consent been loaded?
	 */
	private static $cookie_consent_loaded = false;

	/**
	 * @var string default consent service name (set by using class)
	 */
	public $cookie_default_service = '';


	/**
	 * initialize cookie consent (on _plugin_startup)
	 *
	 * @example $this->cookie_consent_init( $this->pluginHeader( 'PluginSlug' ) );
	 *
	 * @param string 		$plugin_slug plugin/slugname (plugin_basename)
	 * @param string 		$default_service plugin or service name
	 *
	 * @return void
	 */
	private function cookie_consent_init( string $plugin_slug, string $default_service = '' ): void
	{
		if ( class_exists( '\WP_CONSENT_API' ) )
		{
			$this->cookie_default_service 	= $default_service ?: dirname($plugin_slug);

			// WP_CONSENT_API waits for plugins_loaded to instantiate (?)
			// which may be after this plugin loads.
			\WP_CONSENT_API::get_instance();

			// declare compliance with WP Consent API
			if ($plugin_slug) {
				add_filter( "wp_consent_api_registered_{$plugin_slug}", '__return_true' );
			}

			// do only once
			if (! self::$cookie_consent_loaded)
			{
				self::$cookie_consent_loaded = true;

				// add 'necessary' consent category, always allowed
				add_filter('wp_consent_categories', function($consent) {
					return array_merge(['necessary'],$consent);
				});
				// 'necessary' is always allowed
				add_filter('wp_has_consent', function($has_consent, $category, $requested_by) {
					return ($category == 'necessary') ? true : $has_consent;
				},5,3);

				// add javascript and filter for consent type (optin/optout)
				add_action( 'wp_enqueue_scripts', array( $this, 'cookie_consent_patch' ),PHP_INT_MAX);
			}
		}
	}


	/**
	 * enqueue javascripts to patch consent interface between browser and server
	 *
	 * @return	void
	 */
	public function cookie_consent_patch(): void
	{
		// WP Consent API doesn't pass client consent type to the server,
		// use a cookie on change (provided the CMP fires 'wp_consent_type_defined' event).
		wp_add_inline_script( 'wp-consent-api',
			"document.addEventListener('wp_consent_type_defined',function(){".
				"consent_api_set_cookie(consent_api.cookie_prefix+'_consent_type',window.wp_consent_type);".
			"});"
		);

		// some consent management platforms may not set wp_get_consent_type
		add_filter('wp_get_consent_type',function($type)
			{
				if ((empty($type))) {	// has not (yet) been set
					$prefix	= \WP_CONSENT_API::$config->consent_cookie_prefix();
					return $this->get_cookie("{$prefix}_consent_type",'optout');
				}
				return $type;
			},
			PHP_INT_MAX - 100
		);
	}


	/**
	 * set a cookie supporting wp_consent if enabled
	 *
	 * @param string|array	$name the cookie name
	 * @param mixed			$value the cookie value
	 * @param string|int	$expires cookie expiration in seconds or timestamp or string
	 * 						'delete', 'expired', 'session', 'n days', 'n months', etc.
	 * @param array 		$options cookie parameters
	 * 						'path', 'domain', 'secure', 'httponly', 'samesite'.
	 * @param mixed 		$consent (array) consent parameters or (string) category or true = already registered
	 *						'plugin_or_service', 'category', 'function', ...
	 * @return bool 		success or failure (as best we can tell)
	 */
	public function set_cookie(string|array $name, $value, string|int $expires=0, array $options=[], $consent=[]): bool
	{
		if (is_array($name)) $name = $name[0];

		$name	= sanitize_key($name);

		if ( is_array( $value ) || is_object( $value ) ) {
			$value 	= serialize( $value );
		} else {
			$value 	= sanitize_text_field($value);
		}

 		$using_consent = self::$cookie_consent_loaded;
		if ($consent === true) {
			$consent = ($using_consent) ? $this->get_cookie_consent($name) : [];
		}
 		$using_consent = ($using_consent && !empty($consent));

		list($expInt,$expStr) = $this->set_cookie_expiration($expires,$using_consent);

		$options = apply_filters( 'wp_setcookie_options', wp_parse_args(
			$options,
			[
				'expires'	=> $expInt,
				'path'		=> COOKIEPATH,
				'domain'	=> COOKIE_DOMAIN,
				'secure'	=> is_ssl(),
				'httponly'	=> true,
				'samesite'	=> 'lax',
			]),
			$name,
			$value
		);

		unset($_COOKIE[$name]);

		if ( $using_consent )
		{
			if (! $consent['category'] = wp_validate_consent_category(
				apply_filters( 'wp_setcookie_category',$consent['category'],$name,$value )
			)) {
				_doing_it_wrong(__FUNCTION__, __("Missing/invalid consent category."), '2.7.0');
				return false;
			}

			$consent = $this->set_cookie_consent($name,$consent,true,[
				'expires'	=> $expStr,
			]);

			if (! wp_has_consent($consent['category'])) return false;
		}

		foreach ($options as $n => $v)
		{
			$options[$n] = apply_filters( "wp_setcookie_{$n}", $v, $name, $value );
		}

		if (setcookie($name,$value,$options))
		{
			//	echo "<div class='notice'><pre>".__METHOD__." ".var_export([$name,$value,$options,$consent],true)."</pre></div>";
			if ($options['expires'] == 0 || $options['expires'] > time()) {
				$_COOKIE[$name] = $value;
				do_action( 'wp_setcookie_success',$name,$value,$options,$consent );
			}
			return true;
		}

		return false;
	}


	/**
	 * set a cookie expiration as integer and string
	 *
	 * @param string|int	$expires cookie expiration in seconds or timestamp or string
	 * 						'delete', 'expired', 'session', 'n days', 'n months', etc.
	 * @param bool			$getString (false) return array when true
	 * @return array 		[ $expInt, $expStr ]
	 */
	public function set_cookie_expiration($expires, bool $getString = true): array
	{
		$expInt = 0; $expStr = '';

		$utc = new \DateTimeZone('utc');
		$now = new \DateTime('now',$utc);
		$time_now = $now->getTimestamp();

		// get expires as integer

		if (is_string($expires)) {							// string, i.e. '30 days' or 'session'
			switch (strtolower($expires)) {
				case 'session': $expInt = 0; break;
				case 'delete': 	$expires = 'expired'; #nobreak
				case 'expired':	$expInt = $time_now - DAY_IN_SECONDS; break;
				default: $expInt = ( new \DateTime($expires,$utc) )->getTimestamp();
			}
		} else if ($expires !== 0 && $expires < $time_now) {// int seconds from/before now
			$expInt = intval($time_now + $expires);
		} else {											// timestamp or 0
			$expInt = intval($expires);
		}

		// get expires as string

		if ($getString)
		{
			if (is_string($expires)) {						// string, i.e. '30 days'
				$expStr = strtolower($expires);
			} else if ($expInt == 0) {						// session
				$expStr = 'session';
			} else if ($expInt < $time_now) {				// delete/expired
				$expStr = 'expired';
			} else {										// convert timestamp to days
				$exp = new \DateTime('now',$utc);
				$exp->setTimestamp($expInt + 60);			// offset for diff()
				$exp = $now->diff($exp);
				foreach (['y'=>'year','m'=>'month','d'=>'day','h'=>'hour','i'=>'minute'] as $x=>$period) {
					if ($x = $exp->{$x}) {
						$expStr .= ($x > 1) ? "{$x} {$period}s " : "{$x} {$period} ";
					}
				}
				$expStr = trim($expStr) ?: 'session';
			}
		}

		return [$expInt,$expStr];
	}


	/**
	 * set a cookie's consent information
	 *
	 * @param string		$name the cookie name
	 * @param array|string 	$consent consent parameters or category
	 *						'plugin_or_service', 'category', 'function', ...
	 * @param bool			$register with wp_add_cookie_info()
	 * @param array		 	$defaults default consent parameters
	 * @return array 		$consent
	 */
	public function set_cookie_consent(string $name, $consent, bool $register = true, array $defaults = []): array
	{
		if (is_string($consent)) $consent = ['category' => $consent];
		$consent = apply_filters( 'wp_setcookie_consent', array_merge(
				[
					'plugin_or_service'			=> $this->cookie_default_service,
					'category'					=> '', 		// necessary, functional, preferences, statistics, statistics-anonymous, marketing
					'expires'					=> 0,
					'function'					=> '',		// describe cookie functionality
					'collectedPersonalData' 	=> '',		// describe personal data collected
					'memberCookie' 				=> false,
					'administratorCookie' 		=> false,
					'type' 						=> 'HTTP',
					'domain' 					=> '',
				],
				$defaults,
				$consent
			),
			$name
		);

		if ($register && !empty($consent['function']) && self::$cookie_consent_loaded)
		{
			// maybe replace placeholders with cookie array values
			$consent['function'] = sprintf($consent['function'],
				$consent['plugin_or_service'],
				$consent['category'],
				$consent['type'],
			);
			wp_add_cookie_info( $name, ...array_values($consent) );
		}

		return $consent;
	}


	/**
	 * get a registered cookie consent array for a single cookie or all cookies
	 *
	 * @param string		$name the cookie name (optional)
	 * @return array	 	$consent or array of [name => $consent]
	 */
	public function get_cookie_consent($name = false): array
	{
		$consent = (self::$cookie_consent_loaded) ? wp_get_cookie_info($name) : false;
		// because wp_get_cookie_info may return all cookies even when we ask for only one
		if ($consent && $name) {
			if (! isset($consent['plugin_or_service'])) return [];
		}
		return (is_array($consent)) ? $consent : [];
	}


	/**
	 * get a cookie (convenience method, since we have set_cookie)
	 *
	 * @param string|array	$name the cookie name (and alternates)
	 * @param string		$default if cookie not set
	 * @return string 		cookie value
	 */
	public function get_cookie(string|array $name, $default = null)
	{
		if (!is_array($name)) $name = array($name);

		foreach ($name as $key) {
			if (isset($_COOKIE[$key])) {
				$default = maybe_unserialize($_COOKIE[$key]);
				break;
			}
			if (isset($_COOKIE[sanitize_key($key)])) {
				$default = maybe_unserialize($_COOKIE[sanitize_key($key)]);
				break;
			}
		}

		return is_string($default) ? sanitize_text_field($default) : $default;
	}


	/**
	 * check consent is loaded and category set (convenience method)
	 *
	 * @param string		$category consent category to check.
	 * @return	bool
	 */
	public function has_cookie_consent(string $category = null): bool
	{
		if (is_null($category)) {
			return self::$cookie_consent_loaded;
		}
		return (self::$cookie_consent_loaded) ? wp_has_consent($category) : true;
	}
}
