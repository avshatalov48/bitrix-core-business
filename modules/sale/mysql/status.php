<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/status.php");

class CSaleStatus extends CAllSaleStatus
{
	/**
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param bool|array  $arGroupBy
	 * @param bool|array  $arNavStartParams
	 * @param array $arSelectFields
	 * @return bool|int|CDBResult
	 */
	function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (!is_array($arOrder) && !is_array($arFilter))
		{
			$arOrder = strval($arOrder);
			$arFilter = strval($arFilter);
			if ('' != $arOrder && '' != $arFilter)
				$arOrder = array($arOrder => $arFilter);
			else
				$arOrder = array();

			$arFilter = array();
			$arFilter["LID"] = LANGUAGE_ID;
			if ($arGroupBy)
			{
				$arGroupBy = strval($arGroupBy);
				if ('' != $arGroupBy)
					$arFilter["LID"] = $arGroupBy;
			}
			$arGroupBy = false;

			$arSelectFields = array("ID", "SORT", "LID", "NAME", "DESCRIPTION");
		}

		$arFields = array(
			"ID" => array("FIELD" => "S.ID", "TYPE" => "char"),
			"SORT" => array("FIELD" => "S.SORT", "TYPE" => "int"),
			"GROUP_ID" => array("FIELD" => "SSG.GROUP_ID", "TYPE" => "int", "FROM" => "LEFT JOIN b_sale_status2group SSG ON (S.ID = SSG.STATUS_ID)"),
			"PERM_VIEW" => array("FIELD" => "SSG.PERM_VIEW", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_status2group SSG ON (S.ID = SSG.STATUS_ID)"),
			"PERM_CANCEL" => array("FIELD" => "SSG.PERM_CANCEL", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_status2group SSG ON (S.ID = SSG.STATUS_ID)"),
			"PERM_DELIVERY" => array("FIELD" => "SSG.PERM_DELIVERY", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_status2group SSG ON (S.ID = SSG.STATUS_ID)"),
			"PERM_MARK" => array("FIELD" => "SSG.PERM_MARK", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_status2group SSG ON (S.ID = SSG.STATUS_ID)"),
			"PERM_DEDUCTION" => array("FIELD" => "SSG.PERM_DEDUCTION", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_status2group SSG ON (S.ID = SSG.STATUS_ID)"),
			"PERM_PAYMENT" => array("FIELD" => "SSG.PERM_PAYMENT", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_status2group SSG ON (S.ID = SSG.STATUS_ID)"),
			"PERM_STATUS" => array("FIELD" => "SSG.PERM_STATUS", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_status2group SSG ON (S.ID = SSG.STATUS_ID)"),
			"PERM_STATUS_FROM" => array("FIELD" => "SSG.PERM_STATUS_FROM", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_status2group SSG ON (S.ID = SSG.STATUS_ID)"),
			"PERM_UPDATE" => array("FIELD" => "SSG.PERM_UPDATE", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_status2group SSG ON (S.ID = SSG.STATUS_ID)"),
			"PERM_DELETE" => array("FIELD" => "SSG.PERM_DELETE", "TYPE" => "char", "FROM" => "LEFT JOIN b_sale_status2group SSG ON (S.ID = SSG.STATUS_ID)"),
			"LID" => array("FIELD" => "SL.LID", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_status_lang SL ON (S.ID = SL.STATUS_ID)"),
			"NAME" => array("FIELD" => "SL.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_status_lang SL ON (S.ID = SL.STATUS_ID)"),
			"DESCRIPTION" => array("FIELD" => "SL.DESCRIPTION", "TYPE" => "string", "FROM" => "LEFT JOIN b_sale_status_lang SL ON (S.ID = SL.STATUS_ID)")
		);

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_sale_status S ".$arSqls["FROM"];
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

		$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_sale_status S ".$arSqls["FROM"];
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
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM b_sale_status S ".$arSqls["FROM"];
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

	function GetByID($ID, $strLang = LANGUAGE_ID)
	{
		global $DB;

		$ID = $DB->ForSql($ID, 1);
		$strLang = $DB->ForSql($strLang, 2);
		if (isset($GLOBALS["SALE_STATUS"]["SALE_STATUS_CACHE_".$ID."_".$strLang]) && is_array($GLOBALS["SALE_STATUS"]["SALE_STATUS_CACHE_".$ID."_".$strLang]) && is_set($GLOBALS["SALE_STATUS"]["SALE_ORDER_CACHE_".$ID."_".$strLang], "ID"))
		{
			return $GLOBALS["SALE_STATUS"]["SALE_STATUS_CACHE_".$ID."_".$strLang];
		}
		else
		{

			$strSql = "SELECT S.ID, S.SORT, SL.LID, SL.NAME, SL.DESCRIPTION FROM b_sale_status S ".
				"	LEFT JOIN b_sale_status_lang SL ON (S.ID = SL.STATUS_ID AND SL.LID = '".$strLang."') WHERE ID = '".$ID."'";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($res = $db_res->Fetch())
			{
				$GLOBALS["SALE_STATUS"]["SALE_STATUS_CACHE_".$ID."_".$strLang] = $res;
				return $res;
			}
		}
		return false;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = $DB->ForSql($ID, 1);
		if (!CSaleStatus::CheckFields("UPDATE", $arFields, $ID))
			return false;

		foreach (GetModuleEvents("sale", "OnBeforeStatusUpdate", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
				return false;
		}

		$strUpdate = $DB->PrepareUpdate("b_sale_status", $arFields);
		if (!empty($strUpdate))
		{
			$strSql = "UPDATE b_sale_status SET ".$strUpdate." WHERE ID = '".$ID."' ";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		if (isset($arFields['LANG']) && is_array($arFields['LANG']))
		{
			$DB->Query("DELETE FROM b_sale_status_lang WHERE STATUS_ID = '".$ID."'");

			foreach ($arFields['LANG'] as $statusLang)
			{
				$langUpdateFields = $langInsertFields = $statusLang;
				$langInsertFields['STATUS_ID'] = $ID;
				$arInsert = $DB->PrepareInsert("b_sale_status_lang", $langInsertFields);
				if (isset($langUpdateFields['STATUS_ID']))
					unset($langUpdateFields['STATUS_ID']);
				if (isset($langUpdateFields['LID']))
					unset($langUpdateFields['LID']);
				$langUpdate = "";
				if (count($langUpdateFields) > 0)
					$langUpdate = " ON DUPLICATE KEY UPDATE ".$DB->PrepareUpdate("b_sale_status_lang", $langUpdateFields);
				$strSql =
					"INSERT INTO b_sale_status_lang(".$arInsert[0].") VALUES(".$arInsert[1].")".$langUpdate;
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
			if (isset($statusLang))
				unset($statusLang);
		}

		if (isset($arFields['PERMS']) && is_array($arFields["PERMS"]))
		{
			$DB->Query("DELETE FROM b_sale_status2group WHERE STATUS_ID = '".$ID."'");

			foreach ($arFields["PERMS"] as &$arOnePerm)
			{
				$arInsert = $DB->PrepareInsert("b_sale_status2group", $arOnePerm);
				$strSql = "INSERT INTO b_sale_status2group(STATUS_ID, ".$arInsert[0].") VALUES('".$ID."', ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
			if (isset($arOnePerm))
				unset($arOnePerm);
		}

		foreach (GetModuleEvents("sale", "OnStatusUpdate", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
		}

		return $ID;
	}

	function GetPermissionsList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		$arFields = array(
			"ID" => array("FIELD" => "S.ID", "TYPE" => "int"),
			"GROUP_ID" => array("FIELD" => "S.GROUP_ID", "TYPE" => "int"),
			"STATUS_ID" => array("FIELD" => "S.STATUS_ID", "TYPE" => "char"),
			"PERM_VIEW" => array("FIELD" => "S.PERM_VIEW", "TYPE" => "char"),
			"PERM_CANCEL" => array("FIELD" => "S.PERM_CANCEL", "TYPE" => "char"),
			"PERM_MARK" => array("FIELD" => "S.PERM_MARK", "TYPE" => "char"),
			"PERM_DELIVERY" => array("FIELD" => "S.PERM_DELIVERY", "TYPE" => "char"),
			"PERM_DEDUCTION" => array("FIELD" => "S.PERM_DEDUCTION", "TYPE" => "char"),
			"PERM_PAYMENT" => array("FIELD" => "S.PERM_PAYMENT", "TYPE" => "char"),
			"PERM_STATUS" => array("FIELD" => "S.PERM_STATUS", "TYPE" => "char"),
			"PERM_STATUS_FROM" => array("FIELD" => "S.PERM_STATUS_FROM", "TYPE" => "char"),
			"PERM_UPDATE" => array("FIELD" => "S.PERM_UPDATE", "TYPE" => "char"),
			"PERM_DELETE" => array("FIELD" => "S.PERM_DELETE", "TYPE" => "char"),
		);

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_sale_status2group S ".$arSqls["FROM"];
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

		$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_sale_status2group S ".$arSqls["FROM"];
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
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM b_sale_status2group S ".$arSqls["FROM"];
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