<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Web\UserAgent;

use Bitrix\Main\Localization\Loc;

class DeviceType
{
	public const UNKNOWN = 0;
	public const DESKTOP = 1;
	public const MOBILE_PHONE = 2;
	public const TABLET = 3;
	public const TV = 4;

	public static function getDescription($lang = null): array
	{
		static $description = [];

		if (!isset($description[$lang ?? '']))
		{
			$description[$lang ?? ''] = [
				self::UNKNOWN => Loc::getMessage('main_devicetype_unknown', null, $lang),
				self::DESKTOP => Loc::getMessage('main_devicetype_desktop', null, $lang),
				self::MOBILE_PHONE => Loc::getMessage('main_devicetype_phone', null, $lang),
				self::TABLET => Loc::getMessage('main_devicetype_tablet', null, $lang),
				self::TV => Loc::getMessage('main_devicetype_tv', null, $lang),
			];
		}

		return $description[$lang ?? ''];
	}
}
