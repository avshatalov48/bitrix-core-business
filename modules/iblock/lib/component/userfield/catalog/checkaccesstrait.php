<?php

namespace Bitrix\Iblock\Component\UserField\Catalog;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Main\Loader;

/**
 * Trait for checking access to catalog into UF components.
 */
trait CheckAccessTrait
{
	/**
	 * Checks access to catalog.
	 *
	 * @return bool
	 */
	protected function hasAccessToCatalog(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			return true;
		}

		$iblockId = (int)($this->arResult['userField']['SETTINGS']['IBLOCK_ID'] ?? 0);
		if ($iblockId <= 0)
		{
			return true;
		}

		$catalog = CatalogIblockTable::getRow([
			'select' => [
				'IBLOCK_ID',
			],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);

		if ($catalog === null)
		{
			return true;
		}

		return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
	}
}
