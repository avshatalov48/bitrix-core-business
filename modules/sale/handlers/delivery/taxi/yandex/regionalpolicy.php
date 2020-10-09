<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Loader;
use CBitrix24;

/**
 * Class RegionalPolicy
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class RegionalPolicy
{
	/**
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function isAvailableInCurrentRegion(): bool
	{
		return in_array($this->getCurrentRegion(), ['ru', 'kz', 'by']);
	}

	/**
	 * @return string|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function getCurrentRegion()
	{
		$result = null;

		if (Loader::includeModule('bitrix24'))
		{
			$result = CBitrix24::getLicensePrefix();
		}
		elseif (Loader::includeModule('intranet'))
		{
			$result = \CIntranetUtils::getPortalZone();
		}

		return $result;
	}
}
