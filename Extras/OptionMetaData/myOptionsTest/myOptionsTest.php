<?php
/**
 * EarthAsylum Consulting {eac}Doojigger derivative
 *
 * Provides examples of input types, parameters, and processing callbacks & filters.
 *
 * @category	WordPress Plugin
 * @package		myOptionsTest
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2025 EarthAsylum Consulting <www.earthasylum.com>
 * @uses		EarthAsylumConsulting\Traits\plugin_loader
 *
 * @wordpress-plugin
 * Plugin Name:			My Options Test
 * Description:			My Options Test - Provides examples of input types, parameters, and processing callbacks & filters.
 * Version:				1.4.2
 * Requires at least:	5.8
 * Tested up to: 		6.8
 * Requires PHP:		7.4
 * Author:				Kevin Burkholder @ EarthAsylum Consulting
 * Author URI:			http://www.earthasylum.com
 */


namespace myAwesomeNamespace
{
	// must have {eac}Doojigger and {eac}DoojiggerAutoloader activated
	if (!defined('EACDOOJIGGER_VERSION'))
	{
		\add_action( 'all_admin_notices', function()
			{
			echo '<div class="notice notice-error is-dismissible"><p>myAoptionsTest requires installation & activation of '.
				 '<a href="https://eacdoojigger.earthasylum.com/eacdoojigger" target="_blank">{eac}Doojigger</a>.</p></div>';
			}
		);
		return;
	}


	/**
	 * loader/initialization class
	 */
	class myOptionsTest
	{
		use \EarthAsylumConsulting\Traits\plugin_loader;

		/**
		 * @var array $plugin_detail
		 * 	'PluginFile' 	- the file path to this file (__FILE__)
		 * 	'NameSpace' 	- the root namespace of our plugin class (__NAMESPACE__)
		 * 	'PluginClass' 	- the full classname of our plugin (to instantiate)
		 */
		protected static $plugin_detail =
			[
				'PluginFile'		=> __FILE__,
				'NameSpace'			=> __NAMESPACE__,
				'PluginClass'		=> __NAMESPACE__.'\\Plugin\\myOptionsTest',
			];
	} // myOptionsTest
} // namespace


namespace // global scope
{
	defined( 'ABSPATH' ) or exit;

	/**
	 * Run the plugin loader - only for php files
	 */
 	\myAwesomeNamespace\myOptionsTest::loadPlugin(true);
}
