<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Common;

use Bitrix\Main\Loader;

/**
 * Class RegionFinder
 * @package Sale\Handlers\Delivery\YandexTaxi\Common
 * @internal
 */
final class RegionFinder
{
	/**
	 * @return string|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getCurrentRegion(): ?string
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

		return $result;
	}
}
