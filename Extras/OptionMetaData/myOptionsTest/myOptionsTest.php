<?php
/**
 * EarthAsylum Consulting {eac}Doojigger derivative
 *
 * Plugin Loader
 *
 * @category	WordPress Plugin
 * @package		myOptionsTest
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2022 EarthAsylum Consulting <www.earthasylum.com>
 * @version		1.x
 * @uses		EarthAsylumConsulting\Traits\plugin_loader
 *
 * @wordpress-plugin
 * Plugin Name:			My Options Test
 * Description:			My Options Test - used to define and test {eac}Doojigger option types.
 * Version:				1.3.0
 * Requires at least:	5.5.0
 * Tested up to: 		6.4
 * Requires PHP:		7.2
 * Author:				Kevin Burkholder @ EarthAsylum Consulting
 * Author URI:			http://www.earthasylum.com
 */


namespace EarthAsylumConsulting
{
	// must have {eac}Doojigger and {eac}DoojiggerAutoloader activated
	if (!defined('EAC_DOOJIGGER_VERSION'))
	{
		\add_action( 'all_admin_notices', function()
			{
				echo '<div class="notice notice-error is-dismissible">'.
					 '<h4>myOptionsTest requires installation & activation of <em>{eac}Doojigger</em>.</h4>'.
					 '</div>';
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
 	\EarthAsylumConsulting\myOptionsTest::loadPlugin(true);
}
