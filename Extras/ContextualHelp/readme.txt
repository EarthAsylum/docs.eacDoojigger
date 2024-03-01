=== {eac}Doojigger, Contextual Help ===
Plugin URI: 		https://eacDoojigger.earthasylum.com/
Author: 			[EarthAsylum Consulting](https://www.earthasylum.com)
Last Updated: 		10-Nov-2022
Contributors:       kevinburkholder
Requires EAC: 		2.0

Using contextual help in {eac}Doojigger derivative plugins and extensions.

== Description ==

{eac}Doojigger has several methods available to easily add contextual help to your plugin administration screen and it automatically adds field-level help when rendering your options based on your [option meta data](/options/).

To use contextual help, first, when defining your plugin class, `use` the plugin help trait...

	class myAwesomePlugin extends \EarthAsylumConsulting\abstract_context
	{
		/**
		 * @trait methods for contextual help tabs
		 */
		use \EarthAsylumConsulting\Traits\plugin_help;

		...
	}

Second, in your class _constructor method, add the action to call your rendering function...

	public function __construct(array $header)
	{
		parent::__construct($header);

		if ($this->is_admin())
		{
			// Register plugin options
			$this->add_action( "options_settings_page", 		array($this, 'admin_options_settings') );

			// Add contextual help
			$this->add_action( 'options_settings_help', 		array($this, 'admin_options_help') );
		}
	}

>	In your plugin extensions, you use the same `options_settings_help` action but omit the plugin help trait (only the plugin should enable the help system).


Third, add your rendering function to add help tab(s) and (optionally) sidebar content...

	public function admin_options_help()
	{
		ob_start();
		? >;
			Lorem ipsum dolor sit amet, consectetur adipiscing elit,
			sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
			Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
			Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
			Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
		< ? php

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

You can also control when (on what option tab) your contextual help will appear by checking the current tab name...

	public function admin_options_help()
	{
		if ($this->plugin->isSettingsPage('General'))
		{
			//  content only on the 'General' tab
		}
	}


Field-level help is automatically rendered from your [options meta data](/options/).


== Help Methods ==

Enable or diisable the help system (and field-level help) or check if help system is enabled:

	$this->pluginHelpEnabled(bool [,bool]);
	if ( $this->pluginHelpEnabled() ) {...}



Add help content:

	$this->addPluginHelpTab(
		'tab_name',
		'content_html',
		'content_header', 	// optional <details> heading
		priority			// optional, default = 10
	);

+ 	`content_html` may be a single string of text/html or an array of strings to be rendered as paragraphs.
+	`content_header` is an optional string to be rendered in a `<details><summary>` tag containing content_html.
+	`content_header` may be an array of ['...header...','open'] to open the `<details>` tag.


Add help sidebar links:

	$this->addPluginSidebarLink(
		'title',
		'link',
		'tooltip'			// optional
	)


Add help sidebar text:

	$this->addPluginSidebarText(
		'content_html'
	);

+	`content_html` may be a single string of text/html or an array of strings to be rendered as paragraphs.


Add field-level help (may used be after help tabs are rendered):

>	This method is automatically called when rendering your option fields using option['help'] or option['title']+option['info']. If option['help'] is an array, the array key is used as the help tab_name.

	$this->addPluginHelpField(
		'tab_name',
		'label',
		'content_html'
	);

+	`tab_name` should match 'tab_name' of addPluginHelpTab() otherwise reverts to first help tab.
+	`tab_name` may be an array of possible addPluginHelpTab() 'tab_name's, using the first match.
+	`content_html` may be a single string of text/html or an array of strings (not rendered as paragraphs).


Force all field-level help on the first help tab:

	$this->plugin_help_fields_on_first_tab = true;		// default is false


== Other Screens ==

If your plugin or extension includes screen(s) other than the administrator settings page (e.g. custom posts or taxonomies), you can still easily make use of the {eac}Doojigger contextual help framework.

= Rendering the help screen =

Add an action to
+	Verify that you're on your custom screen.
+	Add help screen content.
+	Render the help screen.

```
	add_action( 'current_screen', array($this, 'render_help'));

	public function render_help($screen)
	{
		if ($screen->id == 'edit-' . 'my-awesome-screen')
		{
			//  add a tab - tab_name , content , <details> heading (optional), priority (optional)
			$this->addPluginHelpTab('My Awesome Screen', $content, ['My Awesome Screen','open']);

			$this->plugin_help_render($screen);
		}

	}
```

= Add field-level contextual help =

* This assumes you're USEing the html_input_fields trait. See [Using administrator option field methods](/using-doojigger/#using-administrator-option-field-methods)

Where desired, add field level help

		$this->plugin->html_input_help('My Awesome Screen', 'field-name', [
				'label'	=>	'Field Name',
				'help'	=>	'help content.'
			]
		);


== Screenshots ==

1. My Awesome Plugin Contextual Help
![ContextualHelp](https://swregistry.earthasylum.com/software-updates/eacdoojigger/assets/screenshot-10.png)
