<?php

/** @global CMain $APPLICATION */
use Bitrix\Main,
	Bitrix\Main\Config\Option,
	Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/product.php");

class CCatalogProduct extends CAllCatalogProduct
{
	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * @see Catalog\Model\Product::getList or Catalog\ProductTable::getList
	 *
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param bool|array $arGroupBy
	 * @param bool|array $arNavStartParams
	 * @param array $arSelectFields
	 * @return bool|CDBResult
	 */
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		$entityResult = new CCatalogResult('\Bitrix\Catalog\Model\Product');

		if (!is_array($arOrder) && !is_array($arFilter))
		{
			$arOrder = (string)$arOrder;
			$arFilter = (string)$arFilter;
			$arOrder = ($arOrder != '' && $arFilter != '' ? array($arOrder => $arFilter) : array());
			$arFilter = (is_array($arGroupBy) ? $arGroupBy : array());
			$arGroupBy = false;
		}

		$defaultQuantityTrace = Option::get('catalog', 'default_quantity_trace');
		$defaultCanBuyZero = Option::get('catalog', 'default_can_buy_zero');
		$defaultNegativeAmount = Option::get('catalog', 'allow_negative_amount');
		$defaultSubscribe = Option::get('catalog', 'default_subscribe');

		$arFields = array(
			"ID" => array("FIELD" => "CP.ID", "TYPE" => "int"),
			"QUANTITY" => array("FIELD" => "CP.QUANTITY", "TYPE" => "double"),
			"QUANTITY_RESERVED" => array("FIELD" => "CP.QUANTITY_RESERVED", "TYPE" => "double"),
			"QUANTITY_TRACE_ORIG" => array("FIELD" => "CP.QUANTITY_TRACE", "TYPE" => "char"),
			"CAN_BUY_ZERO_ORIG" => array("FIELD" => "CP.CAN_BUY_ZERO", "TYPE" => "char"),
			"NEGATIVE_AMOUNT_TRACE_ORIG" => array("FIELD" => "CP.NEGATIVE_AMOUNT_TRACE", "TYPE" => "char"),
			"QUANTITY_TRACE" => array(
				"FIELD" => "CASE WHEN CP.QUANTITY_TRACE = 'D' THEN '".$DB->ForSql($defaultQuantityTrace)."' ELSE CP.QUANTITY_TRACE END",
				"TYPE" => "char",
			),
			"CAN_BUY_ZERO" => array(
				"FIELD" => "CASE WHEN CP.CAN_BUY_ZERO = 'D' THEN '".$DB->ForSql($defaultCanBuyZero)."' ELSE CP.CAN_BUY_ZERO END",
				"TYPE" => "char",
			),
			"NEGATIVE_AMOUNT_TRACE" => array(
				"FIELD" => "CASE WHEN CP.NEGATIVE_AMOUNT_TRACE = 'D' THEN '".$DB->ForSql($defaultNegativeAmount)."' ELSE CP.NEGATIVE_AMOUNT_TRACE END",
				"TYPE" => "char",
			),
			"SUBSCRIBE_ORIG" => array("FIELD" => "CP.SUBSCRIBE", "TYPE" => "char"),
			"SUBSCRIBE" => array(
				"FIELD" => "CASE WHEN CP.SUBSCRIBE = 'D' THEN '".$DB->ForSql($defaultSubscribe)."' ELSE CP.SUBSCRIBE END",
				"TYPE" => "char",
			),
			"AVAILABLE" => array("FIELD" => "CP.AVAILABLE", "TYPE" => "char"),
			"BUNDLE" => array("FIELD" => "CP.BUNDLE", "TYPE" => "char"),
			"WEIGHT" => array("FIELD" => "CP.WEIGHT", "TYPE" => "double"),
			"WIDTH" => array("FIELD" => "CP.WIDTH", "TYPE" => "double"),
			"LENGTH" => array("FIELD" => "CP.LENGTH", "TYPE" => "double"),
			"HEIGHT" => array("FIELD" => "CP.HEIGHT", "TYPE" => "double"),
			"TIMESTAMP_X" => array("FIELD" => "CP.TIMESTAMP_X", "TYPE" => "datetime"),
			"PRICE_TYPE" => array("FIELD" => "CP.PRICE_TYPE", "TYPE" => "char"),
			"RECUR_SCHEME_TYPE" => array("FIELD" => "CP.RECUR_SCHEME_TYPE", "TYPE" => "char"),
			"RECUR_SCHEME_LENGTH" => array("FIELD" => "CP.RECUR_SCHEME_LENGTH", "TYPE" => "int"),
			"TRIAL_PRICE_ID" => array("FIELD" => "CP.TRIAL_PRICE_ID", "TYPE" => "int"),
			"WITHOUT_ORDER" => array("FIELD" => "CP.WITHOUT_ORDER", "TYPE" => "char"),
			"SELECT_BEST_PRICE" => array("FIELD" => "CP.SELECT_BEST_PRICE", "TYPE" => "char"),
			"VAT_ID" => array("FIELD" => "CP.VAT_ID", "TYPE" => "int"),
			"VAT_INCLUDED" => array("FIELD" => "CP.VAT_INCLUDED", "TYPE" => "char"),
			"TMP_ID" => array("FIELD" => "CP.TMP_ID", "TYPE" => "char"),
			"PURCHASING_PRICE" => array("FIELD" => "CP.PURCHASING_PRICE", "TYPE" => "double"),
			"PURCHASING_CURRENCY" => array("FIELD" => "CP.PURCHASING_CURRENCY", "TYPE" => "string"),
			"BARCODE_MULTI" => array("FIELD" => "CP.BARCODE_MULTI", "TYPE" => "char"),
			"MEASURE" => array("FIELD" => "CP.MEASURE", "TYPE" => "int"),
			"TYPE" => array("FIELD" => "CP.TYPE", "TYPE" => "int"),
			"ELEMENT_IBLOCK_ID" => array("FIELD" => "I.IBLOCK_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_iblock_element I ON (CP.ID = I.ID)"),
			"ELEMENT_XML_ID" => array("FIELD" => "I.XML_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_iblock_element I ON (CP.ID = I.ID)"),
			"ELEMENT_NAME" => array("FIELD" => "I.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_iblock_element I ON (CP.ID = I.ID)")
		);

		$arSelectFields = $entityResult->prepareSelect($arSelectFields);

		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_catalog_product CP ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_catalog_product CP ".$arSqls["FROM"];
		if (!empty($arSqls["WHERE"]))
			$strSql .= " WHERE ".$arSqls["WHERE"];
		if (!empty($arSqls["GROUPBY"]))
			$strSql .= " GROUP BY ".$arSqls["GROUPBY"];
		if (!empty($arSqls["ORDERBY"]))
			$strSql .= " ORDER BY ".$arSqls["ORDERBY"];

		$intTopCount = 0;
		$boolNavStartParams = (!empty($arNavStartParams) && is_array($arNavStartParams));
		if ($boolNavStartParams && isset($arNavStartParams['nTopCount']))
			$intTopCount = (int)$arNavStartParams['nTopCount'];

		if ($boolNavStartParams && $intTopCount <= 0)
		{
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM b_catalog_product CP ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql_tmp .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql_tmp .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql_tmp);
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
			if ($boolNavStartParams && $intTopCount > 0)
				$strSql .= " LIMIT ".$intTopCount;

			$entityResult->setResult($DB->Query($strSql));

			$dbRes = $entityResult;
		}

		return $dbRes;
	}

	/**
	* @deprecated deprecated since catalog 8.5.1
	* @see CCatalogProduct::GetList()
	 *
	 * @param array $arOrder
	 * @param array $arFilter
	 *
	 * @return false
	 *
	*/
	public static function GetListEx($arOrder=array("SORT"=>"ASC"), $arFilter=array())
	{
		return false;
	}

	/**
	 * @deprecated deprecated since catalog 17.6.3
	 * @see CCatalogProduct::GetVATDataByID
	 *
	 * @param int $PRODUCT_ID
	 * @return false|CDBResult
	 */
	public static function GetVATInfo($PRODUCT_ID)
	{
		$vat = self::GetVATDataByID($PRODUCT_ID);
		if (empty($vat))
			$vat = [];
		else
			$vat = [0 => $vat];
		$result = new CDBResult();
		$result->InitFromArray($vat);
		unset($vat);

		return $result;
	}

	/**
	 * @param array $list
	 *
	 * @return array
	 */
	public static function GetVATDataByIDList(array $list): array
	{
		$output = [];
		if (empty($list))
			return $output;
		Main\Type\Collection::normalizeArrayValuesByInt($list, true);
		if (empty($list))
			return $output;
		return self::loadVatInfoFromDB($list);
	}

	/**
	 * @param $id
	 *
	 * @return false|array
	 */
	public static function GetVATDataByID($id)
	{
		$id = (int)$id;
		if ($id <= 0)
			return false;
		$result = self::loadVatInfoFromDB([$id]);
		return ($result[$id] ?? false);
	}

	/**
	 * @param array $list
	 *
	 * @return array
	 */
	private static function loadVatInfoFromDB(array $list): array
	{
		$result = array_fill_keys($list, false);
		$ids = [];
		foreach ($list as $id)
		{
			if (isset(static::$vatCache[$id]))
			{
				$result[$id] = static::$vatCache[$id];
			}
			else
			{
				$ids[] = $id;
				static::$vatCache[$id] = false;
			}
		}
		if (!empty($ids))
		{
			$conn = Main\Application::getConnection();
			$iterator = $conn->query(
				"
	select CAT_PR.ID as PRODUCT_ID, CAT_VAT.*, CAT_PR.VAT_INCLUDED
	from b_catalog_product CAT_PR
	left join b_iblock_element BE on (BE.ID = CAT_PR.ID)
	left join b_catalog_iblock CAT_IB on ((CAT_PR.VAT_ID is null or CAT_PR.VAT_ID = 0) and CAT_IB.IBLOCK_ID = BE.IBLOCK_ID)
	left join b_catalog_vat CAT_VAT on (CAT_VAT.ID = CASE WHEN (CAT_PR.VAT_ID is null or CAT_PR.VAT_ID = 0) THEN CAT_IB.VAT_ID ELSE CAT_PR.VAT_ID END)
	where CAT_PR.ID in (".implode(', ', $ids).")
	and CAT_VAT.ACTIVE='Y'
	"
			);
			while ($row = $iterator->fetch())
			{
				$productId = (int)$row['PRODUCT_ID'];
				if (isset($row['TIMESTAMP_X']) && $row['TIMESTAMP_X'] instanceof Main\Type\DateTime)
				{
					$row['TIMESTAMP_X'] = $row['TIMESTAMP_X']->toString();
				}
				if ($row['RATE'] !== null)
				{
					$row['RATE'] = (float)$row['RATE'];
				}
				static::$vatCache[$productId] = $row;
				$result[$productId] = $row;
			}
			unset($productId, $row, $iterator);
			unset($conn);
		}
		unset($ids);

		return $result;
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * @see Catalog\Model\Product::update
	 * @param int $intID
	 * @param int $intTypeID
	 * @return bool
	 */
	public static function SetProductType($intID, $intTypeID)
	{
		$intID = (int)$intID;
		if ($intID <= 0)
			return false;
		$intTypeID = (int)$intTypeID;
		if ($intTypeID != Catalog\ProductTable::TYPE_PRODUCT && $intTypeID != Catalog\ProductTable::TYPE_SET)
			return false;

		$result = Catalog\Model\Product::update($intID, ['TYPE' => $intTypeID]);
		return $result->isSuccess();
	}
}
