<?php
/**
 * {eac}CronSettings - Site wide settings and actions for WP-Cron / Action Scheduler.
 *
 * This is a self-contained piece of code - drop in to plugins or mu-plugins folder to invoke.
 *
 * -- REVIEW BEFORE IMPLEMENTING --
 * Disables normal WP-Cron function;
 * Adds constants and actions used to control and debug WP-Cron and/or Action Scheduler
 *
 *
 * @category    WordPress Plugin
 * @package     {eac}CronSettings
 * @author      Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright   Copyright (c) 2025 EarthAsylum Consulting <www.earthasylum.com>
 *
 * @wordpress-plugin
 * Plugin Name:         {eac}CronSettings
 * Description:         {eac}CronSettings - Site wide settings and actions for WP-Cron / Action Scheduler
 * Version:             1.5.4
 * Last Updated:        26-Apr-2025
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

namespace EarthAsylumConsulting\CronSettings
{
    /* *****
     *
     * Define constatnts to control actions
     *
     ***** */


    /*
     * internal wp-cron may be disabled when triggered by external request to /wp-cron.php?doing_wp_cron
     *      like server-based crontab - wget -q -O - https://domain.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
     *          or EasyCron - https://www.easycron.com
     *          or UptimeRobot - https://dashboard.uptimerobot.com/
     *          or AWS EventBridge - https://aws.amazon.com/eventbridge/
     *          or some other external trigger
     */
    if (!defined('DISABLE_WP_CRON'))
    {
        define('DISABLE_WP_CRON', true);
    }


    /*
     * set minimum interval time for all wp-cron jobs
     * some wp-cron jobs may be scheduled every minute, this forces a minimum time between executions
     */
    if (!defined('WP_CRON_MINIMUM_INTERVAL'))
    {
        define('WP_CRON_MINIMUM_INTERVAL', 5 * MINUTE_IN_SECONDS);
    }


    /*
     * add or change wp-cron schedule intervals
     * create new or override existing intervals (schedules)
     */
    if (!defined('WP_CRON_SCHEDULE_INTERVALS'))
    {
        $days_this_month = (int)wp_date('t');
        define('WP_CRON_SCHEDULE_INTERVALS', array(
            // add 'monthly' based on days this month
            'monthly'       => [
                'interval'  => $days_this_month * DAY_IN_SECONDS,
                'display'   => "Monthly ({$days_this_month} days)",
            ],
        ));
    }


    /*
     * log wp-cron scheduling errors
     */
    if (!defined('WP_CRON_LOG_ERRORS'))
    {
        define('WP_CRON_LOG_ERRORS', true);
    }

    /*
     * debug certain wp-cron scheduling actions
     */
    if (!defined('WP_CRON_DEBUG'))
    {
//      define('WP_CRON_DEBUG', true);
    }


    /*
     * if eacDoojigger is not installed, add a replacement action for debugging
     */
    add_action('muplugins_loaded', function()
        {
            if (!defined('EACDOOJIGGER_VERSION'))
            {
                add_action('eacDoojigger_log_debug', function($data,$source)
                    {
                        error_log($source.': '.var_export($data,true));
                    }
                );
            }
        }
    );


    /* *****
     *
     * Actions triggered with above constants
     *
     ***** */


    /*
     * Change WP-Cron to ActionScheduler or ActionScheduler to WP-Cron
     */
//    wp_cron_to_action_scheduler();    // calls ActionScheduler functions too early
//    action_scheduler_to_wp_cron();


    /*
     * log scheduling errors through eacDoojigger
     */
    if (defined('WP_CRON_LOG_ERRORS'))
    {
        /*
         * catch & log rescheduling errors
         */
        add_action( 'cron_reschedule_event_error', function($result, $hook, $v)
        {
            do_action( 'eacDoojigger_log_debug',func_get_args(),"cron_reschedule_event_error" );
        },10,3);

        /*
         * catch & log unscheduling errors
         */
        add_action( 'cron_unschedule_event_error', function($result, $hook, $v)
        {
            do_action( 'eacDoojigger_log_debug',func_get_args(),"cron_unschedule_event_error" );
        },10,3);
    }


    /*
     * debugging filters
     */
    if (defined('WP_CRON_DEBUG'))
    {
        add_filter( 'pre_reschedule_event', function($return,$event)
        {
            $event->_datetime_ = wp_date('c',$event->timestamp);
            do_action('eacDoojigger_log_debug',$event,"pre_reschedule_event");
            return $return;
        },PHP_INT_MAX,2);

        add_filter( 'pre_schedule_event', function($return,$event)
        {
            $event->_datetime_ = wp_date('c',$event->timestamp);
            do_action('eacDoojigger_log_debug',$event,"pre_schedule_event");
            return $return;
        },PHP_INT_MAX,2);

        add_filter( 'pre_unschedule_event', function($return, $timestamp, $hook, $args, $wp_error)
        {
            do_action('eacDoojigger_log_debug',[wp_date('c',$timestamp), $timestamp, $hook, $args],"pre_unschedule_event");
            return $return;
        },PHP_INT_MAX,5);
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
        });
    }


    /*
     * add or change schedule intervals
     */
    if (defined('WP_CRON_SCHEDULE_INTERVALS'))
    {
        add_filter( 'cron_schedules', function($cron_intervals)
        {
            return array_merge($cron_intervals,WP_CRON_SCHEDULE_INTERVALS);
        },10000);
    }


    /* *****
     *
     * Methods used
     *
     ***** */


    /**
     * Route WordPress events to ActionScheduler schedules.
     * ** WordPress cron may call these filters before ActionScheduler initializes **
     */
    function wp_cron_to_action_scheduler()
    {
        /*
         * Route single/recurring events to ActionScheduler
         */
        add_filter( 'pre_schedule_event', function($return, $event, $wp_error )
        {
            $event = (array)$event;
            $return = ($event['schedule'] == false)
                ? as_schedule_single_action( $event['timestamp'], $event['hook'], $event['args'], 'wp-cron' )
                : as_schedule_recurring_action( $event['timestamp'], $event['interval'], $event['hook'], $event['args'], 'wp-cron' );
            return $return;
        },10,3);

        /*
         * Route reschedule recurring events to ActionScheduler
         */
        add_filter( 'pre_reschedule_event', function($return, $event, $wp_error )
        {
            $event = (array)$event;
            as_unschedule_action( $event['hook'], $event['args'] );
            $return = as_schedule_recurring_action( $event['timestamp'], $event['interval'], $event['hook'], $event['args'], 'wp-cron' );
            return $return;
        },10,3);

        /*
         * Route unschedule events to ActionScheduler
         */
        add_filter( 'pre_unschedule_event', function($return, $timestamp, $hook, $args, $wp_error )
        {
            as_unschedule_action( $hook, $args );
            return $return;
        },10,5);

        /*
         * Route clear events to ActionScheduler
         */
        add_filter( 'pre_clear_scheduled_hook', function($return, $hook, $args, $wp_error )
        {
            as_unschedule_all_actions( $hook, $args );
            return $return;
        },10,4);

        /*
         * Route unschedule hook to ActionScheduler
         */
        add_filter( 'pre_unschedule_hook', function($return, $hook, $wp_error )
        {
            as_unschedule_all_actions( $hook );
            return $return;
        },10,3);

        /*
         * Route get event to ActionScheduler
         */
        add_filter( 'pre_get_scheduled_event', function($return, $hook, $args, $timestamp )
        {
            $result =  as_get_scheduled_actions([
                'hook'          => $hook,
                'args'          => $args,
                'date'          => $timestamp,
                'date_compare'  => '>=',
                'status'        => \ActionScheduler_Store::STATUS_PENDING,
                'per_page'      => 1,
            ]);
            if (!$result) return [];
            $result = current($result);

            $schedule = $result->get_schedule();
            $timestamp = $schedule->get_date()->getTimestamp();
            if ($schedule->is_recurring()) {
                $interval = $schedule->get_recurrence();
                $schedule = find_wp_schedule($interval);
            } else {
                $interval = null;
                $schedule = false;
            }

            $return = (object) array(
                'hook'      => $result->get_hook(),
                'timestamp' => $timestamp,
                'schedule'  => $schedule,
                'args'      => $result->get_args(),
                'interval'  => $interval,
            );
            return $return;
        },10,4);
    }


    /**
     * Route ActionScheduler events to WordPress schedules
     */
    function action_scheduler_to_wp_cron()
    {
        /*
         * Route single events to wp-cron
         */
        add_filter( 'pre_as_schedule_single_action', function( $return, $timestamp, $hook, $args )
        {
            return wp_schedule_single_event( $timestamp, $hook, $args );
        },25,4);

        /*
         * Route recurring events to wp-cron
         */
        add_filter( 'pre_as_schedule_recurring_action', function( $timestamp, $interval, $hook, $args )
        {
            $recurrence = find_wp_schedule($interval);
            return wp_schedule_event( $timestamp, $recurrence, $hook, $args );
        },25,4);
    }


    /**
     * Find a WordPress schedule from ActionScheduler interval
     */
    function find_wp_schedule($interval)
    {
        $cron_schedules = wp_get_schedules();
        uasort($cron_schedules, function($a,$b) {
            return ($a['interval'] == $b['interval']) ? 0 : ( ($a['interval'] < $b['interval']) ? -1 : 1);
        });
        // look for closest interval from any schedule
        foreach($cron_schedules as $name => $schedule) {
            if ($interval <= $schedule['interval']) {
                return $name;
            }
        }
        return 'no-schedule-found';
    }
}
