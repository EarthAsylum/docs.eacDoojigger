<?php
namespace myAwesomeNamespace;

/**
 * Add myAwesomeExtension extension to myAwesomePlugin
 *
 * @category	WordPress Plugin
 * @package		myAwesomePlugin
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2026 EarthAsylum Consulting <www.earthasylum.com>
 *
 * @wordpress-plugin
 * Plugin Name:			My Awesome Extension
 * Description:			My Awesome Extension
 * Version:				1.1.4
 * Requires at least:	5.9
 * Requires PHP:		8.2
 * Author:				Kevin Burkholder @ EarthAsylum Consulting
 * Author URI:			http://www.earthasylum.com
 * License: 			GPLv3 or later
 * License URI: 		https://www.gnu.org/licenses/gpl.html
 */

/**
 * This simple plugin file responds to the 'myAwesomePlugin_load_extensions' filter to load additional extensions.
 * Using this method prevents overwriting extensions when the plugin is updated or reinstalled.
 */

if (! defined( 'ABSPATH' )) exit;

class myAwesomeExtension
{
	/**
	 * constructor method
	 *
	 * @return	void
	 */
	public function __construct()
	{
		/**
		 * {pluginname}_load_extensions - get the extensions directory to load
		 *
		 * @param 	array	$extensionDirectories - array of [plugin_slug => plugin_directory]
		 * @return	array	updated $extensionDirectories
		 */
		add_filter( 'myAwesomePlugin_load_extensions',	function($extensionDirectories)
			{
				$extensionDirectories[ plugin_basename( __FILE__ ) ] = [plugin_dir_path( __FILE__ )];
				return $extensionDirectories;
			}
		);
	}
}
new \myAwesomeNamespace\myAwesomeExtension();
?>
