<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/store_docs_element.php");

class CCatalogStoreDocsElement
	extends CCatalogStoreDocsElementAll
{
	public static function add($arFields)
	{
		global $DB;

		foreach(GetModuleEvents("catalog", "OnBeforeCatalogStoreDocsElementAdd", true) as $arEvent)
			if(ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				return false;

		if (!self::CheckFields('ADD',$arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_catalog_docs_element", $arFields);
		$strSql = "INSERT INTO b_catalog_docs_element (".$arInsert[0].") VALUES(".$arInsert[1].")";

		$res = $DB->Query($strSql, true, "File: ".__FILE__."<br>Line: ".__LINE__);
		if(!$res)
			return false;
		$lastId = intval($DB->LastID());

		foreach(GetModuleEvents("catalog", "OnCatalogStoreDocsElementAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($lastId, $arFields));

		return $lastId;
	}

	/**
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param bool|array $arGroupBy
	 * @param bool|array $arNavStartParams
	 * @param array $arSelectFields
	 * @return bool|CDBResult
	 */
	public static function getList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (empty($arSelectFields))
			$arSelectFields = array("ID", "DOC_ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE",
				"BASE_PRICE", "BASE_PRICE_EXTRA", "BASE_PRICE_EXTRA_RATE"
			);

		$arFields = array(
			"ID" => array("FIELD" => "DE.ID", "TYPE" => "int"),
			"DOC_ID" => array("FIELD" => "DE.DOC_ID", "TYPE" => "int"),
			"STORE_FROM" => array("FIELD" => "DE.STORE_FROM", "TYPE" => "int"),
			"STORE_TO" => array("FIELD" => "DE.STORE_TO", "TYPE" => "int"),
			"ELEMENT_ID" => array("FIELD" => "DE.ELEMENT_ID", "TYPE" => "int"),
			"AMOUNT" => array("FIELD" => "DE.AMOUNT", "TYPE" => "double"),
			"PURCHASING_PRICE" => array("FIELD" => "DE.PURCHASING_PRICE", "TYPE" => "double"),
			"BASE_PRICE" => array("FIELD" => "DE.BASE_PRICE", "TYPE" => "double"),
			"BASE_PRICE_EXTRA" => array("FIELD" => "DE.BASE_PRICE_EXTRA", "TYPE" => "double"),
			"BASE_PRICE_EXTRA_RATE" => array("FIELD" => "DE.BASE_PRICE_EXTRA_RATE", "TYPE" => "int"),

			"IS_MULTIPLY_BARCODE" => array("FIELD" => "CP.BARCODE_MULTI", "TYPE" => "char", "FROM" => "INNER JOIN b_catalog_product CP ON (DE.ELEMENT_ID = CP.ID)"),
			"RESERVED" => array("FIELD" => "CP.QUANTITY_RESERVED", "TYPE" => "double", "FROM" => "INNER JOIN b_catalog_product CP ON (DE.ELEMENT_ID = CP.ID)"),

			"ELEMENT_IBLOCK_ID" => array("FIELD" => "IE.IBLOCK_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_iblock_element IE ON (DE.ELEMENT_ID = IE.ID)"),
			"ELEMENT_NAME" => array("FIELD" => "IE.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_iblock_element IE ON (DE.ELEMENT_ID = IE.ID)")
		);
		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);
		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_catalog_docs_element DE ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}
		$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_catalog_docs_element DE ".$arSqls["FROM"];
		if (!empty($arSqls["WHERE"]))
			$strSql .= " WHERE ".$arSqls["WHERE"];
		if (!empty($arSqls["GROUPBY"]))
			$strSql .= " GROUP BY ".$arSqls["GROUPBY"];
		if (!empty($arSqls["ORDERBY"]))
			$strSql .= " ORDER BY ".$arSqls["ORDERBY"];

		$intTopCount = 0;
		$boolNavStartParams = (!empty($arNavStartParams) && is_array($arNavStartParams));
		if ($boolNavStartParams && array_key_exists('nTopCount', $arNavStartParams))
		{
			$intTopCount = intval($arNavStartParams["nTopCount"]);
		}
		if ($boolNavStartParams && 0 >= $intTopCount)
		{
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM b_catalog_docs_element DE ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql_tmp .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql_tmp .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if (empty($arSqls["GROUPBY"]))
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if ($boolNavStartParams && 0 < $intTopCount)
			{
				$strSql .= " LIMIT ".$intTopCount;
			}
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return $dbRes;
	}
}