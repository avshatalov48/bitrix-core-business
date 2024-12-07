<?php

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Iblock;

IncludeModuleLangFile(__FILE__);

class CAllCatalogStore
{
	protected static function CheckFields($action, &$arFields)
	{
		global $DB;
		global $USER;

		if ($action !== 'ADD' && $action !== 'UPDATE')
		{
			return false;
		}

		$currentUserId = false;
		if (isset($USER) && $USER instanceof CUser)
		{
			$currentUserId = (int)$USER->GetID();
			if ($currentUserId <= 0)
			{
				$currentUserId = false;
			}
		}

		if ($action === 'ADD')
		{
			$arFields += [
				'ACTIVE' => 'Y',
				'IMAGE_ID' => false,
				'LOCATION_ID' => false,
				'ISSUING_CENTER' => 'N',
				'SHIPPING_CENTER' => 'N',
				'SITE_ID' => false,
				'CODE' => false,
				'IS_DEFAULT' => 'N',
			];

			$allowList = [
				'TITLE' => true,
				'ACTIVE' => true,
				'ADDRESS' => true,
				'DESCRIPTION' => true,
				'GPS_N' => true,
				'GPS_S' => true,
				'IMAGE_ID' => true,
				'LOCATION_ID' => true,
				'USER_ID' => true,
				'MODIFIED_BY' => true,
				'PHONE' => true,
				'SCHEDULE' => true,
				'XML_ID' => true,
				'SORT' => true,
				'EMAIL' => true,
				'ISSUING_CENTER' => true,
				'SHIPPING_CENTER' => true,
				'SITE_ID' => true,
				'CODE' => true,
				'IS_DEFAULT' => true,
			];
		}
		else
		{
			$allowList = [
				'TITLE' => true,
				'ACTIVE' => true,
				'ADDRESS' => true,
				'DESCRIPTION' => true,
				'GPS_N' => true,
				'GPS_S' => true,
				'IMAGE_ID' => true,
				'LOCATION_ID' => true,
				'MODIFIED_BY' => true,
				'PHONE' => true,
				'SCHEDULE' => true,
				'XML_ID' => true,
				'SORT' => true,
				'EMAIL' => true,
				'ISSUING_CENTER' => true,
				'SHIPPING_CENTER' => true,
				'SITE_ID' => true,
				'CODE' => true,
				'IS_DEFAULT' => true,
			];
		}

		$arFields = array_intersect_key($arFields, $allowList);
		$arFields['~DATE_MODIFY'] = $DB->GetNowFunction();
		$arFields['MODIFIED_BY'] = $arFields['MODIFIED_BY'] ?? $currentUserId;
		if ($action === 'ADD')
		{
			$arFields['~DATE_CREATE'] = $DB->GetNowFunction();
			$arFields['USER_ID'] = $arFields['USER_ID'] ?? $currentUserId;
		}

		if (array_key_exists("ADDRESS", $arFields) && (string)$arFields["ADDRESS"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("CS_EMPTY_ADDRESS"));
			return false;
		}

		if (array_key_exists('IMAGE_ID', $arFields))
		{
			self::prepareImage($arFields, 'IMAGE_ID');
		}

		if(isset($arFields["ISSUING_CENTER"]) && ($arFields["ISSUING_CENTER"]) !== 'Y')
		{
			$arFields["ISSUING_CENTER"] = 'N';
		}
		if(isset($arFields["SHIPPING_CENTER"]) && ($arFields["SHIPPING_CENTER"]) !== 'Y')
		{
			$arFields["SHIPPING_CENTER"] = 'N';
		}
		if(isset($arFields["SITE_ID"]) && ($arFields["SITE_ID"] === '0' || $arFields["SITE_ID"] === ''))
		{
			$arFields["SITE_ID"] = false;
		}
		if (isset($arFields['CODE']) && $arFields['CODE'] === '')
		{
			$arFields['CODE'] = false;
		}
		if (isset($arFields['IS_DEFAULT']) && ($arFields['IS_DEFAULT']) !== 'Y')
		{
			$arFields['IS_DEFAULT'] = 'N';
		}

		return true;
	}

	private static function prepareImage(array &$fields, $fieldName): void
	{
		if (
			$fields[$fieldName] === null
			|| $fields[$fieldName] === 'null'
			|| $fields[$fieldName] === ''
			|| $fields[$fieldName] === false
		)
		{
			$fields[$fieldName] = false;
		}
		elseif (
			is_string($fields[$fieldName])
			|| is_int($fields[$fieldName])
		)
		{
			$fields[$fieldName] = (int)$fields[$fieldName];
			if ($fields[$fieldName] <= 0)
			{
				unset($fields[$fieldName]);
			}
		}
		elseif (is_array($fields[$fieldName]))
		{
			self::prepareImageArray($fields, $fieldName);
		}
		else
		{
			unset($fields[$fieldName]);
		}
	}

	private static function prepareImageArray(array &$fields, $fieldName): void
	{
		if (empty($fields[$fieldName]))
		{
			unset($fields[$fieldName]);

			return;
		}

		if (!isset($fields[$fieldName]['name']) && !isset($fields[$fieldName]['del']))
		{
			unset($fields[$fieldName]);

			return;
		}

		$fields[$fieldName]['MODULE_ID'] = 'catalog';
	}

	public static function Update($id, $arFields)
	{
		global $DB;
		$id = (int)$id;
		if ($id <= 0)
			return false;

		$store = Catalog\StoreTable::getRow([
			'select' => [
				'ID',
				'IMAGE_ID',
				'ACTIVE',
			],
			'filter' => [
				'=ID' => $id,
			]
		]);
		if (empty($store))
		{
			return false;
		}

		if ($store['IMAGE_ID'] !== null)
		{
			$store['IMAGE_ID'] = (int)$store['IMAGE_ID'];
			if ($store['IMAGE_ID'] <= 0)
			{
				$store['IMAGE_ID'] = null;
			}
		}

		foreach (GetModuleEvents("catalog", "OnBeforeCatalogStoreUpdate", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($id, &$arFields))===false)
				return false;
		}

		if (!self::CheckFields('UPDATE', $arFields))
			return false;

		if (isset($arFields['IMAGE_ID']))
		{
			if (is_array($arFields['IMAGE_ID']))
			{
				$arFields['IMAGE_ID']['old_file'] = $store['IMAGE_ID'];
				CFile::SaveForDB($arFields, 'IMAGE_ID', 'catalog');
			}
			elseif ($store['IMAGE_ID'] !== null)
			{
				CFile::Delete($store['IMAGE_ID']);
			}
		}
		$strUpdate = $DB->PrepareUpdate("b_catalog_store", $arFields);

		$bNeedConversion = false;
		if (!empty($strUpdate))
		{
			if (isset($arFields['ACTIVE']))
			{
				$bNeedConversion = ($store['ACTIVE'] !== $arFields['ACTIVE']);
			}

			$strSql = "update b_catalog_store set ".$strUpdate." where ID = ".$id;
			if(!$DB->Query($strSql))
				return false;
			CCatalogStoreControlUtil::clearStoreName($id);

			Catalog\StoreTable::cleanCache();
		}

		if($bNeedConversion)
		{
			self::recalculateStoreBalances($id);
		}

		foreach(GetModuleEvents("catalog", "OnCatalogStoreUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($id, $arFields));

		return $id;
	}

	public static function Delete($id)
	{
		global $DB, $USER_FIELD_MANAGER;
		$id = (int)$id;
		if ($id > 0)
		{
			$store = Catalog\StoreTable::getRow([
				'select' => [
					'ID',
					'IMAGE_ID'
				],
				'filter' => [
					'=ID' => $id,
				]
			]);
			if (empty($store))
			{
				return false;
			}

			if ($store['IMAGE_ID'] !== null)
			{
				$store['IMAGE_ID'] = (int)$store['IMAGE_ID'];
				if ($store['IMAGE_ID'] <= 0)
				{
					$store['IMAGE_ID'] = null;
				}
			}

			foreach (GetModuleEvents("catalog", "OnBeforeCatalogStoreDelete", true) as $arEvent)
			{
				if(ExecuteModuleEventEx($arEvent, array($id))===false)
					return false;
			}

			$dbDocs = $DB->Query("select ID from b_catalog_docs_element where STORE_FROM = ".$id." or STORE_TO = ".$id, true);
			if($bStoreHaveDocs = $dbDocs->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("CS_STORE_HAVE_DOCS"));
				return false;
			}

			$DB->Query("delete from b_catalog_store_product where STORE_ID = ".$id, true);
			$DB->Query("delete from b_catalog_store where ID = ".$id, true);
			if ($store['IMAGE_ID'] !== null)
			{
				CFile::Delete($store['IMAGE_ID']);
			}

			$USER_FIELD_MANAGER->Delete(Catalog\StoreTable::getUfId(), $id);

			Catalog\StoreTable::cleanCache();

			foreach(GetModuleEvents("catalog", "OnCatalogStoreDelete", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($id));

			self::recalculateStoreBalances($id);
			CCatalogStoreControlUtil::clearStoreName($id);
			return true;
		}
		return false;
	}

	/**
	 * Recalculate quantity for store.
	 */
	public static function recalculateStoreBalances(int $storeId): void
	{
		if ($storeId <= 0)
		{
			return;
		}

		if (!Catalog\Config\State::isUsedInventoryManagement())
		{
			return;
		}

		$store = Catalog\StoreTable::getList([
			'select' => ['ID'],
			'filter' => ['=ID' => $storeId],
		])->fetch();
		if (!$store)
		{
			return;
		}

		Iblock\PropertyIndex\Manager::enableDeferredIndexing();
		Catalog\Product\Sku::enableDeferredCalculation();

		$iblockIds = [];
		$startId = 0;
		do
		{
			$hasMore = false;
			$productIds = [];

			$storeProductList = Catalog\StoreProductTable::getList([
				'select' => ['ID', 'PRODUCT_ID'],
				'filter' => [
					'>ID' => $startId,
					'=STORE_ID' => $storeId,
					'!=AMOUNT' => 0,
				],
				'order' => ['ID' => 'ASC'],
				'limit' => 200,
			]);
			while ($row = $storeProductList->fetch())
			{
				$hasMore = true;
				$startId = (int)$row['ID'];
				$productIds[] = (int)$row['PRODUCT_ID'];
			}

			if (!empty($productIds))
			{
				Main\Type\Collection::normalizeArrayValuesByInt($productIds);
			}

			self::recalculateProductsBalancesInternal($productIds, $iblockIds);
		} while ($hasMore);

		Catalog\Product\Sku::disableDeferredCalculation();
		Catalog\Product\Sku::calculate();

		Iblock\PropertyIndex\Manager::disableDeferredIndexing();
		if (!empty($iblockIds))
		{
			foreach ($iblockIds as $iblock)
			{
				Iblock\PropertyIndex\Manager::runDeferredIndexing($iblock);
			}
		}

		Catalog\Model\Product::clearCache();
	}

	/**
	 * Recalculate quantity for specified product ids
	 */
	public static function recalculateProductsBalances(array $productIds): void
	{
		if (!Catalog\Config\State::isUsedInventoryManagement())
		{
			return;
		}

		Iblock\PropertyIndex\Manager::enableDeferredIndexing();
		Catalog\Product\Sku::enableDeferredCalculation();

		$iblockIds = [];
		self::recalculateProductsBalancesInternal($productIds, $iblockIds);

		Catalog\Product\Sku::disableDeferredCalculation();
		Catalog\Product\Sku::calculate();

		Iblock\PropertyIndex\Manager::disableDeferredIndexing();
		if (!empty($iblockIds))
		{
			foreach ($iblockIds as $iblock)
			{
				Iblock\PropertyIndex\Manager::runDeferredIndexing($iblock);
			}
		}

		Catalog\Model\Product::clearCache();
	}

	private static function recalculateProductsBalancesInternal(array $productIds, array &$iblockIds): void
	{
		if (empty($productIds))
		{
			return;
		}

		$products = [];
		$foundedProductIds = [];

		$productsList = Catalog\Model\Product::getList([
			'select' => [
				'ID',
				'QUANTITY_RESERVED',
				'IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID',
			],
			'filter' => ['@ID' => $productIds],
			'order' => ['ID' => 'ASC'],
		]);
		while ($row = $productsList->fetch())
		{
			if ($row['IBLOCK_ID'] === null)
			{
				continue;
			}

			$id = (int)$row['ID'];

			$iblockId = (int)$row['IBLOCK_ID'];
			$iblockIds[$iblockId] = $iblockId;
			$products[$id] = [
				'QUANTITY_RESERVED' => (float)$row['QUANTITY_RESERVED'],
				'IBLOCK_ID' => $iblockId,
			];
			$foundedProductIds[] = $id;
		}
		unset($row, $productsList);

		if (empty($products))
		{
			return;
		}

		$storeProductQuantityList = Main\Application::getConnection()->query("
			SELECT
				SUM(CSP.AMOUNT) AS PRODUCT_QUANTITY,
				CSP.PRODUCT_ID
			FROM b_catalog_store_product CSP
			INNER JOIN b_catalog_store CS ON CS.ID = CSP.STORE_ID
			WHERE
				CSP.PRODUCT_ID IN (" . implode(',', $foundedProductIds) . ")
				AND CS.ACTIVE = 'Y'
			GROUP BY CSP.PRODUCT_ID
		");
		while ($row = $storeProductQuantityList->fetch())
		{
			$productId = (int)$row['PRODUCT_ID'];

			Catalog\Model\Product::update(
				$productId,
				[
					'fields' => [
						'QUANTITY' => (float)$row['PRODUCT_QUANTITY'] - $products[$productId]['QUANTITY_RESERVED'],
					],
					'external_fields' => [
						'IBLOCK_ID' => $products[$productId]['IBLOCK_ID'],
					],
				]
			);

			unset($products[$productId]);
		}
		unset($row, $storeProductQuantityList);
		unset($foundedProductIds);

		if (!empty($products))
		{
			foreach ($products as $rowId => $rowData)
			{
				Catalog\Model\Product::update(
					$rowId,
					[
						'fields' => [
							'QUANTITY' => ($rowData['QUANTITY_RESERVED'] != 0 ? -$rowData['QUANTITY_RESERVED'] : 0),
						],
						'external_fields' => [
							'IBLOCK_ID' => $rowData['IBLOCK_ID'],
						],
					]
				);
			}
		}

		Catalog\Model\Product::clearCache();
	}
}
