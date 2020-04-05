<?php
namespace Bitrix\Report\VisualConstructor\Helper;

/**
 * Common helper class, for some system methods
 * @package Bitrix\Report\VisualConstructor\Helper
 */
class Util
{
	/**
	 * Generate and return User unique id.
	 *
	 * @param string $prefix String to set of prefix of user unique Id.
	 * @return string
	 */
	public static function generateUserUniqueId($prefix = '')
	{
		global $USER;
		$gid = ($prefix ? $prefix . '_' : '') . randString(25) . '_' . $USER->getId() . randString(8);
		return $gid;
	}


	/**
	 * Get User Profile picture src by file index.
	 *
	 * @param int $avatarId Avatar file id.
	 * @param int $width Width size.
	 * @param int $height Height size.
	 * @return mixed|null
	 */
	public static function getAvatarSrc($avatarId, $width = 21, $height = 21)
	{
		static $cache = array();

		if(empty($avatarId))
		{
			return null;
		}

		$avatarId = (int) $avatarId;
		$key = $avatarId . " $width $height";

		if (!isset($cache[$key]))
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

				$cache[$key] = $src;
			}
		}

		return $cache[$key];
	}
}