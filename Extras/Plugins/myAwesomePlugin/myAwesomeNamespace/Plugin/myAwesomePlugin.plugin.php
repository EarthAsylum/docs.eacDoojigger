<?php
/**
 * EarthAsylum Consulting {eac}Doojigger derivative
 *
 * @category	WordPress Plugin
 * @package		myAwesomePlugin, {eac}Doojigger derivative
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2023 EarthAsylum Consulting <www.earthasylum.com>
 * @version		1.x
 */

namespace myAwesomeNamespace\Plugin;

class myAwesomePlugin extends \EarthAsylumConsulting\abstract_context
{
	/**
	 * @trait methods for common/standard options
	 */
 	use \EarthAsylumConsulting\Traits\standard_options;

	/**
	 * @trait methods for contextual help tabs
	 */
 	use \EarthAsylumConsulting\Traits\plugin_help;


	/**
	 * constructor method
	 *
	 * @param array plugin header passed from loader script
	 * @return void
	 */
	public function __construct(array $header)
	{
		parent::__construct($header);

		$this->logAlways('version '.$this->getVersion().' '.wp_date('Y-m-d H:i:s',filemtime(__FILE__)),__CLASS__);

		if ($this->is_admin())
		{
			// Register plugin options
			$this->add_action( "options_settings_page", 		array($this, 'admin_options_settings') );

			// Add contextual help
			$this->add_action( 'options_settings_help', 		array($this, 'admin_options_help') );

			// used by optionExport in standard_options trait to process export url (admin_post)
			$this->standard_options('optionExport_action');

			// When this plugin is activated
			register_activation_hook($header['PluginFile'],		array($this, 'admin_plugin_activated') );

			// When this plugin is deactivated
			register_deactivation_hook($header['PluginFile'],	array($this, 'admin_plugin_deactivated') );

			// When this plugin is installed
			$this->add_action( 'version_installed', 			array($this, 'admin_plugin_installed'), 10, 3);

			// When this plugin is updated ('myAwesomePlugin_version_updated')
			$this->add_action( 'version_updated', 				array($this, 'admin_plugin_updated'), 10, 3 );
 		}
	}


	/**
	 * register options on options_settings_page
	 *
	 * @access public
	 * @return void
	 */
	public function admin_options_settings()
	{
		// wrapping the <h1> header in "settings_banner" <div> creates a sticky/floating header.
		// a "settings_info" <div> is floated to the right of the <h1> header.
		$this->add_filter("options_form_h1_html", function($h1)
			{
				return 	"<div id='settings_banner'>" .
							$h1 .
							"<div id='settings_info'>" .
								// adds a dashicons button...
								"<a href='".$this->getSettingsURL()."' title='This settings page'>" .
									"<span class='dashicons dashicons-admin-plugins button'><p>Settings</p></span>" .
								"</a>" .
							"</div>" .
						"</div>";
			}
		);

        // from standard_options trait
        $this->registerPluginOptions('plugin_settings',$this->standard_options(
			[
				'siteEnvironment',
				'adminSettingsMenu',
				'uninstallOptions',
				'backupOptions',
				'restoreOptions',
				'optionExport',
				'optionImport'
			]
		));
	}


	/**
	 * Add help tab on admin page
	 *
	 * @return	void
	 */
	public function admin_options_help()
	{
		ob_start();
		?>
			Lorem ipsum dolor sit amet, consectetur adipiscing elit,
			sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
			Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
			Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
			Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
		<?php

		$content = ob_get_clean();

		//  add a tab - tab_name , content , <details> heading (optional), priority (optional)
		$this->addPluginHelpTab('My Awesome Plugin', $content, ['My Awesome Plugin','open']);

		// add sidebar text/html
		$this->addPluginSidebarText('<h4>For more information:</h4>');

		// add sidebar link - title , url , tooltip (optional)
		$this->addPluginSidebarLink(
			"<span class='dashicons dashicons-editor-help'></span>Custom Plugins",
			'https://eacdoojigger.earthasylum.com/eacdoojigger/',
			"Custom Plugin Derivatives"
		);
	}


	/**
	 * Called after instantiating and loading extensions
	 *
	 * @return	void
	 */
	public function initialize(): void
	{
		parent::initialize();
	}


	/**
	 * Add actions and filters
	 * Called after instantiating, loading extensions and initializing
	 *
	 * @return	void
	 */
	public function addActionsAndFilters(): void
	{
		parent::addActionsAndFilters();
	}


	/**
	 * Add shortcodes
	 * Called after instantiating, loading extensions and initializing
	 *
	 * @return	void
	 */
	public function addShortcodes(): void
	{
		parent::addShortcodes();
	}


	/*
	 *
	 * When this plugin is activated, deactivated, installed, updated
	 *
	 */


	/**
	 * plugin activated
	 *
	 * @param bool $isNetwork true if network activated
	 * @return	void
	 */
	public function admin_plugin_activated(bool $isNetwork=false)
	{
	}


	/**
	 * plugin deactivated
	 *
	 * @param bool $isNetwork true if network activated
	 * @return	void
	 */
	public function admin_plugin_deactivated(bool $isNetwork=false)
	{
	}


	/**
	 * version installed (action {classname}_version_installed)
	 *
	 * May be called more than once on a given site (once as network admin).
	 *
	 * @param	string|null	$curVersion currently installed version number (null)
	 * @param	string		$newVersion version being installed/updated
	 * @param	bool		$asNetworkAdmin running as network admin
	 * @return	void
	 */
	public function admin_plugin_installed($curVersion, $newVersion, $asNetworkAdmin)
	{
	}


	/**
	 * version updated (action {classname}_version_updated)
	 *
	 * May be called more than once on a given site (once as network admin).
	 *
	 * @param	string|null	$curVersion currently installed version number
	 * @param	string		$newVersion version being installed/updated
	 * @param	bool		$asNetworkAdmin running as network admin
	 * @return	void
	 */
	public function admin_plugin_updated($curVersion, $newVersion, $asNetworkAdmin)
	{
	}
}
