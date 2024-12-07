<?php

namespace Bitrix\Catalog\Store\EnableWizard;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\v2\Integration\Landing\ShopManager;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Type\Collection;

class ConditionsChecker
{
	public static function hasConductedDocumentsOrQuantities(): bool
	{
		return (
			self::doesProductWithQuantityExist()
			|| self::doesConductedDocumentExist()
		);
	}

	public static function areTherePublishedShops(): bool
	{
		return (new ShopManager())->areTherePublishedShops();
	}

	public static function areThereActiveProducts(): bool
	{
		$iblockIds = ProductDisabler::getIblocksForDisabling();
		Collection::normalizeArrayValuesByInt($iblockIds);
		if (empty($iblockIds))
		{
			return false;
		}

		$iblockIds = implode(', ', $iblockIds);

		return (bool)Application::getConnection()->query("
			SELECT ie.ACTIVE
			FROM b_catalog_product cp
			JOIN b_iblock_element ie on ie.ID = cp.ID
			WHERE ie.ACTIVE = 'Y'
			AND ie.IBLOCK_ID in ($iblockIds)
			LIMIT 1
		")->fetch();
	}

	public static function doesProductWithQuantityExist(): bool
	{
		$connection = Application::getConnection();

		$productTypes = new SqlExpression('(?i, ?i)', ProductTable::TYPE_PRODUCT, ProductTable::TYPE_OFFER);
		$query = $connection->query("
			select ID from b_catalog_product cp
			where TYPE in {$productTypes} and (QUANTITY != 0 or QUANTITY_RESERVED != 0)
			limit 1
		");

		if ($query->fetch())
		{
			return true;
		}

		$query = $connection->query("
			select ID from b_catalog_store_product csp
			where AMOUNT != 0 or QUANTITY_RESERVED != 0
			limit 1
		");

		if ($query->fetch())
		{
			return true;
		}

		return false;
	}

	public static function doesConductedDocumentExist(): bool
	{
		$iterator = StoreDocumentTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=STATUS' => 'Y',
			],
			'limit' => 1,
		]);
		$row = $iterator->fetch();
		unset($iterator);

		return !empty($row);
	}
}
