<?php
/**
 * Add myAwesomeExtension extension to myAwesomePlugin
 *
 * @category	WordPress Plugin
 * @package		myAwesomePlugin
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2022 EarthAsylum Consulting <www.earthasylum.com>
 * @version		1.x
 *
 * @wordpress-plugin
 * Plugin Name:			My Awesome Extension
 * Description:			My Awesome Extension
 * Version:				1.1.0
 * Requires at least:	5.5
 * Requires PHP:		7.2
 * Author:				Kevin Burkholder @ EarthAsylum Consulting
 * Author URI:			http://www.earthasylum.com
 */

/**
 * This simple plugin file responds to the 'myAwesomePlugin_load_extensions' filter to load additional extensions.
 * Using this method prevents overwriting extensions when the plugin is updated or reinstalled.
 */

namespace myAwesomeNamespace;

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
				$extensionDirectories[ plugin_basename( __FILE__ ) ] = [plugin_dir_path( __FILE__ ).'/Extensions'];
				return $extensionDirectories;
			}
		);
	}
}
new \myAwesomeNamespace\myAwesomeExtension();
?>
