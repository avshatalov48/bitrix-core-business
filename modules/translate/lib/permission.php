<?php
namespace Bitrix\Translate;

use Bitrix\Main;


class Permission
{
	const WRITE = 'W';
	const READ = 'R';
	const DENY = 'D';

	/** @var array  */
	private static $initFolders = array();

	/**
	 * Checks user's access to path.
	 *
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public static function isAllowPath($path)
	{
		if (empty(self::$initFolders))
		{
			$initFolders = trim((string)Main\Config\Option::get('translate', 'INIT_FOLDERS', \Bitrix\Translate\TRANSLATE_DEFAULT_PATH));
			$initFolders = explode(',', $initFolders);
			foreach ($initFolders as $oneFolder)
			{
				self::$initFolders[] = trim($oneFolder);
			}
		}

		$path = (string)$path;
		$allowPath = false;
		foreach (self::$initFolders as $oneFolder)
		{
			if (strpos($path, $oneFolder) === 0)
			{
				$allowPath = true;
				break;
			}
		}

		return $allowPath;
	}
}
