<?php
/**
 * {eac}CronSettings - Site wide settings and actions for WP-Cron / Action Scheduler.
 *
 * @category    WordPress Plugin
 * @package     {eac}CronSettings
 * @author      Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright   Copyright (c) 2025 EarthAsylum Consulting <www.earthasylum.com>
 *
 * @wordpress-plugin
 * Plugin Name:         {eac}CronSettings
 * Description:         {eac}CronSettings - Site wide settings and actions for WP-Cron / Action Scheduler
 * Version:             1.6.0
 * Last Updated:        02-Jun-2025
 * Requires at least:   5.8
 * Tested up to:        6.8
 * Requires PHP:        7.4
 * Author:              EarthAsylum Consulting
 * Author URI:          http://www.earthasylum.com
 * License:             GPLv3 or later
 * License URI:         https://www.gnu.org/licenses/gpl.html
 * Tags:                cron, wp-cron, action-scheduler
 * Github URI:          https://github.com/EarthAsylum/docs.eacDoojigger/blob/main/Doodads/
 */


/* *****
 *
    This is a self-contained piece of code - drop in to plugins or mu-plugins folder to invoke.


    --    REVIEW AND ADJUST BEFORE IMPLEMENTING     --
    -- Adjust the defined constants below as needed --


    WP-Cron is built in to WordPress and is used to schedule and run jobs or tasks in the background.
    By default, WP-Cron is triggered when a page is loaded on your web site making it dependent on web site traffic.

    Action Scheduler is included with WooCommerce and other plugins and is a more advanced, scalable system for scheuling,
    running and logging large sets of background tasks. Action Scheduler is dependent on WP-Cron to initiate its
    queue runner process so is also dependent on web site traffic.

    The default WP-Cron behavior can be disabled and replaced with a more reliable time-based trigger, such as a server
    cron event or third-pary cron service, making it more timely and less dependent on web site traffic.

    By default, this plugin...

    - Disables the normal WP-Cron behavior, assuming an external WP-Cron trigger (DISABLE_WP_CRON).
    - Caches WP-Cron events to a custom table (and wp_cache), removing the 'cron' option (WP_CRON_CACHE_EVENTS).
    - Sets the minimum cron interval to 5 minutes (WP_CRON_MINIMUM_INTERVAL).
    - Adds a 'Monthly' interval based on the days in the current month (WP_CRON_SCHEDULE_INTERVALS).
    - Increases Action Scheduler run time limit from 30 to 60 seconds (AS_RUN_TIME_LIMIT)
    - Adds 'failed' actions to Action Scheduler's automatic clean-up.
    - Changes Action Scheduler clean-up retention period from 1 month to 1 week (AS_CLEANUP_RETENTION_PERIOD).
    - Changes Action Scheduler clean-up batch size from 20 to 100 (AS_CLEANUP_BATCH_SIZE).
    - Logs scheduling errors (WP_CRON_LOG_ERRORS).

    Optionally...

    - Route WP-Cron events to Action Scheduler or Action Scheduler to WP-Cron (WP_CRON_REROUTE_EVENTS).
            Action Scheduler is not ready until WP 'init' action.
                WP-Cron events scheduled or checked before 'init' are not routed through AS.
            Action Scheduler doesn't provide hooks for several rescheduling/unscheduling functions.
                Only new AS tasks can be routedd to WP-Cron, scheduled recuring events remain in AS.
    - Disable the Action Scheduler queue runner (DISABLE_AS_QUEUE_RUNNER).
    - Log scheduling events for debugging (WP_CRON_DEBUG).
 *
 ***** */

namespace EarthAsylumConsulting\CronSettings;

const VERSION       = '1.6.0';
const WP_TO_AS      = 'wp-as';
const AS_TO_WP      = 'as-wp';
const AS_CRON_HOOK  = 'action_scheduler_run_queue';
const CACHE_ID      = 'eac_cache';
$days_this_month    = (int)wp_date('t');


/* *****
 *
 * Define constants to control actions.
 *
 * set/change/remove these constants as needed
 * may instead be defined in wp-congif.php
 *
 ***** */


/*
 * Define constants controlling WP-Cron
 */
define_constants([
    /*
     * Internal wp-cron may be disabled when triggered by external request to /wp-cron.php?doing_wp_cron
     *      like server-based crontab -> wget -q -O - https://domain.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
     *          or EasyCron - https://www.easycron.com
     *          or UptimeRobot - https://www.uptimerobot.com/
     *          or AWS EventBridge - https://aws.amazon.com/eventbridge/
     *          or some other external trigger
     */

    'DISABLE_WP_CRON'                   => true,

    /*
     * Store WP-Cron events in a custom table rather than 'cron' option.
     * Caches to custom table and wp_cache, removes 'cron' from options table and $alloptions array.
     * This will generate 'The cron event list could not be saved' error that can be ignored.
     * - set to 'revert' to revert this process and restore 'cron' option from cache -
     */

    'WP_CRON_CACHE_EVENTS'              => true,

    /*
     * Route WP-Cron events to Action Scheduler or Action Scheduler to WP-Cron.
     * Currently scheduled events are not changed.
     * Since AS is not useable until the WP 'init' action, events scheduled before 'init' are not rerouted.
     */

    // wp-cron -> action scheduler
    //'WP_CRON_REROUTE_EVENTS'          => WP_TO_AS,

    // action scheduler -> wp-cron
    //'WP_CRON_REROUTE_EVENTS'          => AS_TO_WP,

    /*
     * Set minimum interval time for all wp-cron jobs.
     * Some wp-cron jobs (like AS queue runner) may be scheduled every minute,
     * this forces a minimum time between executions.
     */

    'WP_CRON_MINIMUM_INTERVAL'          => 5 * MINUTE_IN_SECONDS,

    /*
     * Add or change wp-cron schedule intervals.
     * Create new or override existing intervals (schedules)
     */

    'WP_CRON_SCHEDULE_INTERVALS'        => array(
            // add 'monthly' based on days this month
            'monthly'       => [
                'interval'  => $days_this_month * DAY_IN_SECONDS,
                'display'   => "Monthly ({$days_this_month} days)",
            ],
    ),

    /*
     * Log wp-cron scheduling errors
     */

    //'WP_CRON_LOG_ERRORS'              => true,

    /*
     * Debug certain wp-cron scheduling actions
     */

    //'WP_CRON_DEBUG'                   => true,
]);


/*
 * Define constants controlling Action Scheduler
 */
define_constants([
    /*
     * Disable the Action Scheduler queue runner.
     * Does not disable or change Action Scheduler functions but stops events from running.
     * When used with WP_CRON_REROUTE_EVENTS, this should not be disabled until pending events have run.
     */

    //'DISABLE_AS_QUEUE_RUNNER'         => true,

    /*
     * Set Action Scheduler run time limit (default = 30 seconds).
     */

    'AS_RUN_TIME_LIMIT'                 => MINUTE_IN_SECONDS,

    /*
     * Set Action Scheduler retention time (default = 1 month).
     * Action Scheduler retains completed & failed events for 1 month which could lead to a bloated database table.
     */

    'AS_CLEANUP_RETENTION_PERIOD'       => WEEK_IN_SECONDS,

    /*
     * Set Action Scheduler clean-up batch size (default = 20).
     * Action Scheduler purges only 20 records at a time in its clean-up process.
     */

    'AS_CLEANUP_BATCH_SIZE'             => 100,
]);


/* *****
 *
 * No further changes required
 *
 ***** */


/* *****
 *
 * Actions & filters triggered by above constants
 *
 ***** */


/*
 * error logging (always log internal errors)
 */
add_action("wpcron_log_error", function($message,$source)
{
	if (has_action("eacDoojigger_log_error")) {
		do_action("eacDoojigger_log_error",$message,$source);
	}
	error_log($source.': '.$message);
},10,2);

/*
 * Debugging - if eacDoojigger is not installed, use error_log
 */
add_action("wpcron_log_debug", function($data,$source)
{
	if (has_action("eacDoojigger_log_debug")) {
		do_action("eacDoojigger_log_debug",$data,$source);
	} else {
		error_log($source.': '.var_export($data,true));
	}
},10,2);


/*
 * Store WP-Cron events in a custom table rather than 'cron' option.
 */
if (defined('WP_CRON_CACHE_EVENTS'))
{
    if (WP_CRON_CACHE_EVENTS === true) {
        store_wp_to_db();
    } else
    if (WP_CRON_CACHE_EVENTS === 'revert') {
        revert_wp_to_db();
    }
}


/*
 * Route WP-Cron to Action Scheduler or Action Scheduler to WP-Cron.
 * Events scheduled before WordPress 'init' are not rerouted.
 */
if (defined('WP_CRON_REROUTE_EVENTS'))
{
    add_action('action_scheduler_init', function()
    {
        // Route WP-Cron to Action Scheduler
        if (WP_CRON_REROUTE_EVENTS == WP_TO_AS) {
            route_wp_to_as();
        } else
        // Route Action Scheduler to WP-Cron
        if (WP_CRON_REROUTE_EVENTS == AS_TO_WP) {
            route_as_to_wp();
        }
    },5);
}


/*
 * set minimum interval time when scheduling
 */
if (defined('WP_CRON_MINIMUM_INTERVAL') && is_int(WP_CRON_MINIMUM_INTERVAL))
{
    add_filter( 'schedule_event', function($event)
    {
        if ($event->schedule && $event->interval < WP_CRON_MINIMUM_INTERVAL) {
            $event->interval = WP_CRON_MINIMUM_INTERVAL;
            $event->timestamp = time() + $event->interval;
        }
        return $event;
    },1000);
}


/*
 * add or change schedule intervals
 */
if (defined('WP_CRON_SCHEDULE_INTERVALS') && is_array(WP_CRON_SCHEDULE_INTERVALS))
{
    add_filter( 'cron_schedules', function($cron_intervals)
    {
        return array_merge($cron_intervals,WP_CRON_SCHEDULE_INTERVALS);
    },1000);
}


/*
 * add schedules translated from Action Scheduler intervals when rerouting events
 */
if (defined('WP_CRON_REROUTE_EVENTS') && ($schedules = get_option('as_wp_cron_schedules')))
{
    add_filter('cron_schedules', function($cron_intervals) use($schedules)
    {
        return array_merge($cron_intervals,$schedules);
    },1000);
}


/*
 * When (if) Action Scheduler loads
 */
add_action('action_scheduler_init', function()
{
    /*
     * add 'failed' actions to Action Scheduler automatic cleanup
     */
    add_filter( 'action_scheduler_default_cleaner_statuses', fn($s) => $s[] = \ActionScheduler_Store::STATUS_FAILED );

    /*
     * disable Action Scheduler queue runner
     */
    if (defined('DISABLE_AS_QUEUE_RUNNER') && DISABLE_AS_QUEUE_RUNNER)
    {
        remove_action( 'action_scheduler_run_queue', array( ActionScheduler::runner(), 'run' ) );
    }

    /*
     * set the maximum run time for Action Scheduler (default 30 seconds)
     */
    if (defined('AS_RUN_TIME_LIMIT') && is_int(AS_RUN_TIME_LIMIT))
    {
        add_filter( 'action_scheduler_queue_runner_time_limit', fn() => AS_RUN_TIME_LIMIT );
    }

    /*
     * Set Action Scheduler retention period
     */
    if (defined('AS_CLEANUP_RETENTION_PERIOD') && is_int(AS_CLEANUP_RETENTION_PERIOD))
    {
        add_filter( 'action_scheduler_retention_period', fn() => AS_CLEANUP_RETENTION_PERIOD );
    }

    /*
     * set number of actions to purge in Action Scheduler automatic cleanup (default 20)
     */
    if (defined('AS_CLEANUP_BATCH_SIZE') && is_int(AS_CLEANUP_BATCH_SIZE))
    {
        add_filter( 'action_scheduler_cleanup_batch_size', fn() => AS_CLEANUP_BATCH_SIZE );
    }
}); // action_scheduler_init


/*
 * log scheduling errors
 */
if (defined('WP_CRON_LOG_ERRORS') && WP_CRON_LOG_ERRORS)
{
    /*
     * catch & log rescheduling errors
     */
    add_action( 'cron_reschedule_event_error', function($result, $hook, $v)
    {
        do_action( 'wpcron_log_debug',func_get_args(),current_action() );
    },10,3);

    /*
     * catch & log unscheduling errors
     */
    add_action( 'cron_unschedule_event_error', function($result, $hook, $v)
    {
        do_action( 'wpcron_log_debug',func_get_args(),current_action() );
    },10,3);

    /*
     * catch & log Action Scheduler errors
     */
    add_action( 'action_scheduler_failed_to_schedule_next_instance', function($action_id, $e, $action)
    {
        do_action( 'wpcron_log_debug',[$action->get_hook(),$e->getMessage()],current_action() );
    },10,3);

    add_action( 'action_scheduler_failed_execution', function($action_id, $e, $context)
    {
        do_action( 'wpcron_log_debug',$e->getMessage(),current_action() );
    },10,3);

    add_action( 'action_scheduler_failed_validation', function($action_id, $e, $context)
    {
        do_action( 'wpcron_log_debug',$e->getMessage(),current_action() );
    },10,3);
}


/*
 * debugging filters
 */
if (defined('WP_CRON_DEBUG') && WP_CRON_DEBUG)
{
    add_filter( 'pre_reschedule_event',                 function($return,$event)
    {
        $_timestamp_ = wp_date('c',$event->timestamp);
        do_action('wpcron_log_debug',get_defined_vars(),current_filter());
        return $return;
    },PHP_INT_MAX,2);

    add_filter( 'pre_schedule_event',                   function($return,$event)
    {
        $_timestamp_ = wp_date('c',$event->timestamp);
        do_action('wpcron_log_debug',get_defined_vars(),current_filter());
        return $return;
    },PHP_INT_MAX,2);

    add_filter( 'pre_unschedule_event',                 function($return, $timestamp, $hook, $args, $wp_error)
    {
        $_timestamp_ = wp_date('c',$timestamp);
        do_action('wpcron_log_debug',get_defined_vars(),current_filter());
        return $return;
    },PHP_INT_MAX,5);


    add_filter( 'pre_as_schedule_single_action',        function($return, $timestamp, $hook, $args)
    {
        $_timestamp_ = wp_date('c',$timestamp);
        do_action('wpcron_log_debug',get_defined_vars(),current_filter());
        return $return;
    },PHP_INT_MAX,4);

    add_filter( 'pre_as_schedule_recurring_action',     function($return, $timestamp, $interval, $hook, $args)
    {
        $_timestamp_ = wp_date('c',$timestamp);
        do_action('wpcron_log_debug',get_defined_vars(),current_filter());
        return $return;
    },PHP_INT_MAX,5);

    add_filter( 'pre_as_schedule_cron_action',          function($return, $timestamp, $schedule, $hook, $args)
    {
        $_timestamp_ = wp_date('c',$timestamp);
        do_action('wpcron_log_debug',get_defined_vars(),current_filter());
        return $return;
    },PHP_INT_MAX,5);

    add_filter( 'pre_as_enqueue_async_action',          function($return, $hook, $args)
    {
        $_timestamp_ = wp_date('c',time());
        do_action('wpcron_log_debug',get_defined_vars(),current_filter());
        return $return;
    },PHP_INT_MAX,3);
}


/* *****
 *
 * Store WordPress cron events to custome table.
 *
 ***** */


/**
 * Add wp_cron hooks
 */
function store_wp_to_db()
{
    if (VERSION != get_option(__NAMESPACE__))
    {
        create_cache_table();
        update_option(__NAMESPACE__,VERSION,true);
    }

    add_filter( 'pre_update_option_cron',           __NAMESPACE__ .'\\_update_option_cron',         10, 3 );
    add_filter( 'pre_option_cron',                  __NAMESPACE__ .'\\_get_option_cron',            10, 3 );

    // only update cache at end of cron process
    if (defined('DOING_CRON') && DOING_CRON)
    {
        add_action( "delete_transient_doing_cron",  __NAMESPACE__ .'\\_flush_option_cron',          10, 1 );
    }
}

/**
 * When updating 'cron' option
 */
function _update_option_cron($value, $old_value, $option)
{
    if ( $value === $old_value || maybe_serialize( $value ) === maybe_serialize( $old_value ) ) {
        return $value;
    }

    wp_cache_set('wp_cron_events',$value,CACHE_ID);

    if (!defined('DOING_CRON')) {
        _flush_option_cron(false,$value);
    }

    // return $old_value to prevent updating
    return $old_value;
}

/**
 * Flush cron actions to db
 */
function _flush_option_cron($transient, $cron = false)
{
    global $wpdb;
    $table = $wpdb->prefix.CACHE_ID;
    $value = $cron ?: wp_cache_get('wp_cron_events',CACHE_ID);
    $result = $wpdb->query(
        $wpdb->prepare(
                "INSERT INTO `{$table}` (`key`, `value`) VALUES (%s, %s) " .
                "ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);",
            'wp_cron_events',
            serialize($value)
        )
    );
    if (!$result) {
        do_action('wpcron_log_error',$wpdb->last_error,__FUNCTION__);
    }
}

/**
 * When getting 'cron' option array
 */
function _get_option_cron($return, $option, $default_value)
{
    global $wpdb;
    $table = $wpdb->prefix.CACHE_ID;
    $result = wp_cache_get('wp_cron_events',CACHE_ID) ?:
              $wpdb->get_var("SELECT `value` FROM `{$table}` WHERE `key` = 'wp_cron_events'",0);
    if (empty($result)) return $return;
    return maybe_unserialize($result);
}

/**
 * Create cron cache table
 */
function create_cache_table()
{
    global $wpdb;
    $table = $wpdb->prefix.CACHE_ID;

    $charset_collate = $wpdb->get_charset_collate();

    $result = $wpdb->query(
        "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
            `key` varchar(255) NOT NULL,
            `value` longtext,
            `expires` timestamp,
            PRIMARY KEY (`id`), UNIQUE `key` (`key`)
        ) ENGINE=InnoDB {$charset_collate};"
    );
    if (!$result) {
        do_action('wpcron_log_error',$wpdb->last_error,__FUNCTION__);
    } else if ($cron = get_option('cron')) {
	    // once we create the cron cache, we can delete the cron option
        _update_option_cron($cron, false, 'cron');
        delete_option('cron');
    }
}


/* *****
 *
 * Revert cached events back to 'cron' option
 *
 ***** */


function revert_wp_to_db()
{
    global $wpdb;
    $table = $wpdb->prefix.CACHE_ID;

    remove_filter( 'pre_update_option_cron',        __NAMESPACE__ .'\\_update_option_cron');
    remove_filter( 'pre_option_cron',               __NAMESPACE__ .'\\_get_option_cron');

    if ($value = _get_option_cron(false,'cron',false)) {
        update_option('cron', $value, true);
        wp_cache_delete('wp_cron_events',CACHE_ID);
        $wpdb->delete("{$table}",['key'=>'wp_cron_events']);
    }
}


/* *****
 *
 * Route WordPress events to Action Scheduler schedules.
 * WP-Cron may call these filters before Action Scheduler initializes
 *
 ***** */


/**
 * Add wp_cron hooks
 */
function route_wp_to_as()
{
    add_filter( 'pre_schedule_event',           __NAMESPACE__.'\\_wp_schedule_event',        10,3 );
    add_filter( 'pre_reschedule_event',         __NAMESPACE__.'\\_wp_reschedule_event',      10,3 );
    add_filter( 'pre_unschedule_event',         __NAMESPACE__.'\\_wp_unschedule_event',      10,5 );
    add_filter( 'pre_clear_scheduled_hook',     __NAMESPACE__.'\\_wp_clear_scheduled_hook',  10,4 );
    add_filter( 'pre_unschedule_hook',          __NAMESPACE__.'\\_wp_unschedule_hook',       10,3 );
    add_filter( 'pre_get_scheduled_event',      __NAMESPACE__.'\\_wp_get_scheduled_event',   10,4 );
}

/**
 * Shortcut to check wp-to-as validity
 *
 * @param callable  $callback function to execute
 * @param array     $args function argument ([0]=default return value)
 * @param string    $hook event hook name
 */
function whenReady( $callback, $args, $hook)
{
    // allow external filters or Action Scheduler cron to fall through
    return (!empty($args[0]) || !($hook == AS_CRON_HOOK))
        ? $args[0]
        : call_user_func_array($callback, $args);
}

/**
 * Route single/recurring events to Action Scheduler
 */
function _wp_schedule_event( $return, $event, $wp_error )
{
    return whenReady(function( $return, $event, $wp_error )
        {
            $event = (array)$event;
            return ($event['schedule'] == false)
                ? \as_schedule_single_action( $event['timestamp'], $event['hook'], $event['args'], 'wp-cron' )
                : \as_schedule_recurring_action( $event['timestamp'], $event['interval'], $event['hook'], $event['args'], 'wp-cron' );
        },
        [$return, $event, $wp_error],
        $event->hook
    );
}

/**
 * Route reschedule recurring events to Action Scheduler
 */
function _wp_reschedule_event( $return, $event, $wp_error )
{
    return whenReady(function( $return, $event, $wp_error )
        {
            $event = (array)$event;
            \as_unschedule_action( $event['hook'], $event['args'] );
            return \as_schedule_recurring_action( $event['timestamp'], $event['interval'], $event['hook'], $event['args'], 'wp-cron' );
        },
        [$return, $event, $wp_error],
        $event->hook
    );
}

/**
 * Route unschedule event to Action Scheduler
 */
function _wp_unschedule_event( $return, $timestamp, $hook, $args, $wp_error )
{
    whenReady(function( $return, $timestamp, $hook, $args, $wp_error )
        {
            return \as_unschedule_action( $hook, $args );
        },
        [$return, $timestamp, $hook, $args, $wp_error],
        $hook
    );
    return $return;
}

/**
 * Route clear events to Action Scheduler
 */
function _wp_clear_scheduled_hook( $return, $hook, $args, $wp_error )
{
    whenReady(function( $return, $hook, $args, $wp_error )
        {
            return \as_unschedule_all_actions( $hook, $args );
        },
        [$return, $hook, $args, $wp_error],
        $hook
    );
    return $return;
}

/**
 * Route unschedule hook to Action Scheduler
 */
function _wp_unschedule_hook( $return, $hook, $wp_error )
{
    whenReady(function( $return, $hook, $wp_error )
        {
            return \as_unschedule_all_actions( $hook );
        },
        [$return, $hook, $wp_error],
        $hook
    );
    return $return;
}

/**
 * Get next event from Action Scheduler
 */
function _wp_get_scheduled_event( $return, $hook, $args, $timestamp )
{
    return whenReady(function( $return, $hook, $args, $timestamp )
        {
            $query_args = [
                'hook'          => $hook,
                'status'        => \ActionScheduler_Store::STATUS_PENDING,
                'per_page'      => 1,
            ];
            if ($args && is_array($args)) {
                $query_args['args']          = $args;
            }
            if ($timestamp && is_numeric($timestamp)) {
                $query_args['date']          = $timestamp;
                $query_args['date_compare']  = '>=';
            }
            $result =  \as_get_scheduled_actions($query_args);

            return (empty($result)) ? false : event_from_action(current($result));
        },
        [$return, $hook, $args, $timestamp],
        $hook
    );
}


/* *****
 *
 * Route Action Scheduler events to WordPress schedules
 * Action Scheduler initializes itself on 'init' with priority 1 and can not be used until then.
 *
 ***** */


/**
 * Add Action Scheduler hooks
 */
function route_as_to_wp()
{
    add_filter( 'pre_as_schedule_single_action',    __NAMESPACE__.'\\_as_schedule_single_action',    10,4 );
    add_filter( 'pre_as_schedule_recurring_action', __NAMESPACE__.'\\_as_schedule_recurring_action', 10,5 );
//  add_filter( 'pre_as_schedule_cron_action',      __NAMESPACE__.'\\_as_schedule_cron_action',      10,5 );
    add_filter( 'pre_as_enqueue_async_action',      __NAMESPACE__.'\\_as_enqueue_async_action',      10,3 );

// no such filters...
//  add_filter( 'as_unschedule_action',             __NAMESPACE__.'\\_as_unschedule_action',         10,2 );
//  add_filter( 'as_unschedule_all_actions',        __NAMESPACE__.'\\_as_unschedule_all_actions',    10,2 );
//  add_filter( 'as_next_scheduled_action',         __NAMESPACE__.'\\_as_next_scheduled_action',     10,2 );
//  add_filter( 'as_has_scheduled_action',          __NAMESPACE__.'\\_as_has_scheduled_action',      10,2 );
//  add_filter( 'as_get_scheduled_actions',         __NAMESPACE__.'\\_as_get_scheduled_actions',     10,2 );
}

/**
 * Route single events to wp-cron
 */
function _as_schedule_single_action( $return, $timestamp, $hook, $args )
{
    return \wp_schedule_single_event( $timestamp, $hook, $args );
}

/**
 * Route recurring events to wp-cron
 */
function _as_schedule_recurring_action( $return, $timestamp, $interval, $hook, $args )
{
    $recurrence = find_wp_schedule($interval);
    return \wp_schedule_event( $timestamp, $recurrence, $hook, $args );
}

/**
 * @todo - Route recurring cron events to wp-cron
 */
function _as_schedule_cron_action( $return, $timestamp, $schedule, $hook, $args )
{
    return false;
}

/**
 * Route single events to wp-cron
 */
function _as_enqueue_async_action( $return, $hook, $args )
{
    return \wp_schedule_single_event( time(), $hook, $args );
}


/* *****
 *
 * Namespaced functions
 *
 ***** */


/**
 * Define constants
 *
 * @param array $constants [constant => value]
 */
function define_constants( $constants )
{
    foreach ($constants as $constant => $value)
    {
        if (!defined($constant)) define($constant,$value);
    }
}

/**
 * Find a WordPress schedule from Action Scheduler interval
 *
 * @param int $interval number of seconds between executions
 */
function find_wp_schedule( $interval )
{
    $cron_schedules = wp_get_schedules();
    uasort($cron_schedules, function($a,$b)
        {
            return ($a['interval'] == $b['interval']) ? 0 : (($a['interval'] < $b['interval']) ? -1 : 1);
        }
    );
    // look for matching interval from any schedule
    foreach($cron_schedules as $name => $schedule)
    {
        if ($interval == $schedule['interval']) {
            return $name;
        }
    }
    return add_wp_schedule($interval);
}

/**
 * Add a new WordPress schedule from Action Scheduler interval
 *
 * @param int $interval number of seconds between executions
 */
function add_wp_schedule( $interval )
{
    $schedule =  ["as_every_{$interval}_seconds"  => [
        'interval'  => $interval,
        'display'   => "Action Scheduler, every {$interval} seconds",
    ]];
    $schedules = \get_option('as_wp_cron_schedules',[]);
    array_push($schedules,$schedule);
    \update_option('as_wp_cron_schedules',$schedules);
    return key($schedule);
}

/**
 * Action Schedule action to WP-Cron event
 *
 * @param object $action Action Scheduler action
 */
function event_from_action( $action )
{
    $schedule           = $action->get_schedule();
    $timestamp          = $schedule->get_date()->getTimestamp();
    if ($schedule->is_recurring()) {
        $interval       = $schedule->get_recurrence();
        $schedule       = find_wp_schedule($interval);
    } else {
        $interval       = null;
        $schedule       = false;
    }

    return (object) array(
        'hook'          => $action->get_hook(),
        'timestamp'     => $timestamp,
        'schedule'      => $schedule,
        'args'          => $action->get_args(),
        'interval'      => $interval,
    );
}
