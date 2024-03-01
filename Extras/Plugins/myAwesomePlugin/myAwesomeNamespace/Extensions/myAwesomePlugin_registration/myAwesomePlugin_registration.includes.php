<?php
/**
 * EarthAsylum Consulting {eac} Software Registration - software registration includes
 *
 * includes the interfaces and traits used by the software registration API
 *
 * @category	WordPress Plugin
 * @package		{eac}SoftwareRegistry
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2021 EarthAsylum Consulting <www.EarthAsylum.com>
 * @version		1.x
 */

/*
 *	class <yourclassname> [extends something] implements \myAwesomeNamespace\myAwesomePlugin_registration_interface
 *	{
 *		use \myAwesomeNamespace\Traits\myAwesomePlugin_registration_wordpress;
 *				- OR -
 *		use \myAwesomeNamespace\Traits\myAwesomePlugin_registration_filebased;
 *		...
 *	}
 */

/*
 * include interface...
 */
	require "myAwesomePlugin_registration.interface.php";

/*
 * include traits ...
 */
	require "myAwesomePlugin_registration.interface.trait.php";

/*
 *	require "myAwesomePlugin_registration.wordpress.trait.php";
 *				- OR -
 *	require "myAwesomePlugin_registration.filebased.trait.php";
 */
 	require "myAwesomePlugin_registration.wordpress.trait.php";
