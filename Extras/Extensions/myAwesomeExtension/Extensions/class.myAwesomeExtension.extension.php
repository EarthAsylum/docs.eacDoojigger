<?php
/**
 * my Awesome Extension - {eac}Doojigger for WordPress
 *
 * @category	WordPress Plugin
 * @package		{eac}Doojigger
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2025 EarthAsylum Consulting <www.earthasylum.com>
 * @uses		EarthAsylumConsulting\eacDoojigger
 */

namespace myAwesomeNamespace\Extensions;

class myAwesomeExtension extends \EarthAsylumConsulting\abstract_extension
{
	/**
	 * @var string extension version
	 */
	const VERSION		= '25.0423.1';

	/**
	 * @var string extension tab name (if not set, add to 'General')
	 */
	//const TAB_NAME 	= 'Extensions';

	/**
	 * @var string|array|bool to set (or disable) default group display/switch
	 * 		false 		disable the 'Enabled'' option for this group
	 * 		string 		the label for the 'Enabled' option
	 * 		array 		override options for the 'Enabled' option (label,help,title,info, etc.)
	 */
	const ENABLE_OPTION	=
		"<abbr title='A really awesome extension'>My Awesome Extension</abbr>";


	/**
	 * constructor method
	 *
	 * @param 	object	$plugin main plugin object
	 * @return 	void
	 */
	public function __construct($plugin)
	{
		parent::__construct($plugin, self::DEFAULT_DISABLED);

		// $this->registerExtension( [ $this->className, 'awesome_examples' ] );	// loads on 'Awesome Examples' tab
		$this->registerExtension( $this->className );								// loads on 'General' tab
		// Register plugin options when needed
		$this->add_action( "options_settings_page", array($this, 'admin_options_settings') );
		// Add contextual help
		$this->add_action( 'options_settings_help', array($this, 'admin_options_help') );
	}


	/**
	 * register options on options_settings_page
	 *
	 * @access public
	 * @return void
	 */
	public function admin_options_settings()
	{
		$this->registerExtensionOptions( $this->className,
			[
				'my_option_name'	=> array(
										'type'			=> 	'text',
										'label'			=> 	'label: {field label}',
										'title'			=> 	'title: information text/html to be displayed',
									//	'options'		=>	array({option,...}),
										'default'		=>	'default: {default option or value}',
										'info'			=> 	'info: Information/instructions',
									//	'attributes'	=> 	html attributes array ['name="value", name="value"'],
										'help'			=>	['My Extension'=>"This field defaults to a 'text' input"],
									),
				'my_option_name_a' 	=> array(
										'type'			=> 	'text',
										'label'			=> 	"Short Label A",
										'info'			=> 	"(text field) Instructions, description, etc.",
										'help'			=>	['My Extension'=>"This field is a normal 'text' input field"],
									),
				'my_option_name_b' 	=> array(
										'type'			=> 	'checkbox',
										'label'			=> 	"Short Label B",
										'options'		=> 	['single','checkbox','options'],
										'default'		=> 	['single','checkbox','options'],
										'info'			=> 	"(checkbox field) Instructions, description, etc.",
										'help'			=>	['My Extension'=>"This field is a standard 'checkbox' input field"],
									),
				'my_option_name_c' 	=> array(
										'type'			=> 	'radio',
										'label'			=> 	"Short Label C",
										'options'		=> 	[ ['associated'=>'A'],['radio'=>'R'],['options'=>'O'] ],
										'default'		=> 	'A',
										'info'			=> 	"(radio field) Instructions, description, etc.",
										'help'			=>	['My Extension'=>"This field is a standard 'radio' input field"],
									),
			]
		);
	}

	/**
	 * Add help tab on admin page
	 *
	 * @return	void
	 */
	public function admin_options_help()
	{
		if ( ! $this->plugin->isSettingsPage('general') ) return; 	// only on 'General' tab

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
		$this->addPluginHelpTab('My Extension', $content, ['My Awesome Extension','open']);

		// add sidebar link - title , url , tooltip (optional)
		$this->addPluginSidebarLink(
			"<span class='dashicons dashicons-editor-help'></span>Custom Extensions",
			'https://eacdoojigger.earthasylum.com/extensions/',
			"Custom Plugin Extensions"
		);
	}


	/**
	 * initialize method - called from main plugin
	 *
	 * @return 	void
	 */
	public function initialize()
	{
		if ( ! parent::initialize() ) return; // disabled
	}


	/**
	 * Add filters and actions - called from main plugin
	 *
	 * @return	void
	 */
	public function addActionsAndFilters()
	{
		parent::addActionsAndFilters();
	}


	/**
	 * Add shortcodes - called from main plugin
	 *
	 * @return	void
	 */
	public function addShortcodes()
	{
		parent::addShortcodes();
	}


	/**
	 * version updated
	 *
	 * @param	string	$curVersion currently installed version number
	 * @param	string	$newVersion version being installed/updated
	 * @return	bool
	 */
	public function adminVersionUpdate($curVersion,$newVersion)
	{
	}
}

/**
 * return a new instance of this class
 */
return new myAwesomeExtension($this);
?>
