<?php
/**
 * EarthAsylum Consulting {eac} Software Registration - software registration command line refresh
 *
 * scheduled to run via crontab :
 * /path/to/php/php /path/to/html/classfolder/myAwesomePlugin_registration.refresh.php {registration_key}
 *
 * @category	WordPress Plugin
 * @package		{eac}SoftwareRegistry
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2026 EarthAsylum Consulting <www.EarthAsylum.com>
 * @version		26.1119.1
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound

include "myAwesomePlugin_registration.includes.php";

class myAwesomePluginRegistrationRefresh implements \myAwesomeNamespace\Interfaces\myAwesomePlugin_registration
{
	use \myAwesomeNamespace\Traits\myAwesomePlugin_registration_wordpress;

	/**
	 * constructor method
	 *
	 * @param string current registration key
	 * @return 	void
	 */
	public function __construct(string $registrationKey = null)
	{
		return $this->refreshRegistration($registrationKey);
	}
}
return new myAwesomePluginRegistrationRefresh($argv[0] ?? null);

// phpcs:ignore missing_direct_file_access_protection
defined( 'ABSPATH' ) or exit;
