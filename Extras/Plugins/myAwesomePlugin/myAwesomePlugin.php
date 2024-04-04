<?php
/**
 * EarthAsylum Consulting {eac}Doojigger derivative
 *
 * Plugin Loader
 *
 * @category	WordPress Plugin
 * @package		myAwesomePlugin
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2023 EarthAsylum Consulting <www.earthasylum.com>
 * @version		1.x
 * @uses		EarthAsylumConsulting\Traits\plugin_loader
 *
 * @wordpress-plugin
 * Plugin Name:			My Awesome Plugin
 * Description:			EarthAsylum Consulting {eac}Doojigger Awesome derivative
 * Version:				1.2.2
 * Requires at least:	5.5.0
 * Tested up to: 		6.5
 * Requires PHP:		7.4
 * Requires EAC:		2.0
 * Plugin URI:			https://myawesomeserver.com/plugins/myAwesomePlugin/myAwesomePlugin.html
 * Author:				Kevin Burkholder @ EarthAsylum Consulting
 * Author URI:			http://www.earthasylum.com
 * Text Domain:			myAwesomePlugin
 * Domain Path:			/languages
 */

/*
 * For automatic updates, include in above @wordpress-plugin block...
 * - Update URI: 	https://myawesomeserver.com/plugins/myAwesomePlugin/myAwesomePlugin.json
 */

/*
 * 	                                    											/ abstract_frontend.class.php \
 *	myAwesomePlugin.php -> myAwesomePlugin.class.php - abstract_context.class.php -             or                	- abstract_core.class.php = object of class myAwesomePlugin
 *	                                    											\ abstract_backend.class.php  /
 */

/*
	See http://rachievee.com/the-wordpress-hooks-firing-sequence/
	We trigger loading/initializing/hooks on 'plugins_loaded' action
	Extensions should use 'init' or 'wp_loaded' (headers are sent before wp_loaded)
	or {classname}_extensions_loaded or {classname}_ready
*/


namespace myAwesomeNamespace
{
	// must have {eac}Doojigger and {eac}DoojiggerAutoloader activated
	if (!defined('EAC_DOOJIGGER_VERSION'))
	{
		\add_action( 'all_admin_notices', function()
			{
			echo '<div class="notice notice-error is-dismissible"><p>myAwesoomePlugin requires installation & activation of '.
				 '<a href="https://eacdoojigger.earthasylum.com/eacdoojigger" target="_blank">{eac}Doojigger</a>.</p></div>';
			}
		);
		return;
	}


	/**
	 * loader/initialization class
	 */
	class myAwesomePlugin
	{
		use \EarthAsylumConsulting\Traits\plugin_loader;
		use \EarthAsylumConsulting\Traits\plugin_environment;

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
				'PluginClass'		=> __NAMESPACE__.'\\Plugin\\myAwesomePlugin',
				'RequiresWP'		=> '5.5',			// WordPress
				'RequiresPHP'		=> '7.2',			// PHP
				'RequiresEAC'		=> '2.3',			// eacDoojigger
			//	'RequiresWC'		=> '5.2',			// WooCommerce
				'NetworkActivate'	=>	false,			// require (or forbid) network activation
				'AutoUpdate'		=> 'self',			// automatic update 'self' or 'wp'
			];
	} // myAwesomePlugin
} // namespace


namespace // global scope
{
	defined( 'ABSPATH' ) or exit;

	/**
	 * Run the plugin loader - only for php files
	 */
 	\myAwesomeNamespace\myAwesomePlugin::loadPlugin(true);
}
