<?php
namespace EarthAsylumConsulting;

/**
 * {eac}Doohickey - a place to load {eac}Doololly extensions for {eac}Doojigger.
 *
 * Install this plugin in the /wp-content/plugins folder.
 * 		- make sure file permissions are properly set.
 *		- Enable the {eac}Doohickey plugin from the plugins page.
 * Install any additional Doolollys/extensions in the 'eacDoohickeys/Doolollys' folder.
 *
 * @category	WordPress Plugin
 * @package		{eac}Doohickey\{eac}Doojigger Extensions
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2025 EarthAsylum Consulting <www.earthasylum.com>
 * @link		https://eacDoojigger.earthasylum.com/
 *
 * @wordpress-plugin
 * Plugin Name:			{eac}Doohickey
 * Description:			{eac}Doohickey - a place to load {eac}Doololly extensions for {eac}Doojigger.
 * Version:				0.1.0
 * Requires at least:	5.8
 * Tested up to:		6.8
 * Requires PHP:		7.4
 * Plugin URI:			https://eacdoojigger.earthasylum.com/
 * Author:				EarthAsylum Consulting
 * Author URI:			http://www.earthasylum.com
 * License:				GPLv3 or later
 * License URI:			https://www.gnu.org/licenses/gpl.html
 */

class eacDoohickey
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
		 * @param	array	$extensionDirectories - array of [plugin_slug => plugin_directory]
		 * @return	array	updated $extensionDirectories
		 */
		add_filter( 'eacDoojigger_load_extensions', function($extensionDirectories)
			{
				/*
    			 * Add our extension to load
    			 */
				$extensionDirectories[ plugin_basename( __FILE__ ) ] = [plugin_dir_path( __FILE__ )];
				return $extensionDirectories;
			}
		);
	}
}
new \EarthAsylumConsulting\eacDoohickey();
?>
