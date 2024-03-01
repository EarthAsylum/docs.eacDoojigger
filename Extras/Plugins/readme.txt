=== {eac}Doojigger, plugin derivatives ===
Plugin URI: 		https://eacDoojigger.earthasylum.com/
Author: 			[EarthAsylum Consulting](https://www.earthasylum.com)
Last Updated: 		15-Nov-2022
Contributors:       kevinburkholder
Requires EAC: 		2.0

Custom plugin derivatives of {eac}Doojigger - Build your own plugin using a robust, efficient, and clean, foundation.

== Description ==

= Summary =

{eac}Doojigger derivatives are easy to create, custom WordPress plugins built from the {eac}Doojigger abstract classes and traits.

+	[Your directory structure](#your-directory-structure) (/myAwesomePlugin/)
+	[Create your custom plugin loader](#create-your-custom-plugin-loader) (myAwesomePlugin.php)
+	[Create your custom plugin uninstaller](#create-your-custom-plugin-uninstaller) (uninstall.php)
+	[Create your custom plugin class](#create-your-custom-plugin-class) (myAwesomePlugin.class.php)
+	[Example plugin class](#example-plugin-class)
+	[Contextual Help](#plugin-contextual-help)
+	[Option Meta Data](#plugin-option-meta-data)
+	[Using Your Plugin](#using-your-plugin)

>	 When creating an {eac}Doojigger derivative plugin, you will be creating three classes (the loader, the uninstaller, and the plugin class), all three will use the same *class name* but they will be in different *namespaces*.


= Your directory structure =

Your plugin should be in a folder using your plugin class name. For example, if your plugin name is 'myAwesomePlugin', your plugin root folder should be named 'myAwesomePlugin'.

>	If you plan to submit your plugin to WordPress to be added to the WordPress Plugin Repository, WordPress may assign a slug-name that differs from your plugin name (i.e. my-awesome-plugin). In this case, your root directory must match the assigned slug-name.

Within the 'myAwesomePlugin' folder, you should have a folder using your namespace. For example, if the namespace you're using is 'myAwesomeNamespace', you should have a folder named 'myAwesomePlugin/myAwesomeNamespace'.

Within the namespace folder, you must have a 'Plugin' folder to hold your plugin class file and you may have an 'Extensions' folder to hold any extensions.
```txt
myAwesomePlugin/
		myAwesomePlugin.php
		uninstall.php

		myAwesomeNamespace/
				Plugin
					myAwesomePlugin.class.php
				Extensions
					class.myAwesomeExtension.extension.php
```


= Create your custom plugin loader =

The loader class is a static class that is the WordPress primary plugin file and must contain the [required WordPress headers](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/); it will use the plugin_loader trait provided by {eac}Doojigger.

The file name should be {classname}.php (e.g. 'myAwesomePlugin.php') and must exist in the root folder of your plugin package.

    /**
	 * Plugin Name:		My Awesome Plugin
	 * Description:		EarthAsylum Consulting {eac}Doojigger Awesome derivative
	 * Version:			1.0.0
     */
    namespace myAwesomeNamespace
    {
        class myAwesomePlugin
        {
            use \EarthAsylumConsulting\Traits\plugin_loader;

            protected static $plugin_detail =
                [
                    'PluginFile'    => __FILE__,
                    'NameSpace'     => __NAMESPACE__,
                    'PluginClass'   => __NAMESPACE__.'\\Plugin\\myAwesomePlugin',
                ];
        } // myAwesomePlugin
    } // namespace

    namespace // global scope
    {
        defined( 'ABSPATH' ) or exit;

        \myAwesomeNamespace\myAwesomePlugin::loadPlugin(true);
    }

__Automatic Updates__

To enable automatic updates for your plugin, add 'AutoUpdate' to the `$plugin_detail` array...

	'AutoUpdate'		=> 'self',			// automatic update 'self' or 'wp'

This will load and use the `plugin_update` trait.
Your plugin header (in your loader) must include *Update URI*
```txt
* Update URI: https://myawesomeserver.com/plugins/myAwesomePlugin/myAwesomePlugin.json
```
and you must provide the needed JSON and .zip files for updates on your server.

>	See [Automatic Updates](/automatic-updates/) (found in the */Extras/AutoUpdate/readme.txt* file) for more information on automatic updating.

__Runtime Environment Checking__

In addition, you may optionally include environment checking with...

	use \EarthAsylumConsulting\Traits\plugin_environment;

and add the `$plugin_detail` options to check...

	'RequiresWP'		=> '6.1',			// WordPress version
	'RequiresPHP'		=> '7.2',			// PHP version
	'RequiresEAC'		=> '2.3',			// eacDoojigger version
	'RequiresWC'		=> '7.0',			// WooCommerce version
	'NetworkActivate'	=>	false,			// require (or forbid) network activation

If any of the checks fail, a notice is presented on the administrator screen:
```txt
 Error from myAwesomePlugin/myAwesomePlugin.php:
 This plugin requires WordPress version 6.1 or greater, your server is running version 6.0.2.

 Plugin deactivated.
```
And the plugin is automatically deactivated.

__Global Plugin Function__

To create a globally accessable function to access your plugin methods, simply add the following in the global namespace:

    namespace // global scope
    {
        defined( 'ABSPATH' ) or exit;

		function myAwesomePlugin()
		{
			return \myAwesomeNamespace\myAwesomePlugin::getInstance();
		}

        \myAwesomeNamespace\myAwesomePlugin::loadPlugin(true);
    }


= Create your custom plugin uninstaller =

The uninstaller class is a static class used when your plugin is uninstalled; it will use the plugin_uninstall trait provided by {eac}Doojigger.

The file name must be uninstall.php and must exist in the root folder of your plugin.

	namespace myAwesomeNamespace\uninstall;

	defined( 'WP_UNINSTALL_PLUGIN' ) or exit;

	class myAwesomePlugin
	{
		use \EarthAsylumConsulting\Traits\plugin_uninstall;
	}
	myAwesomePlugin::uninstall();

The uninstaller will, by default, remove any plugin [administrator options](#plugin-option-meta-data), plugin  transients, custom tables, and scheduled events. And it will work across all sites in a multi-site installation.

= Create your custom plugin class =

The plugin class is where you'll do your work according to your requirements. But you can do so using the {eac}Doojigger framework.

The file name should be {classname}.class.php (e.g. 'myAwesomePlugin.class.php') and the file must exist in the 'Plugin' folder of your namespace folder
```txt
/myAwesomePlugin/myAwesomeNamespace/Plugin/myAwesomePlugin.class.php
```

The first executable line in your plugin class must define the namespace as '{namespace}\Plugin'...

	namespace myAwesomeNamespace\Plugin;

Then declare your class...

	class myAwesomePlugin extends \EarthAsylumConsulting\abstract_context

\* note: *use of abstract_context_self and abstract_context_wp has been depreciated in version 2.0 in lieu of the `AutoUpdate` option in the plugin loader.*

To incorporate any of the {eac}Doojigger traits, simply use them next...

	use \EarthAsylumConsulting\Traits\standard_options;

Then define your constructor method...

	public function __construct(array $header) {...}

The `$header` variable passed to your constructor is the `$plugin_detail` variable defined in your plugin loader.

Within your constructor, you must call the parent constructor passing the `$header` array...

	parent::__construct($header);

Then you can register any plugin options ([option meta data](#plugin-option-meta-data)), activation/deactivation hooks, install and version-update hooks...

	$this->registerPluginOptions('plugin_settings', [ {option meta data} ]);

It is rare that plugin options are needed anywhere but within the plugin's administrator settings page. It is both optimal and good practice to register your settings within a test for the page using `$this->isSettingsPage()`...

	if ($this->isSettingsPage())
	{
		$this->registerPluginOptions('plugin_settings', [ {option meta data} ]);
	}

Or, preferably, you may use the just-in-time `options_settings_page` *action* that is fired right before rendering the settings html page.

	public function __construct(array $header)
	{
		if ($this->is_admin())
		{
			$this->add_action( "options_settings_page", array($this, 'admin_options_settings') );
		}
	}

	public function admin_options_settings()
	{
		$this->registerPluginOptions('plugin_settings', [ {option meta data} ]);
	}

Once all plugins are loaded (when WordPress triggers the `plugins_loaded` action), your plugin loader will...

+	Call the `loadAllExtensions()` method.
+	Fire the `myAwesomePlugin_extensions_loaded` action.
+	Call the `initialize()` method.
+	Fire the `myAwesomePlugin_initialize` action.
+	Call the `addActionsAndFilters()` method.
+	Call the `addShortcodes()` method.
+	Fire the `myAwesomePlugin_ready` action.

Your plugin may define any methods or actions to respond to these.

If your plugin class implements the `initialize()`, the `addActionsAndFilters()`, the `addShortcodes()`, and/or the `__destruct()` methods, it must call the corresponding parent method.


= Example plugin class =

    namespace myAwesomeNamespace\Plugin;

    class myAwesomePlugin extends \EarthAsylumConsulting\abstract_context
    {
        use \EarthAsylumConsulting\Traits\standard_options;

        public function __construct(array $header)
        {
            parent::__construct($header);

            $this->logAlways('version '.$this->getVersion().' '.wp_date('Y-m-d H:i:s',filemtime(__FILE__)),__CLASS__);

            if ($this->is_admin())
            {
				// Register plugin options
				$this->add_action( "options_settings_page",         array($this, 'admin_options_settings') );

                // When this plugin is activated
                register_activation_hook($header['PluginFile'],     array($this, 'admin_plugin_activated') );

                // When this plugin is deactivated
                register_deactivation_hook($header['PluginFile'],   array($this, 'admin_plugin_deactivated') );

                // When this plugin is installed ('myAwesomePlugin_version_installed')
                $this->add_action( 'version_installed',             array($this, 'admin_plugin_installed'), 10, 3);

                // When this plugin is updated ('myAwesomePlugin_version_updated')
                $this->add_action( 'version_updated',               array($this, 'admin_plugin_updated'), 10, 3 );
            }
        }

		public function admin_options_settings()
		{
			// from standard_options trait
			$this->registerPluginOptions('plugin_settings',$this->standard_options(
				[
					'siteEnvironment',
					'adminSettingsMenu',
					'uninstallOptions',
					'backupOptions',
					'restoreOptions'
				]
			));
		}

        public function initialize(): void
        {
            parent::initialize();
        }

        public function addActionsAndFilters(): void
        {
            parent::addActionsAndFilters();
        }

        public function addShortcodes(): void
        {
            parent::addShortcodes();
        }

        public function admin_plugin_activated()
        {
        }

        public function admin_plugin_deactivated()
        {
        }

        public function admin_plugin_installed($curVersion=null, $newVersion, $asNetworkAdmin)
        {
        }

        public function admin_plugin_updated($curVersion=null, $newVersion, $asNetworkAdmin)
        {
        }
    }


= Plugin Contextual Help =

Adding contextual help to your plugin, through the WordPress help system, is easy using the methods built into {eac}Doojigger...

In your plugin, enable the help system by...

	use \EarthAsylumConsulting\Traits\plugin_help;

In your `__constructor()` method, add the `options_settings_help` action...

	$this->add_action( 'options_settings_help', array( $this, 'admin_options_help') );

Then add the method to create your help content...

 	public function admin_options_help()
	{
		$this->addPluginHelpTab('help-tab-name','...help content...');
		$this->addPluginSidebarText('...sidebar content...');
		$this->addPluginSidebarLink('title','url');
	}

>	See [Contextual Help](/contextual-help/) (found in the */Extras/ContextualHelp/readme.txt* file) for more details.


= Plugin Option Meta Data =

Option Meta Data is an array of arrays defining options/settings that can be updated from the plugin's 'settings' page. Each option name is automatically prefixed with the plugin name (e.g. 'myAwesomePlugin').

An option is defined as:

    'my_option_name'   => array(
					'type'			=> 	'type: {input type}',
					'label'			=> 	'label: {field label}',
					'title'			=> 	'title: information text/html to be displayed',
    	            'options'       =>  array({'option',...}),
					'default'		=>	'default: {default option or value}',
					'info'			=> 	'info: Information/instructions',
                    'attributes'    =>  html attributes array ['name="value", name="value"'],
                    'help'			=> 'Contextual help'
            ),

>	See [Administrator Options](/options/) (found in the */Extras/OptionMetaData/readme.txt* file) for details on registering and using options in you plugin and extensions.


= Using Your Plugin =

{eac}Doojigger provides many useful methods and hooks (filters & actions) which can be accessed from your custom plugins or extensions, as well as from your theme functions or any code in WordPress.

>	See [Using {eac}Doojigger}](/using-doojigger/) (found in the /Extras/UsingDoojigger/readme.txt file) for details and examples


= Skeleton/Framework =

There is a complete, functional *myAwesomePlugin* skeleton of these examples in the *Extras/Plugins* folder of {eac}Doojigger that you can use to build your plugin by simply changing the namespace name, plugin name, and adding your own code.

There is also a fully functional *myFunctions* extension plugin in the *Extras/Extensions* folder intended to be used to replace (or augment) a custom theme `functions.php`, with optional custom stylesheet and javascript.


== Screenshots ==

1. My Awesome Plugin with My Awesome Extension
![myAwesomePlugin](https://swregistry.earthasylum.com/software-updates/eacdoojigger/assets/screenshot-9.png)

2. Upgrade Notice
![Upgrade Notice](https://swregistry.earthasylum.com/software-updates/eacdoojigger/assets/upgrade_notice.png)

3. Activation Error
![Activation Error](https://swregistry.earthasylum.com/software-updates/eacdoojigger/assets/activate_error.png)


== Other Notes ==

= Additional Information =

+	When installed and registered, {eac}Doojigger installs an autoloader in the 'mu_plugins' folder.
	This autoloader manages the loading of all abstract classes, traits, interfaces, and extensions.
	There should never be a need to copy a .php file from {eac}Doojigger.

