<?php
/**
 * EarthAsylum Consulting {eac}Doojigger derivative
 *
 * Plugin Loader
 *
 * @category	WordPress Plugin
 * @package		myAwesomePlugin
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2026 EarthAsylum Consulting <www.earthasylum.com>
 * @uses		EarthAsylumConsulting\Traits\plugin_loader
 *
 * @wordpress-plugin
 * Plugin Name:			My Awesome Plugin
 * Description:			EarthAsylum Consulting {eac}Doojigger Awesome derivative
 * Version:				1.3.5
 * Requires at least:	5.8
 * Tested up to: 		7.1
 * Requires PHP:		8.1
 * Requires EAC:		3.1
 * Plugin URI: 			https://github.com/EarthAsylum/myAwesomePlugin
 * Update URI: 			https://dev.earthasylum.net/software-updates/myAwesomePlugin.json
 * Author:				Kevin Burkholder @ EarthAsylum Consulting
 * Author URI:			http://www.earthasylum.com
 * Text Domain:			myAwesomePlugin
 * Domain Path:			/languages
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
	if (! class_exists( 'EarthAsylumConsulting\eacDoojigger', false ) )
	{
		require_once 'dependency.php';
		return \EarthAsylumConsulting\dependency::notice([
			'plugin'	=>	[ 'My Awesome Plugin' 	=> plugin_basename( __FILE__ ) ],
			'requires'	=> 	[ '{eac}Doojigger' 		=> 'eacDoojigger/eacDoojigger.php' ],
			'manifest'	=> 'https://eacdoojigger.earthasylum.com/software-updates/eacdoojigger.json',
			'download'	=> 'https://eacdoojigger.earthasylum.com/software-updates/eacdoojigger.zip',
			'after'		=> self_admin_url('/admin.php?page=eacdoojigger-settings&tab=registration')
		]);
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
				'RequiresWP'		=> '5.8',			// WordPress
				'RequiresPHP'		=> '8.1',			// PHP
				'RequiresEAC'		=> '3.1',			// eacDoojigger
			//	'RequiresWC'		=> '9.0',			// WooCommerce
				'NetworkActivate'	=>	false,			// require (or forbid) network activation
				'AutoUpdate'		=> 'self',			// automatic update 'self' or 'wp'
			];
	} // myAwesomePlugin
} // namespace


namespace // global scope
{
	defined( 'ABSPATH' ) or exit;

	/**
	 * Global function to return an instance of the plugin
	 *
	 * @return object
	 */
	function myAwesomePlugin()
	{
		return \myAwesomeNamespace\myAwesomePlugin::getInstance();
	}

	/**
	 * Run the plugin loader - only for php files
	 */
 	\myAwesomeNamespace\myAwesomePlugin::loadPlugin(true);
}
