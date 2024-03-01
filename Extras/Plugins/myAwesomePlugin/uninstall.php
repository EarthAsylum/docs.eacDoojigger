<?php
/**
 * EarthAsylum Consulting {eac}Doojigger derivative
 *
 * Plugin uninstaller
 *
 * @category	WordPress Plugin
 * @package		myAwesomePlugin, {eac}Doojigger derivative
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2022 EarthAsylum Consulting <www.earthasylum.com>
 * @version		1.x
 */


namespace myAwesomeNamespace\uninstall;

defined( 'WP_UNINSTALL_PLUGIN' ) or exit;

class myAwesomePlugin
{
	use \EarthAsylumConsulting\Traits\plugin_uninstall;
}
myAwesomePlugin::uninstall();
