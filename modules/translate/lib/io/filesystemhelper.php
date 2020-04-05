<?php
namespace Bitrix\Translate\IO;

use Bitrix\Translate;

class FileSystemHelper
{
	/**
	 * Return list full path of folders.
	 *
	 * @param string $path Path to check.
	 * @param string $mask Filter pattern.
	 *
	 * @return string[]
	 */
	public static function getFolderList($path, $mask = '*')
	{
		$path = Translate\IO\Path::tidy(rtrim($path, '/'));
		return glob($path.'/{,.}'.$mask, GLOB_BRACE | GLOB_ONLYDIR);
	}

	/**
	 * Return list full path of folders.
	 *
	 * @param string $path Path to check.
	 * @param string $mask Filter pattern.
	 *
	 * @return string[]
	 */
	public static function getFileList($path, $mask = '*.php')
	{
		$path = Translate\IO\Path::tidy(rtrim($path, '/'));
		return glob($path.'/{,.}'.$mask, GLOB_BRACE);
	}
}
