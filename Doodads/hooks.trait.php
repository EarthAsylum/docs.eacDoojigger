<?php
namespace EarthAsylumConsulting\Traits;

/**
 * hooks trait - filters & actions using prefixed name
 *
 * @category	WordPress Plugin
 * @package		{eac}Doojigger\Traits
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2024 EarthAsylum Consulting <www.EarthAsylum.com>
 * @version		24.1212.1
 */
trait hooks
{
	/**
	 * wp has_filter - returns count of filters
	 *
	 * @param string $hookName the name of the filter
	 * @param callable callback the callback to check for
	 * @return int
	 */
	public function wp_filter_count(string $hookName, $callback = false)
	{
		global $wp_filter;
		$hookCount 	= 0;
		if ( isset( $wp_filter[ $hookName ] ) )
		{
			foreach ($wp_filter[ $hookName ]->callbacks as $priority) {
				$hookCount += count($priority);
			}
		}
		return $hookCount;
	}


	/**
	 * wp has_action - returns count of actions
	 *
	 * @param string $hookName the name of the action
	 * @param callable callback the callback to check for
	 * @return int
	 */
	public function wp_action_count(string $hookName, $callback = false)
	{
		return $this->wp_filter_count( $hookName, $callback );
	}


	/**
	 * has_filter with prefixed name
	 *
	 * @param string $hookName the name of the filter
	 * @param callable callback the callback to check for
	 * @return	mixed
	 */
	public function has_filter(string $hookName, $callback = false)
	{
		return \has_filter( $this->prefixHookName($hookName), $callback );
	}


	/**
	 * has_filter with prefixed name - returns count of filters
	 *
	 * @param string $hookName the name of the filter
	 * @param callable callback the callback to check for
	 * @return int
	 */
	public function has_filter_count(string $hookName, $callback = false)
	{
		return $this->wp_filter_count( $this->prefixHookName($hookName), $callback );
	}


	/**
	 * add_filter with prefixed name
	 *
	 * @param string $hookName the name of the filter
	 * @param mixed ...$args arguments passed to filter
	 * @return	mixed
	 */
	public function add_filter(string $hookName,...$args)
	{
		return \add_filter( $this->prefixHookName($hookName), ...$args );
	}


	/**
	 * remove_filter with prefixed name
	 *
	 * @param string $hookName the name of the filter
	 * @param mixed ...$args arguments passed to filter
	 * @return	mixed
	 */
	public function remove_filter(string $hookName,...$args)
	{
		return \remove_filter( $this->prefixHookName($hookName), ...$args );
	}


	/**
	 * apply_filters with prefixed name
	 *
	 * @param string $hookName the name of the filter
	 * @param mixed ...$args arguments passed to filter
	 * @return	mixed
	 */
	public function apply_filters(string $hookName,...$args)
	{
		return \apply_filters( $this->prefixHookName($hookName), ...$args );
	}


	/**
	 * did_filter with prefixed name (WP 6.1)
	 *
	 * @param string $hookName the name of the filter
	 * @return	mixed
	 */
	public function did_filter(string $hookName)
	{
		return \did_filter( $this->prefixHookName($hookName) );
	}


	/**
	 * has_action with prefixed name
	 *
	 * @param string $hookName the name of the action
	 * @param callable callback the callback to check for
	 * @return	mixed
	 */
	public function has_action(string $hookName, $callback = false)
	{
		return \has_action( $this->prefixHookName($hookName), $callback );
	}


	/**
	 * has_action with prefixed name - returns count of filters
	 *
	 * @param string $hookName the name of the action
	 * @param callable callback the callback to check for
	 * @return int
	 */
	public function has_action_count(string $hookName, $callback = false)
	{
		return $this->wp_action_count( $this->prefixHookName($hookName), $callback );
	}


	/**
	 * add_action with prefixed name
	 *
	 * @param string $hookName the name of the action
	 * @param mixed ...$args arguments passed to action
	 * @return	mixed
	 */
	public function add_action(string $hookName,...$args)
	{
		return \add_action( $this->prefixHookName($hookName), ...$args );
	}


	/**
	 * remove_action with prefixed name
	 *
	 * @param string $hookName the name of the action
	 * @param mixed ...$args arguments passed to action
	 * @return	mixed
	 */
	public function remove_action(string $hookName,...$args)
	{
		return \remove_action( $this->prefixHookName($hookName), ...$args );
	}


	/**
	 * do_action with prefixed name
	 *
	 * @param string $hookName the name of the action
	 * @param mixed ...$args arguments passed to action
	 * @return	mixed
	 */
	public function do_action(string $hookName,...$args)
	{
		return \do_action( $this->prefixHookName($hookName), ...$args );
	}


	/**
	 * did_action with prefixed name
	 *
	 * @param string $hookName the name of the action
	 * @return	mixed
	 */
	public function did_action(string $hookName)
	{
		return \did_action( $this->prefixHookName($hookName) );
	}


	/**
	 * Get the prefixed version of the hook name
	 *
	 * @param	string	$hookName filter/action name
	 * @return	string	hookname with prefix
	 */
	public function prefixHookName(string $hookName): string
	{
		return $this->addClassNamePrefix($hookName);
	}
}
