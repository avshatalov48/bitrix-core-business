<?php

namespace Bitrix\Location\Infrastructure\Service;

use Bitrix\Location\Common\BaseService;
use Bitrix\Main\Loader;

class CurrentRegionFinderService extends BaseService
{
	/** @var CurrentRegionFinderService */
	protected static $instance;

	/**
	 * @return string|null
	 */
	public function getRegion(): ?string
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
			$result = \CIntranetUtils::getPortalZone();
		}
		else
		{
			$result = LANGUAGE_ID;
		}

		return $result;
	}
}
