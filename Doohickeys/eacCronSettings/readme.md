## {eac}CronSettings - Site wide settings and actions for WP-Cron / Action Scheduler  
[![EarthAsylum Consulting](https://img.shields.io/badge/EarthAsylum-Consulting-0?&labelColor=6e9882&color=707070)](https://earthasylum.com/)
[![WordPress](https://img.shields.io/badge/WordPress-Plugins-grey?logo=wordpress&labelColor=blue)](https://wordpress.org/plugins/search/EarthAsylum/)
[![eacDoojigger](https://img.shields.io/badge/Requires-%7Beac%7DDoojigger-da821d)](https://eacDoojigger.earthasylum.com/)

<details><summary>Document Header</summary>

Plugin URI:             https://github.com/EarthAsylum/docs.eacDoojigger 
Author:                 [EarthAsylum Consulting](https://www.earthasylum.com)  
Stable tag:             1.6.0  
Last Updated:           02-Jun-2025  
Requires at least:      5.8  
Tested up to:           6.8  
Requires PHP:           8.1  
Contributors:           earthasylum@github,kevinburkholder@wordpress  
License:				GPLv3 or later  
License URI:			https://www.gnu.org/licenses/gpl.html  
GitHub URI:             https://github.com/EarthAsylum/docs.eacDoojigger/wiki  

</details>

{eac}CronSettings - Site wide settings and actions for WP-Cron / Action Scheduler.

### Description

WP-Cron is built in to WordPress and is used to schedule and run jobs or tasks in the background.
By default, WP-Cron is triggered when a page is loaded on your web site making it dependent on web site traffic.

WordPress checks for any events due to run on every page load. When an event is found, WordPress spawns a request to `wp-cron.php` to run the scheduled events.

Action Scheduler is included with WooCommerce and other plugins and is a more advanced, scalable system for scheuling,
running and logging large sets of background tasks. Action Scheduler is dependent on WP-Cron to initiate its
queue runner process so is also dependent on web site traffic.

The default WP-Cron behavior can be disabled and replaced with a more reliable time-based trigger, such as a server
cron event or third-pary cron service, making it more timely and less dependent on web site traffic.

By default, this plugin...

- Disables the normal WP-Cron behavior, assuming an external WP-Cron trigger (`DISABLE_WP_CRON`).
- Caches WP-Cron events to a custom table (and wp_cache), removing the 'cron' option (`WP_CRON_CACHE_EVENTS`).
- Sets the minimum cron interval to 5 minutes (`WP_CRON_MINIMUM_INTERVAL`).
- Adds a 'Monthly' interval based on the days in the current month (`WP_CRON_SCHEDULE_INTERVALS`).
- Increases Action Scheduler run time limit from 30 to 60 seconds (`AS_RUN_TIME_LIMIT`)
- Adds 'failed' actions to Action Scheduler's automatic clean-up.
- Changes Action Scheduler clean-up retention period from 1 month to 1 week (`AS_CLEANUP_RETENTION_PERIOD`).
- Changes Action Scheduler clean-up batch size from 20 to 100 (`AS_CLEANUP_BATCH_SIZE`).
- Logs scheduling errors (`WP_CRON_LOG_ERRORS`).

Optionally...

- Route WP-Cron events to Action Scheduler or Action Scheduler to WP-Cron (`WP_CRON_REROUTE_EVENTS`).
- Disable the Action Scheduler queue runner (`DISABLE_AS_QUEUE_RUNNER`).
- Log scheduling events for debugging (`WP_CRON_DEBUG`).

#### Defined Constants

The constants used in {eac}CronSettings should be reviewed and changed to suit your requirements. Constants may be set as needed, deleted or 'commented-out' if not needed.

Alternately, these constants may be defined in your `wp-config.php` file.

##### DISABLE_WP_CRON
`true` | `false` (default: true)

The internal wp-cron process may be disabled when triggered by an external request to `/wp-cron.php?doing_wp_cron` like:
 
* server-based crontab -> `wget -q -O - https://domain.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1`
* EasyCron - https://www.easycron.com
* UptimeRobot - https://www.uptimerobot.com/
* AWS EventBridge - https://aws.amazon.com/eventbridge/
* some other external trigger

##### WP_CRON_CACHE_EVENTS
`true` | `false` | `'revert'` (default: true)

Store WP-Cron events in a custom table rather than 'cron' option. Caches to custom table and wp_cache, removes 'cron' from options table and `$alloptions` array.
* This will generate 'The cron event list could not be saved' error that can be ignored.
* set to 'revert' to revert this process and restore 'cron' option from cache

##### WP_CRON_REROUTE_EVENTS
`WP_TO_AS` ('wp-as') | `AS_TO_WP` ('as-wp') (default: undefined)

Route WP-Cron events to Action Scheduler or Action Scheduler to WP-Cron.
*Currently scheduled events are not changed.*

Action Scheduler is not available until the WordPress `init` action.
* WP-Cron events scheduled or checked before `init` are not routed through Action Scheduler.  

Action Scheduler doesn't provide hooks for several rescheduling/unscheduling functions.
* Only new Action Scheduler tasks can be routed to WP-Cron, scheduled recuring events remain in Action Scheduler.  

##### WP_CRON_MINIMUM_INTERVAL 
`int (seconds)` (default: 5 minutes)

Set a minimum interval time for all wp-cron jobs.
Some wp-cron jobs (like Action Scheduler queue runner) may be scheduled every minute, this forces a minimum time between executions.

##### WP_CRON_SCHEDULE_INTERVALS
`array` (default: monthly)

Add or change wp-cron schedule intervals.
Create new or override existing intervals (schedules).

The array format is:

    'schedule_name'    => [
        'interval'  => int (seconds),
        'display'   => "short description",
    ],

##### WP_CRON_LOG_ERRORS
`true` | `false` (default: undefined)

Log wp-cron scheduling errors

##### WP_CRON_DEBUG
`true` | `false` (default: undefined)

Debug certain wp-cron scheduling actions

##### DISABLE_AS_QUEUE_RUNNER
`true` | `false` (default: undefined)

Disable the Action Scheduler queue runner.
Does not disable or change Action Scheduler functions but stops events from running.

##### AS_RUN_TIME_LIMIT
`int (seconds)` (default: 60)

Set Action Scheduler run time limit (default = 30 seconds).

##### AS_CLEANUP_RETENTION_PERIOD
`int (seconds)` (default: 1 week)

Set Action Scheduler retention time.
Action Scheduler retains completed & failed events for 1 month which could lead to a bloated database table.

##### AS_CLEANUP_BATCH_SIZE
`int (count)` (default: 100)

Set Action Scheduler clean-up batch size (default = 20).
Action Scheduler purges only 20 records at a time in its clean-up process.


### Installation

1. Edit the defined constants to fit your configuration needs.
2. Drop the `eacCronSettings.php` file into your `mu-plugins` folder.
