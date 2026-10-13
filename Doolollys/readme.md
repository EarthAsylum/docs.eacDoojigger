## Doololly, An extension to a Doojigger plugin.. 
[![EarthAsylum Consulting](https://img.shields.io/badge/EarthAsylum-Consulting-0?&labelColor=6e9882&color=707070)](https://earthasylum.com/)
[![WordPress](https://img.shields.io/badge/WordPress-Plugins-grey?logo=wordpress&labelColor=blue)](https://wordpress.org/plugins/search/EarthAsylum/)
[![eacDoojigger](https://img.shields.io/badge/Requires-%7Beac%7DDoojigger-da821d)](https://eacDoojigger.earthasylum.com/)
[![Sponsorship](https://img.shields.io/static/v1?label=Sponsorship&message=%E2%9D%A4&logo=GitHub&color=bf3889)](https://github.com/sponsors/EarthAsylum)

<details><summary>Document Header</summary>

Plugin URI:         https://eacDoojigger.earthasylum.com/  
Author:             [EarthAsylum Consulting](https://www.earthasylum.com)  
Last Updated:       13-Oct-2026  
Contributors:       [earthasylum](https://github.com/earthasylum),[kevinburkholder](https://profiles.wordpress.org/kevinburkholder)  
Donate link:        https://github.com/sponsors/EarthAsylum  
Requires EAC:       3.3  
WordPress URI:      https://wordpress.org/plugins/search/earthasylum/  
GitHub URI:         https://github.com/EarthAsylum/docs.eacDoojigger/wiki/  

</details>

_doololly_ (n)
1. Any nameless small object, typically some form of gadget.
2. *An extension to a Doojigger plugin.*

### Description

A Doololly extension is a PHP program class that adds functionality to the base plugin. Extensions can be coded for specific needs and can be as simple or complex as needed.

#### cloudflare.doololly

Provides limited control and cache purging when hosting your site behind CloudFlare.
1. Pause/Resume Cloudflare service.
2. Override the browser cache time-to-live.
3. Set 'cf-edge-cache' header to trigger APO or custom rules.
     custom rules can look for this header set to 'cache,platform=wordpress' or 'no-cache' or not set.
4. Add 'Cache-Tag' header with mime type that can be used for selective cache purge.
5. Set certain cloudflare toggle options directly from WordPress.
6. Automatically purge cloudflare cache (by host name).
7. Create and update Cloudflare IP blacklist (account) and blocking rule (zone) using risk assessment results.

__Risk Assessment Blacklist__

When a request is blocked by the Security::Risk Assessment process, Add the remote IP address to the Cloudflare blacklist to prevent further access to the site. This creates or updates a Cloudflare IP list (Account → Configurations → Lists) and adds a security rule (Zone → Security → Security Rules) to block the list.
Note: To use an existing IP list, change its description to `Block IP addresses identified by Risk Assessment`.

__Filters__

1. 'cloudflare_purge_everything_actions' (array) - filter actions that trigger a cache purge.
2. 'cloudflare_use_cache' (bool) - override `cf-edge-cache` setting (cache/no-cache).

__Actions__

1. 'eacDoojigger_cloudflare_pause'   - pause the cloudflare service
2. 'eacDoojigger_cloudflare_resume'  - resume the cloudflare service
3. 'eacDoojigger_cloudflare_purge'   - purge the cloudflare cache

Recommended for free plans. Paid plans should use the official Cloudflare plugin with
Automatic Platform Optimization (APO).

#### class.webhooks.debugging.doololly

Adds a 'Webhooks' section to the {eac}Doojigger - Debugging tab.

+	Add a new webhook in WooCommerce -> Settings -> Advanced -> Webhooks
+	Use the generated Delivery URL and Webhook Secret in your webhook.

Outputs the webhook payload to the debugging log.

Drop this into
     /wp-content/themes/{your-theme}/eacDoojigger/Doolollys


#### For further documentation, see:

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
