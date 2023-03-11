<?php

use Bitrix\Main;
use Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/store.php");

class CCatalogStore extends CAllCatalogStore
{
	/** Add new store in table b_catalog_store,
	 * @static
	 * @param $arFields
	 * @return bool|int
	 */
	public static function Add($arFields)
	{
		/** @global CDataBase $DB */

		global $DB;

		foreach (GetModuleEvents("catalog", "OnBeforeCatalogStoreAdd", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				return false;
		}

		if (!self::CheckFields('ADD',$arFields))
			return false;

		if (
			isset($arFields['IMAGE_ID'])
			&& is_array($arFields['IMAGE_ID'])
		)
		{
			CFile::SaveForDB($arFields, 'IMAGE_ID', 'catalog');
		}
		$arInsert = $DB->PrepareInsert("b_catalog_store", $arFields);
		$strSql = "INSERT INTO b_catalog_store (".$arInsert[0].") VALUES(".$arInsert[1].")";

		$res = $DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);
		if(!$res)
			return false;
		$lastId = (int)$DB->LastID();

		Catalog\StoreTable::cleanCache();

		foreach(GetModuleEvents("catalog", "OnCatalogStoreAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($lastId, $arFields));

		return $lastId;
	}

	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		$defaultList = [
			'ID',
			'ACTIVE',
			'TITLE',
			'PHONE',
			'SCHEDULE',
			'ADDRESS',
			'DESCRIPTION',
			'GPS_N',
			'GPS_S',
			'IMAGE_ID',
			'DATE_CREATE',
			'DATE_MODIFY',
			'USER_ID',
			'XML_ID',
			'SORT',
			'EMAIL',
			'ISSUING_CENTER',
			'SHIPPING_CENTER',
			'SITE_ID',
			'CODE',
			'IS_DEFAULT',
		];

		if (!is_array($arSelectFields))
		{
			$arSelectFields = [];
		}

		$productIds = [];
		$productFilterExists = array_key_exists('PRODUCT_ID', $arFilter);
		if ($productFilterExists)
		{
			$productIds = is_array($arFilter['PRODUCT_ID']) ? $arFilter['PRODUCT_ID'] : [$arFilter['PRODUCT_ID']];
			Main\Type\Collection::normalizeArrayValuesByInt($productIds);
			$productFilterExists = !empty($productIds);
			unset($arFilter['PRODUCT_ID']);
		}

		if (empty($arSelectFields))
		{
			$arSelectFields = $defaultList;
		}

		$arFields = [];
		$arFields["ID"] = [
			"FIELD" => "CS.ID",
			"TYPE" => "int",
		];
		$arFields["ACTIVE"] = [
			"FIELD" => "CS.ACTIVE",
			"TYPE" => "string",
		];
		$arFields["TITLE"] = [
			"FIELD" => "CS.TITLE",
			"TYPE" => "string",
		];
		$arFields["PHONE"] = [
			"FIELD" => "CS.PHONE",
			"TYPE" => "string",
		];
		$arFields["SCHEDULE"] = [
			"FIELD" => "CS.SCHEDULE",
			"TYPE" => "string",
		];
		$arFields["ADDRESS"] = [
			"FIELD" => "CS.ADDRESS",
			"TYPE" => "string",
		];
		$arFields["DESCRIPTION"] = [
			"FIELD" => "CS.DESCRIPTION",
			"TYPE" => "string",
		];
		$arFields["GPS_N"] = [
			"FIELD" => "CS.GPS_N",
			"TYPE" => "string",
		];
		$arFields["GPS_S"] = [
			"FIELD" => "CS.GPS_S",
			"TYPE" => "string",
		];
		$arFields["IMAGE_ID"] = [
			"FIELD" => "CS.IMAGE_ID",
			"TYPE" => "int",
		];
		$arFields["LOCATION_ID"] = [
			"FIELD" => "CS.LOCATION_ID",
			"TYPE" => "int",
		];
		$arFields["DATE_CREATE"] = [
			"FIELD" => "CS.DATE_CREATE",
			"TYPE" => "datetime",
		];
		$arFields["DATE_MODIFY"] = [
			"FIELD" => "CS.DATE_MODIFY",
			"TYPE" => "datetime",
		];
		$arFields["USER_ID"] = [
			"FIELD" => "CS.USER_ID",
			"TYPE" => "int",
		];
		$arFields["MODIFIED_BY"] = [
			"FIELD" => "CS.MODIFIED_BY",
			"TYPE" => "int",
		];
		$arFields["XML_ID"] = [
			"FIELD" => "CS.XML_ID",
			"TYPE" => "string",
		];
		$arFields["SORT"] = [
			"FIELD" => "CS.SORT",
			"TYPE" => "int",
		];
		$arFields["EMAIL"] = [
			"FIELD" => "CS.EMAIL",
			"TYPE" => "string",
		];
		$arFields["ISSUING_CENTER"] = [
			"FIELD" => "CS.ISSUING_CENTER",
			"TYPE" => "char",
		];
		$arFields["SHIPPING_CENTER"] = [
			"FIELD" => "CS.SHIPPING_CENTER",
			"TYPE" => "char",
		];
		$arFields["SITE_ID"] = [
			"FIELD" => "CS.SITE_ID",
			"TYPE" => "string",
		];
		$arFields["CODE"] = [
			"FIELD" => "CS.CODE",
			"TYPE" => "string",
		];
		$arFields["IS_DEFAULT"] = [
			"FIELD" => "CS.IS_DEFAULT",
			"TYPE" => "char",
		];
		if ($productFilterExists)
		{
			$arFields["PRODUCT_AMOUNT"] = [
				"FIELD" => "CP.AMOUNT",
				"TYPE" => "double",
				"FROM" => "LEFT JOIN b_catalog_store_product CP ON "
					. "(CS.ID = CP.STORE_ID AND CP.PRODUCT_ID IN (" . implode(',', $productIds) . "))"
				,
			];
			if (in_array('*', $arSelectFields) || in_array('PRODUCT_AMOUNT', $arSelectFields))
			{
				$arFields["ELEMENT_ID"] = [
					"FIELD" => "CP.PRODUCT_ID",
					"TYPE" => "int",
				];
			}
		}

		if (!is_array($arOrder))
		{
			$arOrder = [];
		}
		if (!empty($arOrder))
		{
			$arOrder = array_change_key_case($arOrder, CASE_UPPER);
			foreach (array_keys($arOrder) as $field)
			{
				$arOrder[$field] = strtoupper($arOrder[$field]);
				if ($arOrder[$field] !== 'DESC')
				{
					$arOrder[$field] = 'ASC';
				}
			}
		}

		$userField = new CUserTypeSQL();
		$userField->SetEntity("CAT_STORE", "CS.ID");
		$userField->SetSelect($arSelectFields);
		$userField->SetFilter($arFilter);
		$userField->SetOrder($arOrder);

		$strUfFilter = $userField->GetFilter();
		$strSqlUfFilter = ($strUfFilter <> '') ? " (".$strUfFilter.") " : "";


		$strSqlUfOrder = "";
		foreach ($arOrder as $field => $by)
		{
			$field = $userField->GetOrder($field);
			if (empty($field))
				continue;

			if ($strSqlUfOrder <> '')
				$strSqlUfOrder .= ', ';
			$strSqlUfOrder .= $field." ".$by;
		}

		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);
		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." ".$userField->GetSelect()." FROM b_catalog_store CS ".$arSqls["FROM"]. " ".$userField->GetJoin("CS.ID");
			if (!empty($arSqls["WHERE"]))
				$strSql .= " WHERE ".$arSqls["WHERE"]." ";

			if ($arSqls["WHERE"] <> '' && $strSqlUfFilter <> '')
				$strSql .= " AND ".$strSqlUfFilter." ";
			elseif ($arSqls["WHERE"] == '' && $strSqlUfFilter <> '')
				$strSql .= " WHERE ".$strSqlUfFilter." ";

			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}
		$strSql = "SELECT ".$arSqls["SELECT"]." ".$userField->GetSelect()." FROM b_catalog_store CS ".$arSqls["FROM"]." ".$userField->GetJoin("CS.ID");
		if (!empty($arSqls["WHERE"]))
			$strSql .= " WHERE ".$arSqls["WHERE"]." ";

		if ($arSqls["WHERE"] <> '' && $strSqlUfFilter <> '')
			$strSql .= " AND ".$strSqlUfFilter." ";
		elseif ($arSqls["WHERE"] == '' && $strSqlUfFilter <> '')
			$strSql .= " WHERE ".$strSqlUfFilter." ";

		if (!empty($arSqls["GROUPBY"]))
			$strSql .= " GROUP BY ".$arSqls["GROUPBY"];

		if (!empty($arSqls["ORDERBY"]))
			$strSql .= " ORDER BY ".$arSqls["ORDERBY"];
		elseif ($arSqls["ORDERBY"] == '' && $strSqlUfOrder <> '')
			$strSql .= " ORDER BY ".$strSqlUfOrder;

		$intTopCount = 0;
		$boolNavStartParams = (!empty($arNavStartParams) && is_array($arNavStartParams));
		if ($boolNavStartParams && array_key_exists('nTopCount', $arNavStartParams))
			$intTopCount = intval($arNavStartParams["nTopCount"]);

		if ($boolNavStartParams && 0 >= $intTopCount)
		{
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM b_catalog_store CS ".$arSqls["FROM"]. " ".$userField->GetJoin("CS.ID");
			if (!empty($arSqls["WHERE"]))
				$strSql_tmp .= " WHERE ".$arSqls["WHERE"];

			if ($arSqls["WHERE"] <> '' && $strSqlUfFilter <> '')
				$strSql_tmp .= " AND ".$strSqlUfFilter." ";
			elseif ($arSqls["WHERE"] == '' && $strSqlUfFilter <> '')
				$strSql_tmp .= " WHERE ".$strSqlUfFilter." ";

			if (!empty($arSqls["GROUPBY"]))
				$strSql_tmp .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$cnt = 0;
			if (empty($arSqls["GROUPBY"]))
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			else
				$cnt = $dbRes->SelectedRowsCount();

			$dbRes = new CDBResult();

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if($boolNavStartParams && 0 < $intTopCount)
				$strSql .= " LIMIT ".$intTopCount;

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
}
