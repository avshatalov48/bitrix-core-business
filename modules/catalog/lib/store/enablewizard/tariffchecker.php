<?php

namespace Bitrix\Catalog\Store\EnableWizard;

use Bitrix\Main\Loader;
use Bitrix\Bitrix24\Feature;

class TariffChecker
{
	public const FEATURE_ID = 'catalog_inventory_management_1c';

	public static function isOnecInventoryManagementRestricted(): bool
	{
		return (
			Loader::includeModule('bitrix24')
			&& !Feature::isFeatureEnabled(self::FEATURE_ID)
		);
	}
}
