=== {eac}Doojigger, custom extensions ===
Plugin URI: 		https://eacDoojigger.earthasylum.com/
Author: 			[EarthAsylum Consulting](https://www.earthasylum.com)
Last Updated: 		26-Jan-2023
Contributors:       kevinburkholder
Requires EAC: 		2.0

Custom extensions to {eac}Doojigger or derivatives - Add easy-to-code extensions to {eac}Doojigger or your own derivative plugins using the extension framework.

== Description ==

= Summary =

{eac}Doojigger extensions are, relatively, small PHP classes that extend the main plugin. Typically they are created to address specific requirements for your website or business. An extension focuses on your requirements while having available the full plugin functionality of {eac}Doojigger.

+	[Naming Conventions](#naming-conventions)
+	[Define The Extension Class](#define-the-extension-class)
+	[Instantiating The Extension](#instantiating-the-extension)
+	[Registering The Extension](#registering-the-extension)
+	[More...](#extension-option-flags)


= Naming Conventions =

The name of the extension file *must* end with `.extension.php`.

Required file name :
+	`{something}.extension.php`
	+	e.g. myAwesomeExtension.extension.php
+	`class.{something}.extension.php`
	+	e.g. class.myAwesomeExtension.extension.php

Using `{something}.{someother}.extension.php` will use `{someother}_extension_enabled` as the 'Enabled' option for the {something} extension (assuming class names match file names).
+	`myAwesomeExtension.extension.php` and `myAwesomeAddons.myAwesomeExtension.extension.php` both use `myAwesomeExtension_extension_enabled`.
+	Both extensions (`myAwesomeExtension` and `myAwesomeAddons`) are enabled or disabled by the same checkbox on the administration settings screen.

The name of the extension class *must* be unique and *should* match the file name.

Recommended class name :
+	`{something}`
	+	e.g. myAwesomeExtension
+	`{something}_extension`
	+	e.g. myAwesomeExtension_extension


= Define The Extension Class =

The first executable line in your extension class must define the namespace as '{namespace}\Extensions'...

	namespace myAwesomeNamespace\Extensions;

Then declare your extension class by extending `\EarthAsylumConsulting\abstract_extension`...

	namespace myAwesomeNamespace\Extensions;

	class myAwesomeExtension extends \EarthAsylumConsulting\abstract_extension
	{
		public function __construct($plugin)
		{
			parent::__construct($plugin, self::ALLOW_ALL);

			if ($this->plugin->isSettingsPage())
			{
				$this->registerExtension( [$this->className, {admin_tab_name}],
					[
						...option meta data...
					]
				);
			}
		}
		public function initialize()
		{
		}
		public function addActionsAndFilters()
		{
		}
		public function addShortcodes()
		{
		}
	}
	return new myAwesomeExtension($this);


= Instantiating The Extension =

The extension file (`myAwesomeExtension.extension.php`) must return the instantiated class passing `$this` (the main plugin instance) as the first argument:

	return new myAwesomeExtension($this);

The main plugin will automatically load the class/extension file when WordPress triggers the 'plugins_loaded' action.

The extension must call the parent constructor within its `__construct()` method:

	parent::__construct( $plugin, {option_flags} );

+	Sets $this->plugin to the class instance of the main plugin (e.g. eacDoojigger or myAwesomePlugin).
+	Sets $this->pluginName to the short name of the main plugin (sans namespace - 'eacDoojigger' or 'myAwesomePlugin').
+	Sets $this->className to the short name of this extension class (sans namespace - 'myAwesomeExtension').
+	Returns bool (enabled).

The main plugin will then:

+	Fire the `eacDoojigger_extensions_loaded` action.
+	Call the `initialize()` method.
+	Fire the `eacDoojigger_initialize` action.
+	Call the `addActionsAndFilters()` method.
+	Call the `addShortcodes()` method.
+	Fire the `eacDoojigger_ready` action.

\* *For plugin derivatives, the action names are the derivative plugin name (e.g. myAwesomePlugin_extensions_loaded instead of eacDoojigger_extensions_loaded)*.

= Registering The Extension =

The extension must register itself in the constructor with:

	$this->registerExtension( [$this->className, {admin_tab_name}], [option meta data] );

+	Registers '{classname}_extension_enabled' option for this extension.
+	Registers extension options with 'Enabled' checkbox option.
+	Adds options to the {admin_tab_name} tab on the settings page.

If the 1st argument is a string (not an array and not another extension name), the options will be added to the 'General' tab:

	$this->registerExtension( $this->className, [option meta data] );

Equivalent to:

	$this->registerExtension( [$this->className, 'general'], [option meta data] );

The 1st argument may be replaced with another extension's name to append options to that extension & tab:

	$this->registerExtension( 'debugging', [...additional debugging options] );

if the extension has no options/settings to register, the array may be omitted:

	$this->registerExtension( [$this->className, {admin_tab_name}]);

if the extension has no options/settings to register AND wants no 'Enabled' option (the extension is essentially invisible):

	$this->registerExtension( false );

if the extension has options/settings to register BUT wants no 'Enabled' option:

	$this->enable_option = false; // <- before registerExtension();
	$this->registerExtension( [$this->className, {admin_tab_name}], [option meta data] );

It is rare that extension options are needed anywhere but within the plugin's administrator settings page. It is both optimal and good practice to register your settings within a test for the page using `$this->plugin->isSettingsPage()`...

	if ($this->plugin->isSettingsPage())
	{
		$this->registerExtension( [$this->className, {admin_tab_name}], [option meta data] );
	}

Or you may use the just-in-time `options_settings_page` *action* that is fired right before rendering the settings html page.

	public function __construct(array $header)
	{
		if ($this->is_admin())
		{
			$this->registerExtension( [$this->className, {admin_tab_name}] );
			$this->add_action( "options_settings_page", array($this, 'admin_options_settings') );
		}
	}

	public function admin_options_settings()
	{
		$this->registerExtensionOptions( [$this->className, {admin_tab_name}], [option meta data] );
	}


= Extension Option Flags =

The {option_flags} passed to the parent constructor may included any of the abstract_extension constants shown here.

	const ALLOW_ADMIN		= 0b00000001;		// enabled on admin pages (default is disabled)
	const ONLY_ADMIN		= 0b00000010;		// enabled only on admin pages, not front-end
	const ALLOW_NETWORK		= 0b00000100;		// enabled for network admin in multisite (uses network options)
	const ALLOW_CRON		= 0b00001000;		// enabled for cron requests
	const ALLOW_CLI			= 0b00010000;		// enabled for wp-cli requests
	const DEFAULT_DISABLED	= 0b00100000;		// force {classname}_enabled' option to default to not enabled
	const ALLOW_ALL			= self::ALLOW_ADMIN|self::ALLOW_NETWORK|self::ALLOW_CRON|self::ALLOW_CLI;

Multiple options are logically OR'd together:

	parent::__construct( $plugin, self::ALLOW_ADMIN | self::ONLY_ADMIN | self::ALLOW_NETWORK );


= Extension Option Meta Data =

Option Meta Data is an array of arrays defining options/settings that can be updated from the plugin's 'settings' page.
Each option name is automatically prefixed with the plugin name (i.e. 'eacDoojigger').

An option is defined as:

    'my_option_name'   => array(
							'type'			=> 	'type: {input type}',
							'label'			=> 	'label: {field label}',
							'title'			=> 	'title: information text/html to be displayed',
    	                    'options'       =>  array({option,...}),
							'default'		=>	'default: {default option or value}',
							'info'			=> 	'info: Information/instructions',
        	                'attributes'    =>  html attributes array ['name="value", name="value"'],
                    		'help'			=> 'contextual help'
                    ),

>	See [Administrator Options](/options/) (found in the */Extras/OptionMetaData/readme.txt* file) for details on registering and using options in you plugin and extensions.


= Extension Methods =

The extension may use:

	$this->isEnabled(false);

to disable further calls to the extension

The extension should use:

	$this->is_option(optionName [,value]);

to check for option value; returns true|false or actual value, 'Enabled'=true, 'Disabled'=false.

The extension may use:

	$this->is_network_enabled();

to check if the same extension is enabled at the network level.

The extension should use:

	$this->is_network_option(optionName [,value]);

to check network enabled and network option value.

The extension may use:

	$this->registerExtensionOptions( $this->className, [option meta data] );

to register additional options (after $this->registerExtension(...)).


= Extension Version Updates =

If a constant 'VERSION' is set, automatic version checking and update methods may be used.

	const VERSION	= '22.0915.1';

>	Version format shown, a [Calendar Versioning](https://calver.org/) variation: `YY.MMDD.s` (year.monthday.sequence). [Semantic versioning](https://semver.org/): `Mj.Mn.p` (major.minor.patch) works just as well.

When the VERSION changes:
+	An action is triggered : `{pluginName}_version_updated_{extensionName}`.
	+	e.g. `myAwesomPlugin_version_updated_myAwesomeExtension`
+	If the method `adminVersionUpdate()` is defined by the extension, it will be called directly.

Both options take 2 parameters, the previous version and the new (current) version.


= Extension Method Calling =

Extension methods may be called programmatically through the plugin class:

by using:

	$this->plugin->callMethod([extensionName, extensionMethodName], [,args...]);
	$this->callMethod([extensionName, extensionMethodName], [,args...]);
	eacDoojigger()->callMethod([extensionName, extensionMethodName], [,args...]);

or: (as of Version 2.0.1)

	$this->extensionName->extensionMethodName(args...);
	eacDoojigger()->extensionName->extensionMethodName(args...);

this will attempt to call "extensionMethodName" in the 'extensionName' extensions.

by using:

	$this->plugin->callAllExtensions(extensionMethodName [,args...]);
	$this->callAllExtensions(extensionMethodName [,args...]);
	eacDoojigger()->callAllExtensions(extensionMethodName [,args...]);

this will attempt to call "extensionMethodName" in all enabled extensions.


= Filters and Shortcodes =

Through the abstract_extension class, extensions provide a filter to overrids the $this->enabled() method (as part of the method)...

This filter is called for each extension and passes the extension class name as the 2nd argument. Other external code may enable or disable an extension through this filter.

	/**
	 * filter {pluginName}_extension_enabled allow disabling extensions
	 * @param	bool current enabled status
	 * @param	string $this->className (extension name)
	 * @return	bool enabled value
	 */
    $this->enabled = $this->apply_filters( 'extension_enabled', $this->enabled, $this->className );


Filter Examples:

	\add_filters( 'eacDoojigger_extension_enabled', function($enabled,$extension) {
		if ($extension == 'myDisabledExtension') $enabled = false;
		return $enabled;
	});


{eac}Doojigger (and your derivatives) provides a front-end filter and shortcode that gives access to (nearly) all public methods in extensions.

The filter and shortcode name is the plugin class name (e.g. 'eacDoojigger' or 'myAwesomePlugin').
Arguments include:

+	method='{methodName}', which is made up of the extension name and the extension method (extension.method).
+	args='...', to pass a list of arguments/values.
+	default='...', to set a default value.
+	index='...', to index an item from an array returned by your method.

Filter Examples:

	\apply_filters('eacDoojigger', null, [ method='myAwesomeExtension.myCoolMethod' ]);

Shortcode Examples:

	['eacDoojigger' method='myAwesomeExtension.myCoolMethod']

Arguments to the filter or shortcode may be passed as a comma-delimited string as 'args='.

	\apply_filters('eacDoojigger', null, [ method='myAwesomeExtension.myCoolMethod' args='arg1,arg2,...']);
	['eacDoojigger' method='myAwesomeExtension.myCoolMethod' args='arg1,arg2,...']


= Extension Contextual Help =

Adding contextual help to your extension, through the WordPress help system, is easy using the methods built into {eac}Doojigger...

In your `__constructor()` method, add the `options_settings_help` action...

	$this->add_action( 'options_settings_help', array( $this, 'admin_options_help') );

Then add the method to create your help content...

 	public function admin_options_help()
	{
		$this->addPluginHelpTab('help-tab-name','...help content...');
		$this->addPluginSidebarText('...sidebar content...');
		$this->addPluginSidebarLink('title','url');
	}

If you want your extension help to only appear on the extension tab, use the `isSettingsPage()` method before adding any help content...

	if ($this->plugin->isSettingsPage('extension-tab-name')) {
		$this->addPluginHelpTab('help-tab-name','...help content...');
	}

>	See [Contextual Help](/contextual-help/) (found in the */Extras/ContextualHelp/readme.txt* file) for more details.


= Extension Debugging =

Extensions may implement a debugging filter to provide debugging/state information:

	/*
	 * filter {pluginname}_debugging get debugging information
	 * param	array 	current array
	 * return	array	extended array with [ extension_name => [key=>value array] ]
	 */
	add_filter( '{pluginname}_debugging', array($this, 'my_debugging'));

	public function my_debugging($debugging_array)
	{
		$debugging_array[$this->className] = array(
					'somekey'	=> 'somevalue',
					'anotherkey'=> 'anothervalue',
		)
		return $debugging_array;
	}


= Extension Plugin =

A simple plugin can be created to add extensions to an existing plugin (eacDoojigger or myAwesomePlugin) that are outside of the plugin folder. This new plugin answers a filter to point to a separate `Extensions` directory and exist in its own plugin folder with no risk of being overwritten.


	/**
	 * Plugin Name: My Awesome Extensions
	 */
	namespace myAwesomeNamespace;

	class myAwesomeExtension
	{
		public function __construct()
		{
			add_filter( 'eacDoojigger_load_extensions',	function($extensionDirectories)
				{
					$extensionDirectories[ plugin_basename( __FILE__ ) ] = [ plugin_dir_path( __FILE__ ).'/Extensions' ];
					return $extensionDirectories;
				}
			);
		}
	}
	new \myAwesomeNamespace\myAwesomeExtension();

Extensions are then placed in the '.../myAwesomeExtension/Extensions' folder.

This extension can also provide automatic updating.

>	See [Automatic Updates](/automatic-updates/) (found in the */Extras/AutoUpdate/readme.txt* file).


= Skeleton/Framework  =

There is a complete, functional *myAwesomeExtension* skeleton with these examples in the *Extras/Extensions* folder of {eac}Doojigger that you can use to build your extensions by simply changing the namespace name, plugin name, extension name, and adding your own code.

There is also a fully functional *myFunctions* extension plugin in the *Extras/Extensions* folder intended to be used to replace (or augment) a custom theme `functions.php`, with optional custom stylesheet and javascript.


== Screenshots ==

1. My Awesome Plugin with My Awesome Extension
![myAwesomeExtension](https://swregistry.earthasylum.com/software-updates/eacdoojigger/assets/screenshot-9.png)


== Other Notes ==

= Additional Information =

+	Extensions in the plugin 'Extensions' folder are automatically loaded.
+	Extensions in the plugin '{namespace}/Extensions' folder are automatically loaded.
+	Extensions in a 'admin' or 'backend' folder are only loaded for admin requests.
+	Extensions in a 'network' folder are only loaded for network admin requests.
+	Extensions in a 'public' or 'frontend' folder are only loaded for front-end requests.
+	Other directory names in the Extensions folder may be used to categorize extensions.
+	Directory or extension names beginning with '-', '_' or '.' are ignored (disabled).
+	New extensions can be added by placing them in one of the plugin 'Extensions' folders.
	*However, when the plugin is updated, those extensions will be lost and must be re-installed.*
	A better alternative is to create an extension plugin to add new extensions.

