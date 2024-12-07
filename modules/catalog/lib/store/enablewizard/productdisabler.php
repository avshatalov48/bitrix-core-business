<?php

namespace Bitrix\Catalog\Store\EnableWizard;

use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Application;
use Bitrix\Main\Type\Collection;

final class ProductDisabler
{
	public static function getIblocksForDisabling(): array
	{
		$catalogIblocksIterator = CatalogIblockTable::getList([
			'select' => ['IBLOCK_ID'],
			'filter' => [
				'=PRODUCT_IBLOCK_ID' => 0,
			]
		]);
		$iblocks = $catalogIblocksIterator->fetchAll();
		$iblocks = array_column($iblocks, 'IBLOCK_ID');
		Collection::normalizeArrayValuesByInt($iblocks);

		return $iblocks;
	}

	public static function disable(): void
	{
		$iblocks = self::getIblocksForDisabling();
		if (empty($iblocks))
		{
			return;
		}

		foreach ($iblocks as $iblockId)
		{
			\CIBlock::disableClearTagCache();
			\Bitrix\Iblock\PropertyIndex\Manager::enableDeferredIndexing();

			Application::getConnection()->query("
				UPDATE " . ElementTable::getTableName() . "
				SET ACTIVE = 'N'
				WHERE IBLOCK_ID = " . (int)$iblockId . "
			");

			\CIBlock::enableClearTagCache();
			\Bitrix\Iblock\PropertyIndex\Manager::disableDeferredIndexing();
			\CIBlock::clearIblockTagCache($iblockId);
			\Bitrix\Iblock\PropertyIndex\Manager::runDeferredIndexing($iblockId);
		}
	}
}
