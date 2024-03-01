=== {eac}Doojigger, Using Doojigger ===
Plugin URI: 		https://eacDoojigger.earthasylum.com/
Author: 			[EarthAsylum Consulting](https://www.earthasylum.com)
Last Updated: 		24-Oct-2023
Contributors:       kevinburkholder
Requires EAC: 		2.4

Using the methods, filters, and actions provided by {eac}Doojigger.

== Description ==

{eac}Doojigger provides many useful methods and hooks which can be accessed from your custom plugins or extensions, as well as from your theme functions or any code in WordPress.

= Method Access =

Public methods in eacDoojigger.class.php (including abstract classes) may be accessed by using the global `eacDoojigger()` function.

	eacDoojigger()->{methodName}(...$arguments);

From within your derivative plugin, simply use `$this` and from within your extensions, you may use `$this` or `$this->plugin` *.

	$this->{methodName}(...$arguments);
	$this->plugin->{methodName}(...$arguments);

For example, to write an entry to the debugging log...

	eacDoojigger()->logDebug( $myVariable, 'Logging myVariable' );
	$this->logDebug( $myVariable, 'Logging myVariable' );
	$this->plugin->logDebug( $myVariable, 'Logging myVariable' );

To access a plugin option...

	eacDoojigger()->get_option( 'my_option_name' );
	$this->get_option( 'my_option_name' );
	$this->plugin->get_option( 'my_option_name' );

To invoke a plugin action...

	eacDoojigger()->do_action( 'my_action_name', $arg1, $arg2, ... );
	$this->do_action( 'my_action_name', $arg1, $arg2, ... );
	$this->plugin->do_action( 'my_action_name', $arg1, $arg2, ... );

To invoke a method in a plugin extension, use `callMethod()`...

	eacDoojigger()->callMethod( [ {extensionName}, {extensionMethodName} ], ...$arguments )
	$this->callMethod( [ {extensionName}, {extensionMethodName} ], ...$arguments )
	$this->plugin->callMethod( [ {extensionName}, {extensionMethodName} ], ...$arguments )

>	\* Version 2.0.1 of {eac}Doojigger...

+	Removed some method calling restrictions in extensions so that `$this->plugin` is no longer
	necessary as long as the method name is unique. Extensions can now simply use `$this->{methodName}(...$arguments);` to access
	eacDoojigger.class.php methods.

+	Invoking methods in extensions may be done by accessing the extension directly (by class name) via
	`$this->{extension_name}->{methodName}(...$arguments);`. For example, if you have an extension class named 'myAwesomeExtension',
	you may invoke its public methods using:

		$this->myAwesomeExtension->{methodName}(...$arguments);

		eacDoojigger()->myAwesomeExtension->{methodName}(...$arguments);


+	You may also test for and retrieve an extension object using `$this->isExtension(extension_name)`:

	if ($myExtension = $this->isExtension('myAwesomeExtension')) {
		$myExtension->{methodName}(...$arguments);
	}

	if ($myExtension = eacDoojigger()->isExtension('myAwesomeExtension')) {
		$myExtension->{methodName}(...$arguments);
	}


= Filters and Shortcodes =

{eac}Doojigger (and derivatives) provides a front-end filter and a shortcode that gives access to (nearly) all public methods in the plugin and extensions, WordPress options, and blog info.

The filter and shortcode name is the plugin class name ('eacDoojigger').
Arguments include:

+	method='{methodName}' or method='{extension.methodName}'
+	option='{optionName}'
+	bloginfo='{bloginfoName}'
+	args='...', to pass a list of arguments/values.
+	default='...', to set a default value.
+	index='...', to index an item from an array returned by the called method.

Filter Examples:

	\apply_filters('eacDoojigger', null, [ method='getVariable' args='session_manager' ]);	//  expecting session manager stored variable
	\apply_filters('eacDoojigger', null, [ method='session.sessionId' ]);		 			//  expecting session id from session extension
	\apply_filters('eacDoojigger', null, [ method='_SERVER' args='server_name' ]);		 	//  expecting server name from $_SERVER
	\apply_filters('eacDoojigger', null, [ method='getPluginValue' args='PluginSlug' ]);	//  expecting plugin slug
	\apply_filters('eacDoojigger', null, [ option='siteEnvironment' ]);		 				//  expecting siteEnvironment
	\apply_filters('eacDoojigger', null, [ option='blogdescription' ]);		 				//  expecting WordPress blogdescription
	\apply_filters('eacDoojigger', null, [ option='woocommerce_cybersource_credit_card_settings' index='description' default='not installed' ]);	//  expecting cybersource description
	\apply_filters('eacDoojigger', null, [ bloginfo='name' ]);		 						//  expecting WordPress Site Title

Shortcode Examples:

	['eacDoojigger' method='getVariable' args='session_manager']	//  expecting session manager stored variable
	['eacDoojigger' method='session.sessionId']		 				//  expecting session id from session extension
	['eacDoojigger' method='_SERVER' args='server_name']		 	//  expecting server name from $_SERVER
	['eacDoojigger' method='getPluginValue' args='PluginSlug']		//  expecting plugin slug
	['eacDoojigger' option='siteEnvironment']		 				//  expecting siteEnvironment
	['eacDoojigger' option='blogdescription']		 				//  expecting WordPress blogdescription
	['eacDoojigger' option='woocommerce_cybersource_credit_card_settings' index='description' default='not installed']	//  expecting cybersource description
	['eacDoojigger' bloginfo='name' ]	 							//  expecting WordPress Site Title


= Filters, Options, and Transients =

When using class methods to access filters and actions, options, and transients, all names are prefixed with the plugin name ('eacDoojigger_*).
These functions are extended wrappers around WordPress methods...

+	`$this->add_filter(...)` rather than `add_filter(...)`
+	`$this->add_option(...)` rather than `add_option(...)`
+	`$this->add_network_option(...)` rather than `add_network_option(...)`
+	`$this->add_site_option(...)` rather than `add_site_option(...)`
+	`$this->set_transient(...)` rather than `set_transient(...)`
+	`$this->set_site_transient(...)` rather than `set_site_transient(...)`

>	See the [Multi-Site Network](/eacdoojigger/#multi-site-network) section for other important differences.

= Table Names =

For custom table names, use `$this->prefixTableName('my_table_name')` to ensure uniqueness of your table name(s). This will prefixed your table name with the lower-case plugin name ('eacdoojigger_*).

= Front-end, Back-end, Network Determination =

In WordPress, ajax requests always return `true` for `is_admin()` and `false` for `is_network_admin()`. {eac}Doojigger digs a little deeper and returns the correct response for `$this->is_admin()` and `$this->is_network_admin()`. It also sets static variables to make repeated checks faster as well as including a few additional methods...

| Static variable / method 		| Value / response															|
| ----------------------------- | ------------------------------------------------------------------------- |
| `static::CONTEXT_IS_BACKEND`	| Set to true when request is for/from an administrator (backend) page 		|
| `static::CONTEXT_IS_FRONTEND`	| Set to true when request is not for/from an administrator (backend) page  |
| `static::CONTEXT_IS_NETWORK`	| Set to true when request is for/from a network administrator page 		|
| `$this->is_backend()`			| Returns static::CONTEXT_IS_BACKEND  										|
| `$this->is_frontend()`		| Returns static::CONTEXT_IS_FRONTEND  										|
| `$this->is_admin()`			| Set to static::CONTEXT_IS_BACKEND on load, can be overriden 				|
| `$this->is_network_admin()`	| Set to static::CONTEXT_IS_NETWORK on load, can be overriden 				|


== How To... ==

= Table of Contents =

+	[About the {eac}Doojigger File System Extension](#about-the-eacdoojigger-file-system-extension)
	+ 	Access the WordPress file system via FTP/SSH with secure, compatible file permissions.
+	[Using the {eac}Doojigger Installer](#using-the-eacdoojigger-installer)
	+	Install a Must-Use plugin file, theme file, or other file using the WP_Filesystem API.
+	[Using Conditional Traits in PHP](#using-conditional-traits-in-php)
	+	 Conditionally load backend (or frontend) code using traits.
+	[Clone an {eac}Doojigger Extension](#clone-an-eacdoojigger-extension)
	+	Use an {eac}Doojigger extension in your derivative plugin (myAwesomPlugin).
+	[Using administrator option field methods](#using-administrator-option-field-methods)
	+	Add input fields to your custom screens.
+	[Using administrator contextual help methods](#using-administrator-contextual-help-methods)
	+	Add contextual help to your custom screens.
+	[Validate Registration & Licensing](#validate-registration-amp-licensing)
	+	Simple methods for checking the license level of a registered plugin.


= About the {eac}Doojigger File System Extension =

To properly install extensions and create folders & files, {eac}Doojigger uses the WordPress file access sub-system - [WP_Filesystem](https://developer.wordpress.org/reference/functions/wp_filesystem/). This system provides access to the web server files using FTP (file transfer protocol), FTPS (FTP over ssl), or SSH (secure shell or SFTP).

These protocols create files and folders using your ftp user name rather than the default web server user name (e.g. "www"", "www-data", "nobody", "apache") which should maintain proper and secure file permissions.

WordPress uses the WP_Filesystem when installing updates, plugins, and themes. You have probably seen the "Connection Information" form when applying updates. Unlike WordPress, {eac}Doojigger retains your connection information, in a secure, encrypted state, to be used when needed. Your secure credentials will be used by WordPress so you never see the "Connection Information" form again.

{eac}Doojigger provides two methods for accessing the WP_Filesystem:

The first (load_wp_filesystem), will initiate the "Connection Information" form (if $useform is not false) when required and either return the file system object or false.

	public function load_wp_filesystem($useForm = false, string $notice = '', array $args = [])

+	$useForm (false) when truthy, prompt for credentials if needed
+	$notice ('') display an 'admin notice' message before the form
+	$args ([]) [request_filesystem_credential](https://developer.wordpress.org/reference/functions/request_filesystem_credentials/) arguments (override defaults)

The second (link_wp_filesystem), provides a standard WordPress administrator notice, if the "Connection Information" form is required, with a link to access that form. This is less intrusive then load_wp_filesystem() and always returns false unless and until the user clicks the link.

	public function link_wp_filesystem($useForm = true, string $notice = '', array $args = [])

+	$useForm (true) when truthy, display notice with link
+	$notice ('') display an 'admin notice' message before the form
+	$args ([]) [request_filesystem_credential](https://developer.wordpress.org/reference/functions/request_filesystem_credentials/) arguments (override defaults)


Examples:

Redirects to the "Connection  Information" form so the user can enter their FTP credentials. If credentials are already available, returns the WP_Filesystem object in $fs.

	$fs = $this->fs->load_wp_filesystem(true,
		'{...something...} requires WordPress file system access.'
	);

Displays an admin notice at the top of an administrator page providing a link to the  "Connection  Information" form. If credentials are already available, returns the WP_Filesystem object in $fs.

	$fs = $this->fs->link_wp_filesystem(true,
		'{...something...} requires WordPress file system access.'
	);

Returns either false (we don't have FTP credentials), or the WP_Filesystem object.

	$fs = $this->fs->load_wp_filesystem();

In all cases, $fs will either be false or the WP_Filesystem object...

	if ($fs) {
		// we can now use WP_Filesystem methods
		$fs->copy('thisfile','thatfile');
	}

Note that once FTP credentials are entered via the "Connection  Information" form, {eac}Doojigger saves them (encrypted) so the form is never needed again. FTP credentials can also be added to wp-config.php so that the "Connection  Information" form is not required.

- - -
- - -


= Using the {eac}Doojigger Installer =

The installer extension is primarily intended to install a PHP script into the WordPress "must use plugins" folder (WPMU_PLUGIN_DIR), however, it may be used to install files in any folder within the WordPress directory structure.

In it's simplest form, you create an array of options which define the installer parameters (source, destination) when invoking the installer...

	$this->installer->invoke('install',false,
		'title'			=> 'My Awesome MU Plugin',	// title, false=silent
		'sourcePath'	=> dirname(__FILE__),		// from this directory (defults to plugin dir)
		'sourceFile'	=> 'myawesomemu.class.php',	// source file to copy
		'targetPath'	=> WPMU_PLUGIN_DIR,			// destination folder (default to WPMU_PLUGIN_DIR)
		'targetFile'	=> 'myawesomemu.php',		// destination file (defaults to sourceFile)
		'connectForm'	=> true,					// allow automatic redirect to file system connection form
	);

&bull; The first parameter ($action) is one of 'install', 'update', uninstall', or 'delete'. 'install' & 'update', as well as 'uninstall' and 'delete' do the same thing but display the action verb to the user, assumedly being more intuitive.

There are shortcuts for the first parameter:

	$this->installer->install(...);
	$this->installer->update(...);
	$this->installer->uninstall(...);
	$this->installer->delete(...);

&bull; The second parameter ($installMethod) is used to pass an installer function used only if we must redirect to the file system "Connection Information" form. On return, this method will be called (and must be callable as a static method or an extension method). It is usually the method that originally invoked the installer, so if needed, it should be `__METHOD__` or `[__CLASS__,__FUNCTION__]`.

The only time this is needed is if you're doing some pre or post processing around the installer.

	public static function my_installer() {
		$installOptions = array(...);
		// pre-process
		$this->installer->install(__METHOD__,$installOptions);
		// post-process
	}

There are also actions that may accomplish the same ('eacDoojigger_pre_install' and 'eacDoojigger_post_install'), or the '$onSuccess' callback parameter (below).

More often than not, this parameter can be, and should be, set to false.

&bull; The third parameter ($installOptions) is the array of installer parameters (shown above) defining what is to be installed and where.

_title_  is used to display a notice to the user when the action is completed or may be false to suppress notices.

_sourceFile_ may be a single file name or a wildcard to handle multiple files (in which case, targetFile is not used).

If the installer requires file system credentials, these installer parameters are saved, then we redirect to the "Connection Information" form, then back to the originating page to retrieve the parameters and restart the installer process.

&bull; A fourth parameter ($onSuccess) may be used to set a callback called immediately after your file is successfully installed but before determining if the install is successful. So if your callback function returns false, the installed file(s) are removed and the installation fails.

	public static function my_installer() {
		$installOptions = array(...);
		$this->installer->install(__METHOD__,$installOptions, function($action,$installOptions)
			{
				// do something after file is installed
				return true;
			}
		);
	}

Installers can also be queued to be run sequentially, either when invoked in your code or automatically when navigating to another page.

	$this->installer->enqueue($action,false,$installOptions_a);
	$this->installer->enqueue($action,false,$installOptions_b);
	$this->installer->invoke();	// or on the next page-load.


A real-world example using enqueue with an installer method...

	$this->add_action( 'version_updated', array( $this, 'admin_plugin_updated' ), 10, 3 );

	public function admin_plugin_updated($curVersion, $newVersion, $asNetworkAdmin)
	{
		$this->installer->enqueue('update',[$this,'install_autoloader']);
		$this->installer->invoke();	// or on the next page-load.
	}

	public function install_autoloader($action='install'): void
	{
		$this->installer->invoke($action,[__CLASS__,__FUNCTION__],
			[
				'title'			=> 'The {eac}Doojigger Autoloader',
				'sourcePath'	=> $this->pluginHeader('VendorDir').'/Utilities',
				'sourceFile'	=> 'eacDoojiggerAutoloader.php',
				'targetFile'	=> 'eacDoojiggerAutoloader.php',
				'return_url'	=> remove_query_arg('fs'),	// force reload after install
			],
			function($action,$installOptions): bool			// callback onSuccess
			{
				// do stuff after installing
				return true;
			}
		);
	}

Because the install is enqueued, it is dequeued when invoked. When dequeued, the installer method (install_autoloader) is called and the update runs.

Another real-world example that installs a "must use" plugin with support scripts in a sub-folder...

	$this->installer->enqueue($action,false,
		[
			'title'			=> false,
			'sourcePath'	=> $this->pluginHeader('VendorDir').'/Utilities',
			'sourceFile'	=> '*.php',
			'targetPath'	=> WPMU_PLUGIN_DIR.'/eacDoojiggerEnvironment',
		]
	);
	$this->installer->invoke($action,false,
		[
			'title'			=> 'The {eac}Doojigger Environment Switcher',
			'sourcePath'	=> $this->pluginHeader('VendorDir').'/Utilities',
			'sourceFile'	=> 'eacDoojiggerEnvironment.class.php',
			'targetFile'	=> 'eacDoojiggerEnvironment.php',
		]
	);

The first action enqueues the install of a sub-folder (eacDoojiggerEnvironment) within the 'mu_plugins' folder. The second action installs the primary script (eacDoojiggerEnvironment.php) into the "mu_plugins" folder then dequeues and runs the first action (the invoke'd action runs immediately, before the  enqueue'd action).

- - -
- - -


= Using Conditional Traits in PHP =

I've found that within {eac}Doojigger, extensions, and any plugin, that a substantial amount of code is dedicated to backend or administrator functions. This doesn't bode well on the frontend as all of this unneeded code is loaded.

To alleviate this, we can use make-shift conditional traits...

1. Create a small trait file: `myAwesomeTraits.trait.php`
```php
	<?php
	namespace myAwesomeNamespace;

	if ( ! is_admin() )
	{
		trait myAwesomeTraits {}
	}
	else require "myAwesomeTraits.admin.php";
```

2. Create your administrator trait file: `myAwesomeTraits.admin.php`
```php
	<?php
	namespace myAwesomeNamespace;

	trait myAwesomeTraits
	{
		// your administration methods
		public function myAwesomeAdmin()
		{
		}
	}
```

3. Include your trait file in your plugin or extension
```php
	require "myAwesomeTraits.trait.php";

	class myAwesomePlugin extends \EarthAsylumConsulting\abstract_context
	{
		/**
		 * @trait myAwesomeTraits, loads only admin methods
		 */
		use \myAwesomeNamespace\myAwesomeTraits;
	}
```

Using this method you can control what code gets loaded for backend (or frontend) use.

- - -
- - -


= Clone an {eac}Doojigger Extension =

Let's say you want to use the session manager included with {eac}Doojigger as an extension to your own plugin (myAwesomePlugin).

Create `session_manager.extension.class.php` in your myAwesomePlugin `Extensions` folder:

	namespace myAwesomeNamespace\Extensions;

	if ( class_exists('\EarthAsylumConsulting\Extensions\session_extension') )
	{
		return new \EarthAsylumConsulting\Extensions\session_extension($this);
	}

This works for extensions included with {eac}Doojiger but what about extension plugins?
Let's try eacMetaPixel to add Facebook tracking to your plugin...

(*almost the same but the auto-loader may not know where to find the extension if it hasn't been loaded yet.*)

Create `metapixel.extension.php` in your myAwesomePlugin `Extensions` folder:

	namespace myAwesomeNamespace\Extensions;

	if ( class_exists('\EarthAsylumConsulting\Extensions\metapixel_extension') )
	{
		return new metapixel_extension($this);
	}
	if ( file_exists(WP_PLUGIN_DIR.'/eacmetapixel/Extensions/metapixel.extension.php') )
	{
		return require(WP_PLUGIN_DIR.'/eacmetapixel/Extensions/metapixel.extension.php');
	}

This will load the metapixel extension, if it is installed, whether or not it is activated.

In both, the extension will be loaded by and will extend your plugin. Extension settings will be added to your options page and those settings will be stored with your plugin settings, prefixed with your plugin name.

- - -
- - -


= Using administrator option field methods =

Creating custom post types or taxonomies?

There are several methods used by {eac}Doojigger to create the administrator options screens. These methods may also be used outside of the plugin settings page for which they were created.

>	Note: These examples should only be used when on your custom screen. You can ensure that by checking the current screen id: `if (get_current_screen()->id !== 'edit-my-screen-id') return;`

These `html_input` methods are made available by using the html_input_fields trait in your plugin.

	use \EarthAsylumConsulting\Traits\html_input_fields;

- - -

A simple example is to first create an array of field definitions similar to the plugin and extension options (see [{eac}Doojigger: Administrator Options](/options/#option-meta-data))...

	$myFormOptions =
	[
			'registrar_name'		=> array(
								'type'		=> 	'text',
								'label'		=> 	'Registrar Name',
							),
			'registrar_phone'		=> array(
								'type'		=> 	'tel',
								'label'		=> 	'Registrar Telephone',
							),
			'registrar_contact'		=> array(
								'type'		=> 	'email',
								'label'		=> 	'Registrar Support Email',
							),
			'registrar_web'		=> array(
								'type'		=> 	'url',
								'label'		=> 	'Registrar Web Address',
							),
	];


- - -
__*html_input_style()*__
- - -

To incorporate the css style declarations needed for the embedded html, use the `html_input_style()` method...

	add_action( 'admin_enqueue_scripts', array($this, 'html_input_style') );

- - -
__*html_input_block()*__
- - -

Then, where you want the form fields displayed, add the grid container and each of the fields, including label and input elements, using the `html_input_block()` method...


	echo "<div class='settings-grid-container'>\n";
	foreach ($myFormOptions as $optionKey => $optionData)
	{
		$optionValue = ?; 	// get the current value for the field
		echo $this->html_input_block($optionKey, $optionData, $optionValue);
	}
	echo "</div>\n";

- - -
__*html_input_sanitize()*__
- - -

To sanitize these fields when your form is submitted, use the `html_input_sanitize()` method...

	foreach ($myFormOptions as $optionKey => $optionData)
	{
		if (array_key_exists($optionKey,$_POST) )
		{
			$value = $this->html_input_sanitize($_POST[$optionKey], $optionKey, $optionData);
			if ($_POST[$optionKey] == $value)
			{
				// input is valid (passed sanitization), do something with it
			}
			else
			{
				// input is invalid, let the user know
				$this->add_option_error($optionKey,
					sprintf("%s : The value entered does not meet the criteria for this field.",$optionData['label'])
				);
			}
		}
	}

- - -
__*html_input_validate()*__
- - -

The html_input_validate method uses and calls the field's 'validate' callback method (if set).

	foreach ($myFormOptions as $optionKey => $optionData)
	{
		if (array_key_exists($optionKey,$_POST) )
		{
			$value = $this->html_input_validate($_POST[$optionKey], $optionKey, $optionData);
		}
	}

- - -
__*html_input_section()*__
- - -

For a more advanced layout with multiple blocks or sections of input fields...

	$myFormSections =
	[
		'registrar_contact' 	=>
		[
				'registrar_name'		=> array(
									'type'		=> 	'text',
									'label'		=> 	'Registrar Name',
								),
				'registrar_phone'		=> array(
									'type'		=> 	'tel',
									'label'		=> 	'Registrar Telephone',
								),
				'registrar_contact'		=> array(
									'type'		=> 	'email',
									'label'		=> 	'Registrar Support Email',
								),
				'registrar_web'		=> array(
									'type'		=> 	'url',
									'label'		=> 	'Registrar Web Address',
								),
		],
		'registration_defaults' =>
		[
			// ...
		],
		'license_limitations'	=>
		[
			// ...
		],
	];

Add the fields in each section with a section header and fieldset (`html_input_section()`)...

	foreach ($myFormSections as $groupName => $myFormOptions)
	{
		echo $this->html_input_section($groupName, $myFormOptions);
		foreach ($myFormOptions as $optionKey => $optionData)
		{
			$optionValue = ?; 	// get the current value for the field
			echo $this->html_input_block($optionKey, $optionData, $optionValue);
		}
		echo "</fieldset>\n";
		echo "</section>\n";
	}

Then sanitize the fields from each section using `html_input_sanitize()` when the form is submitted...

	foreach ($myFormSections as $groupName => $myFormOptions)
	{
		foreach ($myFormOptions as $optionKey => $optionData)
		{
			if (array_key_exists($optionKey,$_POST) )
			{
				$value = $this->html_input_sanitize($_POST[$optionKey], $optionKey, $optionData);
				if ($_POST[$optionKey] == $value)
				{
					// input is valid (passed sanitization), do something with it
				}
				else
				{
					// input is invalid, let the user know
					$this->add_option_error($optionKey,
						sprintf("%s : The value entered does not meet the criteria for this field.",$optionData['label'])
					);
				}
			}
		}
	}

- - -
__*add_option_error()*__
- - -

The `add_option_error()`, `add_option_warning()`, `add_option_info()`, and `add_option_success()` methods are similar to the WordPress `add_settings_error()` function with a slightly more advanced API that allows administrator notifications to be added anywhere on the page (befor the footer) and to survive a page reload or redirect.

- - -
__*Convert vertical section headers to horizontal tabs*__
- - -

Easily change a long vertical page with multiple input field sections into a tab layout with each section as a tab.

First add an element with a class of 'tab-container' where you want the tabs...

```html
<nav class='tab-container'></nav>
```

Next add a 'true' boolean to the `html_input_style()` method...

	add_action( 'admin_enqueue_scripts', function()
		{
			$this->html_input_style(true);
		}
	);

This will include the css and javascript needed for tab functionality.

Additionally, you can pass an element selector which will have its visibility style changed to 'visible'.

	$this->html_input_style(true,'table.form-table');


- - -
__*Screen Shot*__
- - -

>	These examples where derived from the [Software taxonomy](https://swregistry.earthasylum.com/software-taxonomy/) custom taxonomy extension used by [{eac}SoftwareRegistry](https://swregistry.earthasylum.com/).

![{eac}SoftwareRegistry Software Product](https://swregistry.earthasylum.com/software-updates/eacdoojigger/assets/software-taxonomy.png)

- - -
- - -


= Using administrator contextual help methods =

Taking the examples above and adding contextual help when adding fields is very simple.

First, in your constructor, add something like this to render the help screen.

	add_action( 'current_screen',		function($screen)
		{
			if ($screen->id == 'edit-' . 'my-awesome-screen')
			{
				$this->addPluginHelpTab('My Awesome Screen', $content, ['My Awesome Screen','open']);
				$this->plugin_help_render($screen);
			}
		}
	);

Then where you render your fields, render the field-level help with `html_input_help()`

	foreach ($myFormSections as $groupName => $myFormOptions)
	{
		echo $this->html_input_section($groupName, $myFormOptions);
		foreach ($myFormOptions as $optionKey => $optionData)
		{
			$this->html_input_help('My Awesome Screen', $optionKey, $optionData);
			if ($optionData['type'] == 'help') continue;

			$optionValue = ?; 	// get the current value for the field
			echo $this->html_input_block($optionKey, $optionData, $optionValue);
		}
		echo "</fieldset>\n";
		echo "</section>\n";
	}

>	See [Providing Contextual Help](/contextual-help/) for more details.

- - -
- - -


= Validate Registration & Licensing =


__ Simple methods for checking the license level of a registered plugin that uses the [{eac}SoftwareRegistry](https://swregistry.earthasylum.com) [Distribution Kit](https://swregistry.earthasylum.com/software-registry-sdk/) __

	/**
	 * is license L2 (basic) or better
	 *
	 * @return	bool
	 */
	public function isBasicLicense(): bool
	{
	 	return $this->isRegistryValue('license', 'L2', 'ge');
	}

	/**
	 * is license L3 (standard) or better
	 *
	 * @return	bool
	 */
	public function isStandardLicense(): bool
	{
	 	return $this->isRegistryValue('license', 'L3', 'ge');
	}

	/**
	 * is license L4 (professional) or better
	 *
	 * @return	bool
	 */
	public function isProfessionalLicense(): bool
	{
	 	return $this->isRegistryValue('license', 'L4', 'ge');
	}

	/**
	 * is license L5 (enterprise) or better
	 *
	 * @return	bool
	 */
	public function isEnterpriseLicense(): bool
	{
	 	return $this->isRegistryValue('license', 'L5', 'ge');
	}
