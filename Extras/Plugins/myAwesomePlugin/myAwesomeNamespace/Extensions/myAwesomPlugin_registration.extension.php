<?php
namespace myAwesomeNamespace\Extensions;

/**
 * software registration extension - {eac}Doojigger for WordPress
 *
 * @category	WordPress Plugin
 * @package		{eac}SoftwareRegistry
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2023 EarthAsylum Consulting <www.EarthAsylum.com>
 * @version		1.x
 * @see 		https://eacDoojigger.earthasylum.com/phpdoc/
 * @uses 		\EarthAsylumConsulting\Traits\swRegistrationUI;
 */

include "myAwesomePlugin_registration/myAwesomePlugin_registration.includes.php";

class myAwesomePlugin_registration extends \EarthAsylumConsulting\abstract_extension
	implements \myAwesomeNamespace\Interfaces\myAwesomePlugin_registration
{
	use \EarthAsylumConsulting\Traits\swRegistrationUI;
	use \myAwesomeNamespace\Traits\myAwesomePlugin_registration_wordpress;
//	use \EarthAsylumConsulting\Traits\myAwesomePlugin_registration_filebased;

	/**
	 * @var string extension version
	 */
	const VERSION	= '23.0603.1';

	/**
	 * @var ALIAS constant ($this->Registration->...)
	 */
	const ALIAS		= 'Registration';


	/**
	 * constructor method
	 *
	 * @param 	object	$plugin main plugin object
	 * @return 	void
	 */
	public function __construct($plugin)
	{
		$this->enable_option = false;
		parent::__construct($plugin, self::ALLOW_ALL);

		if ($this->is_admin())
		{
			// load UI (last) from swRegistrationUI trait
			$this->add_action( 'options_settings_page', [$this,'swRegistrationUI'], PHP_INT_MAX );
		}
	}


	/**
	 * Called after instantiating, loading extensions and initializing
	 *
	 * @return	void
	 */
	public function addActionsAndFilters(): void
	{
		parent::addActionsAndFilters();

		// from registration_wordpress trait
		if (method_exists($this, 'addSoftwareRegistryHooks'))
		{
			$this->addSoftwareRegistryHooks();
		}
		// from swRegistrationUI trait (backend)
		if (method_exists($this, 'swRegistrationActionsAndFilters'))
		{
			$this->swRegistrationActionsAndFilters();
		}
	}


	/**
	 * destructor method
	 *
	 */
	public function __destruct()
	{
		/* make sure we're not checking the registration during a registration refresh */
		// if (!defined('REST_REQUEST') && !defined('XMLRPC_REQUEST'))
		// {
		// 	/* if necessary, set HOME and/or TMP/TMPDIR/TEMP directories */
		// 	// putenv('HOME={your home directory}');   // where the registration key is stored, otherwise use $_SERVER['DOCUMENT_ROOT']
		// 	// putenv('TMP={your temp directory}');    // where the registration data is stored, otherwise use sys_get_temp_dir()
		// 	$this->checkRegistryRefreshEvent();
		// }
	}


	/*
	 *
	 * interface implementation through swRegistrationUI & softwareregistry traits
	 *
	 */
}
/**
 * return a new instance of this class
 */
return new myAwesomePlugin_registration($this);
?>
