<?php
namespace EarthAsylumConsulting\Traits;

/*
 * Usage:
 *
 * Use this trait in your class file...
 *
 *		use \EarthAsylumConsulting\Traits\version_compare;
 */

/**
 * version compare utilites trait - {eac}Doojigger for WordPress
 *
 * @category	WordPress Plugin
 * @package		{eac}Doojigger\Traits
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2021 EarthAsylum Consulting <www.EarthAsylum.com>
 * @version		2.x
 * @link		https://eacDoojigger.earthasylum.com/
 * @see 		https://eacDoojigger.earthasylum.com/phpdoc/
 */

trait version_compare
{
	/**
	 * is version compatable (>=)
	 *
	 * @param	string	$version (n.n.n)
	 * @param	string	$required (n.n.n)
	 * @return	bool 	true if $version >= $required
	 */
	public function isVersionCompatable(string $version, string $required): bool
	{
		switch(strtolower($required))
		{
			case 'php':
				return \is_php_version_compatible($version);
			case 'wordpress':
			case 'wp':
				return \is_wp_version_compatible($version);
			case 'woocommerce':
			case 'wc':
				return (defined('WC_VERSION') && version_compare(WC_VERSION, $version, 'ge')) ? true : false;
			case 'eacdoojigger':
			case '{eac}doojigger':
			case 'eac':
				return (defined('EACDOOJIGGER_VERSION') && version_compare(EACDOOJIGGER_VERSION, $version, 'ge')) ? true : false;
		}
		return version_compare($version, $required, 'ge');
	}


	/**
	 * version compare
	 *
	 * @param	string	$version1 (n.n.n)
	 * @param	string	$version2 (n.n.n)
	 * @param	mixed	$eqVal returned if $versions1 = $version2 (true)
	 * @param	mixed	$ltVal returned if $versions1 < $version2 (-1)
	 * @param	mixed	$gtVal returned if $versions1 > $version2 (1)
	 * @return	bool|int true if $versions1 = $version2, $ltVal if $versions1 < $version2, $gtVal if $versions1 > $version2
	 */
	public function isVersionCompare(string $version1, string $version2, $eqVal = true, $ltVal = -1, $gtVal = +1)
	{
		switch (version_compare($version1, $version2)) {
			case -1 : return $ltVal;
			case  0 : return $eqVal;
			case  1 : return $gtVal;
		}
	}


	/**
	 * version compare equal to
	 *
	 * @param	string	$version1 (n.n.n)
	 * @param	string	$version2 (n.n.n)
	 * @return	bool 	true if $versions1 = $version2
	 */
	public function isVersionEqualTo(string $version1, string $version2): bool
	{
		return version_compare($version1, $version2, 'eq');
	}


	/**
	 * version compare not equal to
	 *
	 * @param	string	$version1 (n.n.n)
	 * @param	string	$version2 (n.n.n)
	 * @return	bool 	true if $versions1 <> $version2
	 */
	public function isVersionNotEqualTo(string $version1, string $version2): bool
	{
		return version_compare($version1, $version2, 'ne');
	}


	/**
	 * version compare less than
	 *
	 * @param	string	$version1 (n.n.n)
	 * @param	string	$version2 (n.n.n)
	 * @return	bool 	true if $versions1 < $version2
	 */
	public function isVersionLessThan(string $version1, string $version2): bool
	{
		return version_compare($version1, $version2, 'lt');
	}


	/**
	 * version compare less than or equal to
	 *
	 * @param	string	$version1 (n.n.n)
	 * @param	string	$version2 (n.n.n)
	 * @return	bool 	true if $versions1 <= $version2
	 */
	public function isVersionLessThanEqualTo(string $version1, string $version2): bool
	{
		return version_compare($version1, $version2, 'le');
	}


	/**
	 * version compare greater than
	 *
	 * @param	string	$version1 (n.n.n)
	 * @param	string	$version2 (n.n.n)
	 * @return	bool 	true if $versions1 > $version2
	 */
	public function isVersionGreaterThan(string $version1, string $version2): bool
	{
		return version_compare($version1, $version2, 'gt');
	}


	/**
	 * version compare greater than or equal to
	 *
	 * @param	string	$version1 (n.n.n)
	 * @param	string	$version2 (n.n.n)
	 * @return	bool 	true if $versions1 >= $version2
	 */
	public function isVersionGreaterThanEqualTo(string $version1, string $version2): bool
	{
		return version_compare($version1, $version2, 'ge');
	}
}
