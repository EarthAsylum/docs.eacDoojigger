<?php
namespace EarthAsylumConsulting\Traits;

/*
 * Usage:
 *
 * Use this trait in your class file...
 *
 *		use \EarthAsylumConsulting\Traits\datetime;
 *
 * see: https://www.php.net/manual/en/class.datetime.php
 * see: https://www.php.net/manual/en/datetime.formats.php (Supported Date and Time Formats)
 */

/**
 * date/time utilities trait - {eac}Doojigger for WordPress
 *
 * @category	WordPress Plugin
 * @package		{eac}Doojigger\Traits
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2021 EarthAsylum Consulting <www.EarthAsylum.com>
 * @version		2.x
 * @link		https://eacDoojigger.earthasylum.com/
 * @see 		https://eacDoojigger.earthasylum.com/phpdoc/
 */


trait datetime
{
	/**
	 * Get date/time timezone
	 *
	 * @param 	object|string 	$timezone - timezone or DateTimeZone or DateTime (default = wp_timezone())
	 * @param 	object 			$datetime DateTime
	 * @return 	object DateTimeZone object or false on invalid
	 */
	public function getTimeZone($timezone = null, $datetime = null)
	{
		if (empty($timezone))
		{
			if ($datetime instanceOf \DateTime) {
				$timezone = $datetime->getTimezone();
			} else {
				$timezone = wp_timezone();
			}
		}
		else if (is_string($timezone))
		{
			$timezone = new \DateTimeZone($timezone);
		}
		else if ($timezone instanceOf \DateTime)
		{
			$timezone = $datetime->getTimezone();
		}

		return ($timezone instanceOf \DateTimeZone) ? $timezone : false;
	}


	/**
	 * Get date/time
	 *
	 * @param 	mixed 	date/time string, timestamp or object
	 * @param	string	modify datetime (e.g. '+1 day')
	 * @param 	object|string 	timezone or DateTimeZone (default = wp_timezone())
	 * @return 	object 	DateTime object or false on invalid
	 */
	public function getDateTime($datetime = 'now', $modify = null, $timezone = null)
	{
		$timezone = $this->getTimeZone($timezone,$datetime);

		if (is_numeric($datetime))							// timestamp
		{
			$datetime = new \DateTime('@'.$datetime);		// timestamp ignores timezone here
			$datetime->setTimeZone($timezone);				// so set timezone here
		}
		else if ($datetime instanceOf \DateTime)			// date/time object
		{
			$datetime->setTimeZone($timezone);
		}
		else												// date/time string (Supported Date and Time Format)
		{
			$datetime = new \DateTime($datetime,$timezone);
		}

		if (!empty($modify))
		{
			$datetime->modify($modify);
		}

		return $datetime;
	}


	/**
	 * Get date/time (returning formatted date/time string)
	 *
	 * @param 	string 	$datetime date/time string in format
	 * @param 	string 	$format date/time format
	 * @param 	object 	$timezone DateTimeZone
	 * @return 	string 	DateTime formatted or null on invalid
	 */
	public function getFormattedDateTime(string $datetime = 'now', string $format = 'Y-m-d H:i:s', $timezone = null)
	{
		return ($datetime = $this->getDateTime($datetime,null,$timezone))
			? $datetime->format($format)
			: null;
	}


	/**
	 * UTC date/time
	 *
	 * @param 	string 	date/time string
	 * @param	int		Seconds to add (subtract) to time
	 * @return 	object 	DateTime object or false on invalid
	 */
	public function getDateTimeUTC($datetime = 'now', $modify = null)
	{
		return $this->getDateTime($datetime, $modify, new \DateTimeZone('UTC'));
	}


	/**
	 * local date/time
	 *
	 * @param 	string 	date/time string
	 * @param	int		Seconds to add (subtract) to time
	 * @return 	object 	DateTime object or false on invalid
	 */
	public function getDateTimeLocal($datetime = 'now', $modify = null)
	{
		return $this->getDateTime($datetime, $modify, wp_timezone());
	}


	/**
	 * is a valid date/time
	 *
	 * @param 	string 	$datetime date/time string in format
	 * @param 	string 	$format date/time format
	 * @param 	object 	$timezone DateTimeZone
	 * @return 	object 	DateTime object or false on invalid
	 */
	public function isValidDateTime(string $datetime, string $format = 'Y-m-d H:i:s', $timezone = null)
	{
		$timezone = $this->getTimeZone($timezone,$datetime);

		try {
			$d = \DateTime::createFromFormat($format, $datetime, $timezone);
		} catch (\Throwable $e) {
			return false;
		}

		return ($d && $d->format($format) == $datetime) ? $d : false;
	}


	/**
	 * is a valid date/time (returning formatted date/time string)
	 *
	 * @param 	string 	$datetime date/time string in format
	 * @param 	string 	$format date/time format
	 * @param 	object 	$timezone DateTimeZone
	 * @return 	string 	DateTime formatted or null on invalid
	 */
	public function isFormattedDateTime(string $datetime, string $format = 'Y-m-d H:i:s', $timezone = null)
	{
		return ($datetime = $this->isValidDateTime($datetime,$format,$timezone))
			? $datetime->format($format)
			: null;
	}


	/**
	 * is a valid date
	 *
	 * @param 	string 	$date date string in format
	 * @param 	string 	$format date format
	 * @param 	object 	$timezone DateTimeZone
	 * @return 	object 	DateTime object or false on invalid
	 */
	public function isValidDate(string $date, string $format = 'Y-m-d', $timezone = null)
	{
		return $this->isValidDateTime($date,$format,$timezone);
	}


	/**
	 * is a valid time
	 *
	 * @param 	string 	$time time string in format
	 * @param 	string 	$format time format
	 * @param 	object 	$timezone DateTimeZone
	 * @return 	object 	DateTime object or false on invalid
	 */
	public function isValidTime(string $time, string $format = 'H:i:s', $timezone = null)
	{
		return $this->isValidDateTime($time,$format,$timezone);
	}


	/**
	 * is date/time between range
	 *
	 * @param 	mixed 	$datetime date/time
	 * @param 	mixed 	$datetimeLo date/time low
	 * @param 	mixed 	$datetimeHi date/time high
	 * @param 	mixed 	$timezone default = wp_timezone()
	 * @return 	object 	DateTime object or false on invalid
	 */
	public function isDateTimeBetween($datetime, $datetimeLo, $datetimeHi, $timezone = null)
	{
		$timezone 	= $this->getTimeZone($timezone,$datetime);
		$datetimeLo = $this->getDateTime($datetimeLo,null,$timezone);
		$datetimeHi = $this->getDateTime($datetimeHi,null,$timezone);
 		$datetime 	= $this->getDateTime($datetime,null,$timezone);

		return (($datetime >= $datetimeLo) && ($datetime <= $datetimeHi)) ? $datetime : false;
	}
}
