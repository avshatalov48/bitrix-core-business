<?php
use Bitrix\Catalog;

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

	public static function recalculateStoreBalances($id)
	{
		global $DB;
		$arFields = array();
		if(COption::GetOptionString('catalog','default_use_store_control','N') != 'Y')
		{
			return true;
		}
		$dbStoreProduct = CCatalogStoreProduct::GetList(array(), array("STORE_ID" => $id, "!AMOUNT" => 0), false, false, array("PRODUCT_ID", "AMOUNT"));
		while($arStoreProduct = $dbStoreProduct->Fetch())
		{
			$dbAmount = $DB->Query("select SUM(SP.AMOUNT) as SUM, CP.QUANTITY_RESERVED as RESERVED, CS.ACTIVE FROM b_catalog_store_product SP inner join b_catalog_product CP on SP.PRODUCT_ID = CP.ID inner join b_catalog_store CS on SP.STORE_ID = CS.ID where SP.PRODUCT_ID = ".$arStoreProduct['PRODUCT_ID']." and CS.ACTIVE = 'Y' group by QUANTITY_RESERVED, ACTIVE", true);
			if($arAmount = $dbAmount->Fetch())
			{
				$arFields["QUANTITY"] = doubleval($arAmount["SUM"] - $arAmount["RESERVED"]);
			}
			else
			{
				if($arReservAmount = CCatalogProduct::GetByID($arStoreProduct['PRODUCT_ID']))
				{
					$arFields["QUANTITY"] = doubleval(0 - $arReservAmount["QUANTITY_RESERVED"]);
				}
			}
			CCatalogProduct::Update($arStoreProduct["PRODUCT_ID"], $arFields);
		}
		return true;
	}
}