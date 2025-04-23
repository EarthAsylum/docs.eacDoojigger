<?php
/**
 * Add myFunctions extension to {eac}Doojigger
 *
 * @category	WordPress Plugin
 * @package		{eac}Doojigger
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2025 EarthAsylum Consulting <www.earthasylum.com>
 * @uses		EarthAsylumConsulting\eacDoojigger
 *
 * @wordpress-plugin
 * Plugin Name:			myFunctions
 * Description:			myFunctions loads custom php functions, JavaScript and Stylesheet
 * Version:				1.1.1
 * Requires at least:	5.8
 * Tested up to: 		6.8
 * Requires PHP:		7.4
 * Author:				Kevin Burkholder @ EarthAsylum Consulting
 * Author URI:			http://www.earthasylum.com
 */

/**
 * This simple plugin file responds to the 'eacDoojigger_load_extensions' filter to load additional extensions.
 * Using this method prevents overwriting extensions when the plugin is updated or reinstalled.
 *
 * To load as an extension to a derivative plugin, simply change "eacDoojigger" to the name of your derivative.
 */

namespace EarthAsylumConsulting;

class myFunctions
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
		add_filter( 'eacDoojigger_load_extensions',	function($extensionDirectories)
			{
        		/*
        		 * on plugin_action_links_ filter, add 'Settings' link
        		 */
				add_filter( (is_network_admin() ? 'network_admin_' : '').'plugin_action_links_' . plugin_basename( __FILE__ ),
					function($pluginLinks, $pluginFile, $pluginData) {
						return array_merge(
							[
								'settings'		=> eacDoojigger()->getSettingsLink($pluginData,'general'),
							],
							$pluginLinks
						);
					},20,3
				);

				$extensionDirectories[ plugin_basename( __FILE__ ) ] = [plugin_dir_path( __FILE__ ).'/Extensions'];
				return $extensionDirectories;
			}
		);
	}
}
new \EarthAsylumConsulting\myFunctions();
?>
