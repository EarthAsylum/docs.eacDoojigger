<?php
namespace myAwesomeNamespace\Traits;

/**
 * EarthAsylum Consulting {eac} Software Registration - software registration API interface trait
 *
 * implements common code for myAwesomePlugin_registration interface
 *
 * @category	WordPress Plugin
 * @package		{eac}SoftwareRegistry
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2021 EarthAsylum Consulting <www.EarthAsylum.com>
 * @version		1.x
 */

trait myAwesomePlugin_registration_interface
{
	/**
	 * get the registration server's api create key
	 *
	 * @return string
	 */
	public function getApiCreateKey()
	{
		return self::SOFTWARE_REGISTRY_CREATE_KEY;
	}


	/**
	 * get the registration server's api update key
	 *
	 * @return string
	 */
	public function getApiUpdateKey()
	{
		return self::SOFTWARE_REGISTRY_UPDATE_KEY;
	}


	/**
	 * get the registration server's api read key
	 *
	 * @return string
	 */
	public function getApiReadKey()
	{
		return self::SOFTWARE_REGISTRY_READ_KEY;
	}


	/**
	 * get the registration server's api endpoint URL
	 *
	 * @param string $endpoint one of 'create', 'activate', 'deactivate', 'verify'
	 * @return string
	 */
	public function getApiEndPoint(string $endpoint = null)
	{
		return rtrim(self::SOFTWARE_REGISTRY_HOST_URL.'/'.$endpoint, '/');
	}


	/**
	 * add registry hooks
	 *
	 * @return void
	 */
	abstract function addSoftwareRegistryHooks();


	/**
	 * get the next refresh event
	 *
	 * @return object|bool scheduled event or false
	 */
	abstract function nextRegistryRefreshEvent(string $registrationKey=null);


	/**
	 * check/verify the next refresh event
	 *
	 * @return bool
	 */
	abstract function checkRegistryRefreshEvent(string $registrationKey=null);


	/**
	 * schedule the next refresh event, forces refresh from registration server
	 *
	 * @param int $secondsFromNow time in seconds in the future
	 * @param string $schedule hourly, daily, twicedaily, weekly
	 * @param array $registration (registry_*) values
	 * @return bool
	 */
	abstract public function scheduleRegistryRefresh(int $secondsFromNow, string $schedule, $registration);


	/**
	 * is the current registry information valid
	 *
	 * @param string registration key
	 * @return bool
	 */
	public function isValidRegistration(string $registrationKey=null)
	{
		static $isValid = null;

		if (is_null($isValid))
		{
			$currentRegistry = $this->getCurrentRegistration($registrationKey);
			if (!$currentRegistry) return false;

			$registry = $currentRegistry->registration;
			if ($registry->registry_valid !== true) {
				$isValid = false;
			} else if ($registry->registry_status == 'invalid') {
				$isValid = false;
			} else if (strtotime($registry->registry_expires.' 23:59:59 UTC') < time()) {
				$isValid = false;
			} else if (strtotime($registry->registry_effective.' 00:00:00 UTC') > time()) {
				$isValid = false;
			} else {
				$isValid = true;
			}
		}
		return $isValid;
	}


	/**
	 * get or check the value of a registry key
	 *
	 * @example $this->isRegistryValue('license');
	 * @example $this->isRegistryValue('license', 'L3', 'ge');
	 *
	 * @param string $keyName the key name (sans prefix) of the registry value
	 * @param string $value the value to compare
	 * @param string $compare the comparison to make (=,<,>,<=,>=)
	 * @return bool|mixed
	 */
	public function isRegistryValue(string $keyName, $value=null, string $compare='=')
	{
		if (! $this->isValidRegistration()) return false;

		$registry 	= $this->getCurrentRegistration()->registration;
		$keyName 	= (strpos($keyName, 'registry_') !== false) ? strtolower($keyName) : strtolower("registry_{$keyName}");
		$keyValue 	= $registry->{$keyName} ?? null;
		if (is_null($value)) return $keyValue;
		switch (strtolower($compare))
		{
			case '=':
			case 'eq':
				return ($keyValue == $value);
			case '<':
			case 'lt':
				return ($keyValue < $value);
			case '>':
			case 'gt':
				return ($keyValue > $value);
			case '<=':
			case 'le':
				return ($keyValue <= $value);
			case '>=':
			case 'ge':
				return ($keyValue >= $value);
		}
		return false;
	}


	/**
	 * get registry information from the storage (transient) or api refresh
	 *
	 * @param string registration key
	 * @return object|null
	 */
	public function getCurrentRegistration(string $registrationKey=null)
	{
		static $registryKey = null;
		static $currentRegistry = null;

		if (!empty($registrationKey) || is_null($currentRegistry))
		{
			$registryKey = $this->getRegistrationKey($registrationKey);
			if (! $currentRegistry = $this->getRegistrationCache() )
			{
				$currentRegistry = $this->refreshRegistration($registryKey);
			}
		}

		return ( empty($currentRegistry) || $this->is_api_error($currentRegistry) ) ? null : $currentRegistry;
	}


	/**
	 * refresh registry information from the remote registration server
	 *
	 * @param string registration key
	 * @return string
	 */
	public function refreshRegistration(string $registrationKey=null, array $registrationValues=[])
	{
		$registrationKey = $this->getRegistrationKey($registrationKey);

		if (empty($registrationKey)) return false;

		// get the current registration transient
		$currentRegistry = $this->getRegistrationCache();

		for ($x = 1; $x <= 3; $x++)
		{
			// from the remote registration server
			if (!empty($registrationValues))
			{
				$apiParams = array_merge( ['registry_key' => $registrationKey], $registrationValues );
				$response = $this->registryApiRequest('refresh', $apiParams);
			}
			else
			{
				$response = $this->registryApiRequest('verify', ['registry_key' => $registrationKey]);
			}

			if ($this->is_api_error($response))
			{
				if ($response->error->code == '408') {
					usleep(2500);
					continue;
				}
				if ($response->error->code != '410' && $currentRegistry) {
					$currentRegistry->status = $response->status;
					$currentRegistry->error  = $response->error;
					return $currentRegistry;
				}
				// registration not found (deleted from server)
				$this->purgeRegistrationCache();
				return $response;
			}
		}
		return $response;
	}


	/**
	 * get the current registration key
	 *
	 * @param string registration key
	 * @return string
	 */
	abstract public function getRegistrationKey(string $registrationKey=null);


	/**
	 * get registration cache
	 *
	 * @return	array
	 */
	abstract public function getRegistrationCache();


	/**
	 * set registration cache
	 *
	 * @param array $registration (registry_*) values
	 * @return	bool
	 */
	abstract public function setRegistrationCache($registration);


	/**
	 * purge registration cache
	 *
	 * @return	bool
	 */
	abstract public function purgeRegistrationCache();


	/**
	 * remote API request - builds request array and calls api_remote_request
	 *
	 * @param	string	$endpoint create, activate, deactivate, verify
	 * @param	array	$params api parameters
	 * @return	object api response (decoded)
	 */
	public function registryApiRequest($endpoint,$params)
	{
		$endpoint = strtolower($endpoint);
		switch ($endpoint)
		{
			case 'create':
				$apiKey = $this->getApiCreateKey();
				$method = 'PUT';
				break;
			case 'deactivate':
				$apiKey = $this->getApiUpdateKey();
				$method = 'DELETE';
				break;
			case 'verify':
				$apiKey = $this->getApiReadKey();
				$method = (count($params) > 1) ? 'POST' : 'GET';
				break;
			default:
				$apiKey = $this->getApiUpdateKey();
				$method = (count($params) > 1) ? 'POST' : 'GET';
				break;
		}

		$request = [
			'method' 		=> $method,
			'timeout' 		=> 6,
			'redirection' 	=> 5,
			'sslverify' 	=> false,
		];

		$request['headers']	= [
			'Accept'		=> 'application/json',
			'Referer'		=> 	(PHP_SAPI === 'cli')
								? sprintf('%s://%s%s', 'file', gethostname(), (!empty($argv)) ? '?'.http_build_query($argv) : '' )
								: sprintf('%s://%s%s', isset($_SERVER['HTTPS']) ? 'https' : 'http', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']),
			'Authorization'	=> 'Bearer '.base64_encode($apiKey),
		];
		if (in_array($method,['GET','HEAD','DELETE'])) {
			$request['headers']['Content-Type'] = 'text/plain';
			$remoteUrl = $this->getApiEndPoint($endpoint) .'?'. http_build_query($params);
		} else {
			$request['headers']['Content-Type'] = 'application/json';
			$request['body'] = wp_json_encode($params);
			$remoteUrl = $this->getApiEndPoint($endpoint);
		}

		$response =  $this->api_remote_request($endpoint,$remoteUrl,$request);

		if (!$this->is_api_error($response) && $endpoint != 'deactivate' && isset($response->registration))
		{
			// update the current registration transient
			$this->setRegistrationCache($response);
			// set the next refresh event
			$this->scheduleRegistryRefresh($response->registrar->refreshInterval,$response->registrar->refreshSchedule,$response->registration);
		}
		return $response;
	}


	/**
	 * API remote request - remote http request (wp_remote_request or curl)
	 *
	 * @param	string	$endpoint create, activate, deactivate, verify
	 * @param	string	$remoteUrl remote Url
	 * @return	object api response (decoded)
	 */
	abstract public function api_remote_request($endpoint,$remoteUrl,$request);


	/**
	 * is API error
	 *
	 * @param	string	$apiResponse
	 * @return	bool
	 */
	abstract public function is_api_error($apiResponse);
}
?>
