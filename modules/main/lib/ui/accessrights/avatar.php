<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\UI\AccessRights;

class Avatar
{
	private static $cache = [];

	public static function getSrc($avatarId, $width = 58, $height = 58): ?string
	{
		if(empty($avatarId))
		{
			return null;
		}

		$avatarId = (int) $avatarId;
		$key = $avatarId . " $width $height";

		if (!isset(self::$cache[$key]))
		{
			$src = false;
			if ($avatarId > 0)
			{
				/** @noinspection PhpDynamicAsStaticMethodCallInspection */
				$imageFile = \CFile::getFileArray($avatarId);
				if ($imageFile !== false)
				{
					/** @noinspection PhpDynamicAsStaticMethodCallInspection */
					$fileTmp = \CFile::resizeImageGet(
						$imageFile,
						array("width" => $width, "height" => $height),
						BX_RESIZE_IMAGE_EXACT,
						false
					);
					$src = $fileTmp["src"];
				}

				self::$cache[$key] = $src;
			}
		}

		return self::$cache[$key];
	}
}