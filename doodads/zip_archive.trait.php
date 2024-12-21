<?php
namespace EarthAsylumConsulting\Traits;

/*
 * Usage:
 *
 * Use this trait in your class file...
 *
 *		use \EarthAsylumConsulting\Traits\zip_archive;
 */

/**
 * zip_archive trait - {eac}Doojigger for WordPress
 *
 * Used to create a new .zip archive from a directory
 *
 * @category	WordPress Plugin
 * @package		{eac}Doojigger\Traits
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2021 EarthAsylum Consulting <www.EarthAsylum.com>
 * @version		2.x
 * @link		https://eacDoojigger.earthasylum.com/
 * @see 		https://eacDoojigger.earthasylum.com/phpdoc/
 */

trait zip_archive
{
	/**
	 * Create a new ZipArchive
	 *
	 * @param	string	folder to archive
	 * @param	string	file name of new zip (_.zip)
	 * @param	bool	delete files after archiving
	 * @return	object|bool	zip archive or false
	 */
	public function zip_archive_create(string $pathName, string $fileName, bool $purge = false)
	{
		if ( ! class_exists( '\ZipArchive' ) )
		{
			return false;
		}

		// Get real path for our folder
		$rootPath = realpath($pathName);

		// Initialize archive object
		$zip = new \ZipArchive();
		$zip->open($fileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

		// Initialize empty "delete list"
		$filesToDelete = array();

		// Create recursive directory iterator
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($rootPath),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);

				// Add current file to archive
				$zip->addFile($filePath, $relativePath);

				// Add current file to "delete list"
				$filesToDelete[] = $filePath;
			}
		}

		// Zip archive will be created only after closing object
		$zip->close();

		if ($purge === true)
		{
			// Delete all files from "delete list"
			foreach ($filesToDelete as $file)
			{
				unlink($file);
			}
		}

		return $zip;
	}
}
