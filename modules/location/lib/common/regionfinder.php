<?php

namespace Bitrix\Location\Common;

use Bitrix\Main\Loader;

/**
 * Class RegionFinder
 * @package Bitrix\Location\Common
 */
class RegionFinder
{
	/**
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getCurrentRegion(): string
	{
		$result = null;

		if (Loader::includeModule('bitrix24'))
		{
			$licensePrefix = \CBitrix24::getLicensePrefix();
			if ($licensePrefix !== false)
			{
				$result = (string)$licensePrefix;
			}
		}
		elseif (Loader::includeModule('intranet'))
		{
			$result = (string)\CIntranetUtils::getPortalZone();
		}
		elseif (defined('LANGUAGE_ID'))
		{
			$result = LANGUAGE_ID;
		}

		if (!$result)
		{
			$result = 'en';
		}

		return $result;
	}
}
