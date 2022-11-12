<?php
namespace Bitrix\Translate\IO;

use Bitrix\Translate;

class FileSystemHelper
{
	/**
	 * Return list full path of folders.
	 *
	 * @param string $path Path to check.
	 *
	 * @return string[]
	 */
	public static function getFolderList($path)
	{
		$path = Translate\IO\Path::tidy(\rtrim($path, '/'));
		return \glob($path.'/*', \GLOB_ONLYDIR);
	}

	/**
	 * Return list full path of folders.
	 *
	 * @param string $path Path to check.
	 *
	 * @return string[]
	 */
	public static function getFileList($path)
	{
		$path = Translate\IO\Path::tidy(\rtrim($path, '/'));
		return array_merge(
			\glob($path.'/.*.php'),
			\glob($path.'/*.php')
		);
	}
}
