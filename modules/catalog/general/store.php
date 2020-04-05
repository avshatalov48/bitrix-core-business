<?php
use Bitrix\Main,
	Bitrix\Catalog,
	Bitrix\Iblock;

IncludeModuleLangFile(__FILE__);

class CAllCatalogStore
{
	protected static function CheckFields($action, &$arFields)
	{
		if (array_key_exists("ADDRESS", $arFields) && (string)$arFields["ADDRESS"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("CS_EMPTY_ADDRESS"));
			$arFields["ADDRESS"] = ' ';
		}
		if(($action == 'ADD') &&
			((is_set($arFields, "IMAGE_ID") && strlen($arFields["IMAGE_ID"]) < 0)))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("CS_WRONG_IMG"));
			return false;
		}
		if(($action == 'ADD') &&
			((is_set($arFields, "LOCATION_ID") && intval($arFields["LOCATION_ID"]) <= 0)))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("CS_WRONG_LOC"));
			return false;
		}
		if(($action == 'UPDATE') && is_set($arFields, "ID"))
			unset($arFields["ID"]);

		if(($action == 'UPDATE') && strlen($arFields["IMAGE_ID"]) <= 0)
			unset($arFields["IMAGE_ID"]);

		if(isset($arFields["ISSUING_CENTER"]) && ($arFields["ISSUING_CENTER"]) !== 'Y')
		{
			$arFields["ISSUING_CENTER"] = 'N';
		}
		if(isset($arFields["SHIPPING_CENTER"]) && ($arFields["SHIPPING_CENTER"]) !== 'Y')
		{
			$arFields["SHIPPING_CENTER"] = 'N';
		}
		if(isset($arFields["SITE_ID"]) && ($arFields["SITE_ID"]) === '0')
		{
			$arFields["SITE_ID"] = '';
		}
		if (isset($arFields['CODE']) && $arFields['CODE'] === '')
			$arFields['CODE'] = false;

		return true;
	}

	public static function Update($id, $arFields)
	{
		global $DB;
		$id = (int)$id;
		if ($id <= 0)
			return false;

		foreach (GetModuleEvents("catalog", "OnBeforeCatalogStoreUpdate", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($id, &$arFields))===false)
				return false;
		}

		if(array_key_exists('DATE_CREATE',$arFields))
			unset($arFields['DATE_CREATE']);
		if(array_key_exists('DATE_MODIFY', $arFields))
			unset($arFields['DATE_MODIFY']);
		if(array_key_exists('DATE_STATUS', $arFields))
			unset($arFields['DATE_STATUS']);
		if(array_key_exists('CREATED_BY', $arFields))
			unset($arFields['CREATED_BY']);

		$arFields['~DATE_MODIFY'] = $DB->GetNowFunction();

		if (!self::CheckFields('UPDATE', $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_catalog_store", $arFields);

		$bNeedConversion = false;
		if (!empty($strUpdate))
		{
			if (isset($arFields['ACTIVE']))
			{
				$row = Catalog\StoreTable::getList(array(
					'select' => array('ACTIVE'),
					'filter' => array('=ID' => $id)
				))->fetch();
				if (!empty($row))
					$bNeedConversion = ($row['ACTIVE'] != $arFields['ACTIVE']);
				unset($row);
			}

			$strSql = "update b_catalog_store set ".$strUpdate." where ID = ".$id;
			if(!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
				return false;
			CCatalogStoreControlUtil::clearStoreName($id);
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
		global $DB;
		$id = intval($id);
		if($id > 0)
		{
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
	 *
	 * @param int $storeId		Store id.
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\Db\SqlQueryException
	 */
	public static function recalculateStoreBalances($storeId)
	{
		$storeId = (int)$storeId;
		if ($storeId <= 0)
			return false;

		if (!Catalog\Config\State::isUsedInventoryManagement())
			return true;

		$iterator = Catalog\StoreTable::getList([
			'select' => ['ID'],
			'filter' => ['=ID' => $storeId]
		]);
		$row = $iterator->fetch();
		unset($iterator);
		if (empty($row))
			return false;
		unset($row);

		$errors = [];

		$connection = Main\Application::getConnection();

		Iblock\PropertyIndex\Manager::enableDeferredIndexing();
		Catalog\Product\Sku::enableDeferredCalculation();

		$iblockIds = [];

		$startId = 0;
		do
		{
			$found = false;
			$productIds = [];

			$iterator = Catalog\StoreProductTable::getList([
				'select' => ['ID', 'PRODUCT_ID'],
				'filter' => ['>ID' => $startId, '=STORE_ID' => $storeId, '!=AMOUNT' => 0],
				'order' => ['ID' => 'ASC'],
				'limit' => 200
			]);
			while ($row = $iterator->fetch())
			{
				$found = true;
				$startId = (int)$row['ID'];
				$productIds[] = (int)$row['PRODUCT_ID'];
			}
			unset($row, $iterator);
			if (!empty($productIds))
				Main\Type\Collection::normalizeArrayValuesByInt($productIds, true);

			if (!empty($productIds))
			{
				$products = [];
				$iterator = Catalog\Model\Product::getList([
					'select' => ['ID', 'QUANTITY_RESERVED', 'IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID'],
					'filter' => ['@ID' => $productIds],
					'order' => ['ID' => 'ASC']
				]);
				while ($row = $iterator->fetch())
				{
					if ($row['IBLOCK_ID'] === null)
						continue;
					$rowId = (int)$row['ID'];
					$iblock = (int)$row['IBLOCK_ID'];
					$iblockIds[$iblock] = $iblock;
					$products[$rowId] = [
						'QUANTITY_RESERVED' => (float)$row['QUANTITY_RESERVED'],
						'IBLOCK_ID' => $iblock
					];
				}
				unset($row, $iterator);

				if (!empty($products))
				{
					$query = 'select SUM(CSP.AMOUNT) as PRODUCT_QUANTITY, CSP.PRODUCT_ID '.
						'from b_catalog_store_product CSP inner join b_catalog_store CS on CS.ID = CSP.STORE_ID '.
						'where CSP.PRODUCT_ID in ('.implode(',', array_keys($products)).') and CS.ACTIVE = "Y" '.
						'group by CSP.PRODUCT_ID';
					$iterator = $connection->query($query);
					while ($row = $iterator->fetch())
					{
						$rowId = (int)$row['PRODUCT_ID'];
						$data = [
							'fields' => [
								'QUANTITY' => (float)$row['PRODUCT_QUANTITY'] - $products[$rowId]['QUANTITY_RESERVED']
							],
							'external_fields' => [
								'IBLOCK_ID' => $products[$rowId]['IBLOCK_ID']
							]
						];
						$resultInternal = Catalog\Model\Product::update($rowId, $data);
						if (!$resultInternal->isSuccess())
							$errors[$rowId] = $resultInternal->getErrorMessages();

						unset($products[$rowId]);
					}
					unset($row, $iterator, $query);
				}

				if (!empty($products))
				{
					foreach ($products as $rowId => $rowData)
					{
						$data = [
							'fields' => [
								'QUANTITY' => -$products[$rowId]['QUANTITY_RESERVED']
							],
							'external_fields' => [
								'IBLOCK_ID' => $products[$rowId]['IBLOCK_ID']
							]
						];
						$resultInternal = Catalog\Model\Product::update($rowId, $data);
						if (!$resultInternal->isSuccess())
							$errors[$rowId] = $resultInternal->getErrorMessages();

						unset($products[$rowId]);
					}
					unset($rowId, $rowData);
				}
				unset($products);
			}
			unset($productIds);
		}
		while ($found);

		unset($connection);

		Catalog\Product\Sku::disableDeferredCalculation();
		Catalog\Product\Sku::calculate();

		Iblock\PropertyIndex\Manager::disableDeferredIndexing();
		if (!empty($iblockIds))
		{
			foreach ($iblockIds as $iblock)
				Iblock\PropertyIndex\Manager::runDeferredIndexing($iblock);
		}
		unset($iblockIds);

		Catalog\Model\Product::clearCache();

		return empty($errors);
	}
}