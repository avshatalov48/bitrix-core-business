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
	public static function getFolderList(string $path): array
	{
		$path = Translate\IO\Path::tidy(\rtrim($path, '/'));
		if (defined('GLOB_BRACE'))
		{
			return \glob($path.'/{,.}*', \GLOB_BRACE | \GLOB_ONLYDIR);
		}
		return array_merge(
			\glob($path.'/.*', \GLOB_ONLYDIR),
			\glob($path.'/*', \GLOB_ONLYDIR)
		);
	}

	/**
	 * Return list full path of folders.
	 *
	 * @param string $path Path to check.
	 *
	 * @return string[]
	 */
	public static function getFileList(string $path): array
	{
		$path = Translate\IO\Path::tidy(\rtrim($path, '/'));
		if (defined('GLOB_BRACE'))
		{
			return \glob($path.'/{,.}*.php', \GLOB_BRACE);
		}
		return array_merge(
			\glob($path.'/.*.php'),
			\glob($path.'/*.php')
		);
	}
}
