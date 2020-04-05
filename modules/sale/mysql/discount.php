<?
use Bitrix\Sale\Discount\Index;
use Bitrix\Sale\Internals;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/general/discount.php');

class CSaleDiscount extends CAllSaleDiscount
{
	public function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (!is_array($arOrder) && !is_array($arFilter))
		{
			$arOrder = (string)($arOrder);
			$arFilter = (string)($arFilter);
			if ($arOrder !== '' && $arFilter !== '')
				$arOrder = array($arOrder => $arFilter);
			else
				$arOrder = array();
			if (is_array($arGroupBy))
				$arFilter = $arGroupBy;
			else
				$arFilter = array();
			if (isset($arFilter["PRICE"]))
			{
				$valTmp = $arFilter["PRICE"];
				unset($arFilter["PRICE"]);
				$arFilter["<=PRICE_FROM"] = $valTmp;
				$arFilter[">=PRICE_TO"] = $valTmp;
			}
			$arGroupBy = false;
		}

		$arFields = array(
			"ID" => array("FIELD" => "D.ID", "TYPE" => "int"),
			"XML_ID" => array("FIELD" => "D.XML_ID", "TYPE" => "string"),
			"LID" => array("FIELD" => "D.LID", "TYPE" => "string"),
			"SITE_ID" => array("FIELD" => "D.LID", "TYPE" => "string"),
			"NAME" => array("FIELD" => "D.NAME", "TYPE" => "string"),
			"PRICE_FROM" => array("FIELD" => "D.PRICE_FROM", "TYPE" => "double", "WHERE" => array("CSaleDiscount", "PrepareCurrency4Where")),
			"PRICE_TO" => array("FIELD" => "D.PRICE_TO", "TYPE" => "double", "WHERE" => array("CSaleDiscount", "PrepareCurrency4Where")),
			"CURRENCY" => array("FIELD" => "D.CURRENCY", "TYPE" => "string"),
			"DISCOUNT_VALUE" => array("FIELD" => "D.DISCOUNT_VALUE", "TYPE" => "double"),
			"DISCOUNT_TYPE" => array("FIELD" => "D.DISCOUNT_TYPE", "TYPE" => "char"),
			"ACTIVE" => array("FIELD" => "D.ACTIVE", "TYPE" => "char"),
			"SORT" => array("FIELD" => "D.SORT", "TYPE" => "int"),
			"ACTIVE_FROM" => array("FIELD" => "D.ACTIVE_FROM", "TYPE" => "datetime"),
			"ACTIVE_TO" => array("FIELD" => "D.ACTIVE_TO", "TYPE" => "datetime"),
			"TIMESTAMP_X" => array("FIELD" => "D.TIMESTAMP_X", "TYPE" => "datetime"),
			"MODIFIED_BY" => array("FIELD" => "D.MODIFIED_BY", "TYPE" => "int"),
			"DATE_CREATE" => array("FIELD" => "D.DATE_CREATE", "TYPE" => "datetime"),
			"CREATED_BY" => array("FIELD" => "D.CREATED_BY", "TYPE" => "int"),
			"PRIORITY" => array("FIELD" => "D.PRIORITY", "TYPE" => "int"),
			"LAST_DISCOUNT" => array("FIELD" => "D.LAST_DISCOUNT", "TYPE" => "char"),
			"LAST_LEVEL_DISCOUNT" => array("FIELD" => "D.LAST_LEVEL_DISCOUNT", "TYPE" => "char"),
			"VERSION" => array("FIELD" => "D.VERSION", "TYPE" => "int"),
			"CONDITIONS" => array("FIELD" => "D.CONDITIONS", "TYPE" => "string"),
			"UNPACK" => array("FIELD" => "D.UNPACK", "TYPE" => "string"),
			"APPLICATION" => array("FIELD" => "D.APPLICATION", "TYPE" => "string"),
			"ACTIONS" => array("FIELD" => "D.ACTIONS", "TYPE" => "string"),
			"PRESET_ID" => array("FIELD" => "D.PRESET_ID", "TYPE" => "string"),
			"USE_COUPONS" => array("FIELD" => "D.USE_COUPONS", "TYPE" => "char"),
			"USER_GROUPS" => array("FIELD" => "DG.GROUP_ID", "TYPE" => "int","FROM" => "LEFT JOIN b_sale_discount_group DG ON (D.ID = DG.DISCOUNT_ID)")
		);

		if (empty($arSelectFields))
			$arSelectFields = array('ID','LID','SITE_ID','PRICE_FROM','PRICE_TO','CURRENCY','DISCOUNT_VALUE','DISCOUNT_TYPE','ACTIVE','SORT','ACTIVE_FROM','ACTIVE_TO','PRIORITY','LAST_DISCOUNT', 'LAST_LEVEL_DISCOUNT','VERSION','NAME');
		elseif (is_array($arSelectFields) && in_array('*',$arSelectFields))
			$arSelectFields = array('ID','LID','SITE_ID','PRICE_FROM','PRICE_TO','CURRENCY','DISCOUNT_VALUE','DISCOUNT_TYPE','ACTIVE','SORT','ACTIVE_FROM','ACTIVE_TO','PRIORITY','LAST_DISCOUNT', 'LAST_LEVEL_DISCOUNT','VERSION','NAME');

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", '', $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "select ".$arSqls["SELECT"]." from b_sale_discount D ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql .= " where ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " group by ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql = "select ".$arSqls["SELECT"]." from b_sale_discount D ".$arSqls["FROM"];
		if (!empty($arSqls["WHERE"]))
			$strSql .= " where ".$arSqls["WHERE"];
		if (!empty($arSqls["GROUPBY"]))
			$strSql .= " group by ".$arSqls["GROUPBY"];
		if (!empty($arSqls["ORDERBY"]))
			$strSql .= " order by ".$arSqls["ORDERBY"];

		$intTopCount = 0;
		$boolNavStartParams = (!empty($arNavStartParams) && is_array($arNavStartParams));
		if ($boolNavStartParams && isset($arNavStartParams['nTopCount']))
		{
			$intTopCount = (int)$arNavStartParams["nTopCount"];
		}
		if ($boolNavStartParams && $intTopCount <= 0)
		{
			$strSql_tmp = "select COUNT('x') as CNT from b_sale_discount D ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql_tmp .= " where ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql_tmp .= " group by ".$arSqls["GROUPBY"];

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
			if ($boolNavStartParams && $intTopCount > 0)
			{
				$strSql .= " limit ".$intTopCount;
			}
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	public function GetDiscountGroupList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		$arFields = array(
			"ID" => array("FIELD" => "DG.ID", "TYPE" => "int"),
			"DISCOUNT_ID" => array("FIELD" => "DG.DISCOUNT_ID", "TYPE" => "int"),
			"GROUP_ID" => array("FIELD" => "DG.GROUP_ID", "TYPE" => "int"),
		);

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "select ".$arSqls["SELECT"]." from b_sale_discount_group DG ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql .= " where ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " group by ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql = "select ".$arSqls["SELECT"]." from b_sale_discount_group DG ".$arSqls["FROM"];
		if (!empty($arSqls["WHERE"]))
			$strSql .= " where ".$arSqls["WHERE"];
		if (!empty($arSqls["GROUPBY"]))
			$strSql .= " group by ".$arSqls["GROUPBY"];
		if (!empty($arSqls["ORDERBY"]))
			$strSql .= " order by ".$arSqls["ORDERBY"];

		$intTopCount = 0;
		$boolNavStartParams = (!empty($arNavStartParams) && is_array($arNavStartParams));
		if ($boolNavStartParams && array_key_exists('nTopCount', $arNavStartParams))
		{
			$intTopCount = intval($arNavStartParams["nTopCount"]);
		}
		if ($boolNavStartParams && 0 >= $intTopCount)
		{
			$strSql_tmp = "select COUNT('x') as CNT from b_sale_discount_group DG ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql_tmp .= " where ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql_tmp .= " group by ".$arSqls["GROUPBY"];

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
				$strSql .= " limit ".$intTopCount;
			}
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
}