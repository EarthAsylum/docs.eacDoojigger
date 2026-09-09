## {eac}Doojigger for WordPress  
[![EarthAsylum Consulting](https://img.shields.io/badge/EarthAsylum-Consulting-0?&labelColor=6e9882&color=707070)](https://earthasylum.com/)
[![WordPress](https://img.shields.io/badge/WordPress-Plugins-grey?logo=wordpress&labelColor=blue)](https://wordpress.org/plugins/search/EarthAsylum/)
[![eacDoojigger](https://img.shields.io/badge/Requires-%7Beac%7DDoojigger-da821d)](https://eacDoojigger.earthasylum.com/)
[![Sponsorship](https://img.shields.io/static/v1?label=Sponsorship&message=%E2%9D%A4&logo=GitHub&color=bf3889)](https://github.com/sponsors/EarthAsylum)

<details><summary>Plugin Header</summary>

Plugin URI:             https://eacDoojigger.earthasylum.com/  
Author:                 [EarthAsylum Consulting](https://www.earthasylum.com)  
Last Updated:           07-Sep-2025  
Contributors:           [earthasylum](https://github.com/earthasylum),[kevinburkholder](https://profiles.wordpress.org/kevinburkholder)  
Donate link:            https://github.com/sponsors/EarthAsylum  
License:                EarthAsylum Consulting Proprietary License - {eac}PLv1  
License URI:            https://eacDoojigger.earthasylum.com/end-user-license-agreement/  
Tags:                   plugin development, rapid development, multi-function, security, encryption, debugging, administration, contextual-help, session management, maintenance mode, plugin framework, plugin derivative, plugin extensions, toolkit  
GitHub URI:             https://github.com/EarthAsylum/docs.eacDoojigger/wiki  

</details>

> {eac}Doojigger is a powerful, extensible WordPress framework: a ready-to-use utility plugin combined with an architecture for building your own plugins, so you can ship professional-grade results in a fraction of the usual development time.


### Links

:package: [Download {eac}Doojigger Extras][extras]

:open_file_folder: [{eac}Doojigger Wiki: documentation and examples][wiki]

:green_book: [{eac}Doojigger PHP Reference][reference]

:bookmark_tabs: [{eac}Doojigger Web Site][website]

:package: [Download eacDoojigger.zip][download]

[extras]:       https://swregistry.earthasylum.com/software-updates/eacdoojigger-extras.zip
[wiki]:         https://github.com/EarthAsylum/docs.eacDoojigger/wiki
[reference]:    https://earthasylum.github.io/docs.eacDoojigger/
[website]:      https://eacdoojigger.earthasylum.com
[download]:	    https://swregistry.earthasylum.com/software-updates/eacdoojigger.zip "Download eacDoojigger.zip, latest release, ready to install"

### What's Here

#### Doodads

Example extensions, code snippets, helpers, or traits that may be a part of the [{eac}Doojigger](https://eacdoojigger.earthasylum.com) plugin for WordPress.

#### Doohickeys

Plugins that may be dropped into your `/wp-content/plugins` (or `mu-plugins`) folder. *{eac}Doohickey* gives you an easy way to add additional Doolollys to your site simply by dropping them into the `/eacDoohickey/Doolollys` folder.

#### Doolollys

Extensions that may be added to the {eac}Doohickey plugin or dropped into your theme's `/eacDoojigger/Doolollys` folder.

#### Extras

The {eac}Doojigger `Extras` folder ([download](https://swregistry.earthasylum.com/software-updates/eacdoojigger-extras.zip)) contains documentation as well as examples, templates, skeletons and frameworks for using and building with {eac}Doojigger


### Definitions

__doojigger_ (n)
1. Something unspecified whose name is either forgotten or not known.
2. *A plugin built on {eac}Doojigger (including {eac}Doojigger itself)*

_doololly_ (n)
1. Any nameless small object, typically some form of gadget.
2. *An extension added to a Doojigger *

_doohickey_ (n)
1. A thing (used in a vague way to refer to something whose name one does not know or cannot recall).
2. *A Doololly packaged as its own standalone plugin*

_doodad_ (n)
1. Something, especially a small device or part, whose name is unknown or forgotten.
2. *A shared helper or trait included with a Doojigger*


### Summary

{eac}Doojigger is a WordPress plugin framework: a base plugin that ships with a working set of security, debugging, encryption, session, and administration features, plus an architecture for building your own plugins and extensions on top of it without rewriting WordPress boilerplate each time.

If you build or maintain multiple WordPress plugins — internal tools, client work, or products — {eac}Doojigger's abstract classes and traits handle the repetitive plumbing (activation/deactivation, multi-site awareness, options storage, updates, settings UI, logging) so your code only has to handle what's actually specific to your plugin.

__Three ways to build with {eac}Doojigger__

1. 	**Derivative plugins ("Doojiggers")**
— build your own plugin by extending {eac}Doojigger's abstract classes (`abstract_context`, `abstract_frontend`, `abstract_backend`). You write a small loader file plus a class file; the framework handles the rest.

2. 	**Extensions ("Doolollys")**
— a PHP class dropped into the `Extensions` folder (of the plugin or a child theme) that adds functionality to an existing Doojigger. Lowest-effort option for small, task-specific additions.

3. 	**Extension plugins ("Doohickeys")**
— an extension packaged as its own plugin, so it isn't at risk of being overwritten on the parent plugin's next update or reinstall. Can ship its own automatic updates via the included `plugin_update` trait.

If you're customizing a WordPress site, code that needs to survive a theme change belongs in a plugin, not a theme. Themes should hold presentation code only — anything functional should live in a plugin or plugin extension so it isn't lost the next time the theme is updated or swapped.


### See Also

+   [{eac}SoftwareRegistry]
A full-featured Software Registration/Licensing Server built on {eac}Doojigger.

+   [{eac}ObjectCache]
A light-weight and very efficient drop-in persistent object cache that uses a fast SQLite database and even faster APCu shared memory to cache WordPress objects.

+   [{eac}SimpleCDN]
An {eac}Doojigger extension to enable the use of Content Delivery Network assets on your WordPress site, significantly decreasing your page load times and improving the user experience.

+   [{eac}SimpleSMTP]
An {eac}Doojigger extension to configure WordPress wp_mail and phpmailer to use your SMTP (outgoing) mail server when sending email.

+   [{eac}SimpleAWS]
An {eac}Doojigger extension to include and enable use of the Amazon Web Services (AWS) PHP Software Development Kit (SDK).

+   [{eac}Readme]
An {eac}Doojigger extension to translate a WordPress style markdown 'readme.txt' file and provides _shortcodes_ to access header lines, section blocks, or the entire document.

+   [{eac}SimpleGTM]
Installs and configures the Google Tag Manager (GTM) or Google Analytics (GA4) script with optional tracking events.

+   [{eac}MetaPixel]
An {eac}Doojigger extension to install the Facebook/Meta Pixel to enable tracking of PageView, ViewContent, AddToCart, InitiateCheckout and Purchase events.

+	[{eac}KeyValue]
An easy to use, efficient, key-value pair storage mechanism for WordPress that takes advatage of the WP Object Cache. Similar to WP options/transients with less overhead and greater efficiency (and fewer hooks).

[{eac}SoftwareRegistry]:	https://github.com/EarthAsylum/eacSoftwareRegistry
[{eac}ObjectCache]:			https://github.com/EarthAsylum/eacObjectCache/
[{eac}SimpleCDN]:			https://github.com/EarthAsylum/eacSimpleCDN/
[{eac}SimpleSMTP]:			https://github.com/EarthAsylum/eacSimpleSMTP/
[{eac}SimpleAWS]:			https://github.com/EarthAsylum/eacSimpleAWS/
[{eac}Readme]:				https://github.com/EarthAsylum/eacReadme/
[{eac}SimpleGTM]:			https://github.com/EarthAsylum/eacSimpleGTM/
[{eac}MetaPixel]:			https://github.com/EarthAsylum/eacMetaPixel/
[{eac}KeyValue]:			https://github.com/EarthAsylum/eacKeyValue/
