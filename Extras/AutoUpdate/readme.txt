=== {eac}Doojigger, Plugin Auto-Updating ===
Plugin URI: 		https://eacDoojigger.earthasylum.com/
Author: 			[EarthAsylum Consulting](https://www.earthasylum.com)
Last Updated: 		15-Nov-2022
Contributors:       kevinburkholder
Requires EAC: 		2.0

{eac}Doojigger derivative plugins and custom extension plugins may provide automatic updating similar to WordPress-hosted plugins.

== Description ==

= Summary =

WordPress hosted plugins provide updating functionality automatically. Whenever a new version of a plugin is updated in the WordPress repository, update notifications are seen in your WordPress dashbord on the plugins page.

You can provide the same functionality with your externally or self hosted plugin with a few easy changes.

See [Screenshots](#screenshots)

= Plugin Header =

Both [derivative plugins](https://eacdoojigger.earthasylum.com/derivatives/) and [extension plugins](https://eacdoojigger.earthasylum.com/extensions/) must provide the *Update URI* and *Version* in the main plugin file header (myAwesomePlugin.php or myAwesomeExtension.php).

As well, other information should be included in the plugin header...

	/**
	 * Plugin Name:			my plugin/extension name
	 * Description:			my plugin/extension description (up to 150 characters)
	 * Version:				1.0.0 (major.minor.patch)
	 * Requires at least:	5.5.0 (Minimum WordPress version)
	 * Tested up to:		6.1 (currently tested WordPress version)
	 * Requires PHP:		7.2 (minimum PHP version)
	 * Plugin URI:			url to your web page for this plugin/extension
	 * Update URI: 			url to the JSON file for this plugin/extension
	 * Author:				your name
	 * Author URI:			your website/profile
	 */

See [WordPress Plugin Header Requirements](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/)

The *Update URI* must return the *[JSON Updater Object](#json-updater-object)* that provides the required information for WordPress to detect and automate the updating of your plugin.


= Derivative Plugin Auto-Updating =

Derivative Plugins can auto-update simply by including the aforementioned header values and adding 'AutoUpdate' to the `$plugin_detail` array in the main plugin file...

In your main plugin file (myAwesomePlugin.php)
1.	Add (at least) *Update URI* and *Version* to the header.
2. 	Add `'AutoUpdate' => 'self'` to the $plugin_detail array.

*myAwesomePlugin.php*

    /**
	 * Plugin Name:		My Awesome Plugin
	 * Description:		EarthAsylum Consulting {eac}Doojigger Awesome derivative
	 * Update URI: 		https://myawesomeserver.com/myAwesomePlugin.json
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
					'AutoUpdate'	=> 'self', // automatic update 'self' or 'wp'
                ];
        } // myAwesomePlugin
    } // namespace

    namespace // global scope
    {
        defined( 'ABSPATH' ) or exit;

        \myAwesomeNamespace\myAwesomePlugin::loadPlugin(true);
    }


= Extension Plugin Auto-Updating =

Extension Plugins can auto-update simply by including the aforementioned header values and adding a single line of code...

In your main plugin file (myAwesomeExtension.php)
1.	Add (at least) *Update URI* and *Version* to the header.
2.	Add `myAwesomePlugin::loadPluginUpdater(__FILE__,'self');` inside the 'load_extensions' filter.

*myAwesomeExtension.php*

	/**
	 * Plugin Name: My Awesome Extensions
	 * Description:		EarthAsylum Consulting {eac}Doojigger Awesome extension
	 * Update URI: 	https://myawesomeserver.com/myAwesomeExtension.json
	 * Version:	1.0.0
	 */
	namespace myAwesomeNamespace;

	class myAwesomeExtension
	{
		public function __construct()
		{
			add_filter( 'myAwesomePlugin_load_extensions', function($extensionDirectories)
				{
					myAwesomePlugin::loadPluginUpdater(__FILE__,'self'); // automatic update 'self' or 'wp'

					$extensionDirectories[ plugin_basename( __FILE__ ) ] = [plugin_dir_path( __FILE__ ).'/Extensions'];
					return $extensionDirectories;
				}
			);
		}
	}
	new \myAwesomeNamespace\myAwesomeExtension();

\* Note: myAwesomePlugin::loadPluginUpdater(...) is referencing the plugin this extension is extending. If you are writing an extension for some other plugin (rather than myAwesomePlugin), you would use that plugin's class name


== Screenshots ==

1.	Upgrade Notice
![Upgrade Notice](https://swregistry.earthasylum.com/software-updates/eacdoojigger/assets/upgrade_notice.png)

2.	View Details
![View Details](https://swregistry.earthasylum.com/software-updates/eacdoojigger/assets/view_details.png)


== JSON Updater Object ==

The JSON updater object is a simple text file in JSON format that provides the needed information for both the {eac}Doojigger plugin_updater trait and for WordPress.

```js
{
	"plugin":			"plugin-directory/plugin-file-name.php",
	"name":				"plugin title",
	"homepage":			"url to your web page for this plugin/extension",
	"description":		"short description (up to 150 characters)",
	"version":			"1.0.0",
	"last_updated":		"2022-08-31 01:54:36 +00:00",
	"requires":			"5.5.0",
	"tested":			"6.0",
	"requires_php":		"7.2",
	"download_link":	"https://myawesomeplugin.zip",
	"donate_link":		"paypal.me or something",
	"author":			"<a href='https://www.your-website.com'>Your Name</a>",
	"sections":			{
		"description":		"long description/documentation",
		"installation":		"installation instructions",
		"faq":				"frequently asked questions",
		"changelog":		"recent changes":,
		"upgrade_notice":	"notice when upgrading",
		"screenshots":		"see below"
	},
	"contributors":		{"id": {"display_name":"...","profile":"...","avatar":"..."}},
	"banners":			{
		"low":				"url_to_lowres_(772x250)_banner.jpg",
		"high":				"url_to_highres_(1544x500)_banner.jpg"
	},
	"icons": 			{
		"low": 				"url_to_lowres_icon-128x128.png",
		"high": 			"url_to_highres_icon-256x256.png"
	}
}
```

= Requirements =

| You *must* include | You *should* include | If *omitted*					 |
| ------------------ | -------------------- | ------------------------------ |
| *plugin*			 | *name*				| *name* defaults to the directory of "plugin" |
| *version*			 | *description*		| *requires* defaults to 5.5.0   |
| *download_link*	 | *last_updated*		| *tested* defaults to requires  |
| 					 | *requires*			| *requires_php* defaults to 7.2 |
| 					 | *tested*				| 								 |


= Sections =

Each of the *sections* elements are optional (you should include at least "description") and may use HTML markup.

= Screenshots =

*screenshots* should look something like...

```js
"screenshots":	"<ol>
				<li><p>screen shot title
				<img src='https://www.your-website.com/screenshot-1.png' alt='screen shot title' />
				</p></li>
				</ol>",
```

= Upgrade Notice =

If *upgrade_notice* is present, it will be displayed on the WordPress *Plugins* and the dashboard *Updates* screen when an update is available.

The dashboard *Updates* screen will strip out any html tags.

= Contributors =

The *contributors* element is typically used for those who have a wordpress.org account...

```js
"contributors":{
	"your_wp_id":{
		"display_name":	"Your Name",
		"profile":		"https://profiles.wordpress.org/your_wp_id/",
		"avatar":		"https://www.gravatar.com/avatar/your_gravitar_id"
	}
},
```

However, you may populate this with non-wp names and urls or omit "profile" and/or "avatar".


= JSON Example =

__myAwesomePlugin.json__

(included in the *Extras/AutoUpdate* folder distributed with {eac}Doojigger)

```js
{
    "plugin": "myAwesomePlugin/myAwesomePlugin.php",
    "name": "My Awesome Plugin",
    "homepage": "https://myawesomeserver.com/myAwesomePlugin/",
    "description": "My Awesome Plugin is a really awesome plugin.",
    "version": "1.0.1",
    "last_updated": "13-Sep-2022",
    "requires": "5.5.0",
    "tested": "6.0",
    "requires_php": "7.2",
    "download_link": "https://myawesomeserver.com/myAwesomePlugin/myAwesomePlugin.zip",
    "donate_link": "",
    "author": "<a href=\"https://www.myawesomeserver.com\">Me</a>",
    "contributors": {
        "_me_": {
            "display_name": "Me",
            "profile": "https://profiles.wordpress.org/_me_/",
            "avatar": "https://www.gravatar.com/avatar/_mygravitarcode_"
        }
    },
    "sections": {
        "description": "<p><em>My Awesome Plugin</em> is a really awesome plugin.</p>",
        "installation": "<p>Installation of this plugin can be managed from the WordPress Dashboard » Plugins » Add New page by clicking the [Upload Plugin] button, then selecting the myAwesomePlugin.zip file from your computer.</p>",
        "changelog": "<h4>Version 1.0.1 – Sep 13, 2022</h4>\n<ul>\n<li>Critical bug fixes.</li>\n</ul>\n<h4>Version 1.0.0 – Aug 31, 2022</h4>\n<ul>\n<li>Initial public release.</li>\n</ul>",
        "screenshots": "<ol>\n<li>myAwesomePlugin\n<img src=\"https://myawesomeserver.com/myAwesomePlugin/assets/screenshot-1.png\" alt=\"myAwesomePlugin\" /></li>\n</ol>",
        "upgrade_notice": "<p>This version fixes critical bugs. Please update immediately.</p>",
        "other_notes": "<p>myAwesomePlugin is a derivative plugin of and requires installation and registration of <a href=\"https://eacDoojigger.earthasylum.com/\">{eac}Doojigger</a></p>",
        "copyright": "<p>Copyright © 2022, Me</p>"
    },
    "banners": {
        "low": "https://myawesomeserver.com/myAwesomePlugin/assets/banner-772x250.jpg",
        "high": "https://myawesomeserver.com/myAwesomePlugin/assets/banner-1544x500.jpg"
    },
    "icons": {
        "low": "https://myawesomeserver.com/myAwesomePlugin/assets/icon-128x128.png",
        "high": "https://myawesomeserver.com/myAwesomePlugin/assets/icon-256x256.png"
    },
    "rating": 100,
    "num_ratings": 1
}
```

== Readme File ==

Although not required for a self-hosted plugin, your plugin should include a readme file named _readme.txt_ that follows the [WordPress readme file standard](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/#readme-header-information). Much of the information in your plugin header and the JSON file is mirrored in your _readme.txt_ file.

A *readme.txt* file IS required for WordPress-hosted plugins and is critical to proper hosting, installation, and updating. See [WP Hosted](#wp-hosted).


== WP Hosted ==

If your plugin or extension is to be hosted by WordPress, you obviously don't need a JSON file nor should you set *Update URI* in the plugin file. But what you do need is a [readme.txt](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/) file.

And there's one little thing that seems to be lacking in WordPress that can easily be fixed...

When WordPress shows an update available on the *Plugins* screen, it does not display the *Upgrade Notice* that you may have included in your *readme.txt* file. Fixing this is very simple.

= Upgrade Notice =

One important note: for WordPress to parse the *Upgrade Notice* section of your readme file, it must be versioned. Meaning that there must be sub-sections with the version number. This is the only section that WordPress will parse out of your file.

```text
 == Upgrade Notice ==

 = 1.0.1 =

 This version fixes critical bugs. Please update immediately.
```

When WordPress shows an update available for version 1.0.1, it will only parse

`This version fixes critical bugs. Please update immediately.`

![Upgrade Notice](https://swregistry.earthasylum.com/software-updates/eacdoojigger/assets/upgrade_notice.png)

If there is no `= 1.0.1 =` section, then there is no upgrade notice.

= Derivative Plugin Upgrade Notice =

Derivative Plugins can provide the upgrade notice simply by adding 'AutoUpdate' to the `$plugin_detail` array in the main plugin file...

In your main plugin file (myAwesomePlugin.php)
1. 	Add `'AutoUpdate' => 'wp'` to the $plugin_detail array.

*myAwesomePlugin.php*

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
					'AutoUpdate'	=> 'wp', // automatic update 'self' or 'wp'
                ];
        } // myAwesomePlugin
    } // namespace

    namespace // global scope
    {
        defined( 'ABSPATH' ) or exit;

        \myAwesomeNamespace\myAwesomePlugin::loadPlugin(true);
    }


= Extension Plugin Upgrade Notice =

Extension Plugins can provide the upgrade notice simply by adding 1 line of code...

In your main plugin file (myAwesomeExtension.php)
1.	Add `myAwesomePlugin::loadPluginUpdater(__FILE__,'wp');` inside the 'load_extensions' filter.

*myAwesomeExtension.php*

	/**
	 * Plugin Name: 	My Awesome Extensions
	 * Description:		EarthAsylum Consulting {eac}Doojigger Awesome extension
	 * Version:			1.0.0
	 */
	namespace myAwesomeNamespace;

	class myAwesomeExtension
	{
		public function __construct()
		{
			add_filter( 'myAwesomePlugin_load_extensions', function($extensionDirectories)
				{
					myAwesomePlugin::loadPluginUpdater(__FILE__,'wp'); // automatic update 'self' or 'wp'

					$extensionDirectories[ plugin_basename( __FILE__ ) ] = [plugin_dir_path( __FILE__ ).'/Extensions'];
					return $extensionDirectories;
				}
			);
		}
	}
	new \myAwesomeNamespace\myAwesomeExtension();

\* Note: myAwesomePlugin::loadPluginUpdater(...) is referencing the plugin this extension is extending. If you are writing an extension for some other plugin (rather than myAwesomePlugin), you would use that plugin's class name
