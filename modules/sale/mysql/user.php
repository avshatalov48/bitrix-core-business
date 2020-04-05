<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/user.php");

class CSaleUserAccount extends CAllSaleUserAccount
{
	//********** SELECT **************//
	function GetByID($ID)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		if (isset($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$ID]) && is_array($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$ID]) && is_set($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$ID], "ID"))
		{
			return $GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$ID];
		}
		else
		{
			$strSql = 
				"SELECT UA.ID, UA.USER_ID, UA.CURRENT_BUDGET, UA.CURRENCY, UA.NOTES, UA.LOCKED, ".
				"	".$DB->DateToCharFunction("UA.TIMESTAMP_X", "FULL")." as TIMESTAMP_X, ".
				"	".$DB->DateToCharFunction("UA.DATE_LOCKED", "FULL")." as DATE_LOCKED ".
				"FROM b_sale_user_account UA ".
				"WHERE UA.ID = ".$ID." ";

			$dbUserAccount = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arUserAccount = $dbUserAccount->Fetch())
			{
				$GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$ID] = $arUserAccount;
				return $arUserAccount;
			}
		}

		return false;
	}

	function GetByUserID($userID, $currency)
	{
		global $DB;

		$userID = (int)$userID;
		if ($userID <= 0)
			return false;

		$currency = trim($currency);
		$currency = preg_replace("#[\W]+#", "", $currency);
		if ($currency == '')
			return false;

		if (isset($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$userID."_".$currency]) && is_array($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$userID."_".$currency]) && is_set($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$userID."_".$currency], "ID"))
		{
			return $GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$userID."_".$currency];
		}
		else
		{
			$strSql = 
				"SELECT UA.ID, UA.USER_ID, UA.CURRENT_BUDGET, UA.CURRENCY, UA.NOTES, UA.LOCKED, ".
				"	".$DB->DateToCharFunction("UA.TIMESTAMP_X", "FULL")." as TIMESTAMP_X, ".
				"	".$DB->DateToCharFunction("UA.DATE_LOCKED", "FULL")." as DATE_LOCKED ".
				"FROM b_sale_user_account UA ".
				"WHERE UA.USER_ID = ".$userID." ".
				"	AND UA.CURRENCY = '".$DB->ForSql($currency)."' ";

			$dbUserAccount = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arUserAccount = $dbUserAccount->Fetch())
			{
				$GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$userID."_".$currency] = $arUserAccount;
				return $arUserAccount;
			}
		}

		return false;
	}

	function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (empty($arSelectFields))
			$arSelectFields = array("ID", "USER_ID", "CURRENT_BUDGET", "CURRENCY", "LOCKED", "NOTES", "TIMESTAMP_X", "DATE_LOCKED");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "UA.ID", "TYPE" => "int"),
				"USER_ID" => array("FIELD" => "UA.USER_ID", "TYPE" => "int"),
				"CURRENT_BUDGET" => array("FIELD" => "UA.CURRENT_BUDGET", "TYPE" => "double"),
				"CURRENCY" => array("FIELD" => "UA.CURRENCY", "TYPE" => "string"),
				"LOCKED" => array("FIELD" => "UA.LOCKED", "TYPE" => "char"),
				"NOTES" => array("FIELD" => "UA.NOTES", "TYPE" => "string"),
				"TIMESTAMP_X" => array("FIELD" => "UA.TIMESTAMP_X", "TYPE" => "datetime"),
				"DATE_LOCKED" => array("FIELD" => "UA.DATE_LOCKED", "TYPE" => "datetime"),
				"USER_LOGIN" => array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UA.USER_ID = U.ID)"),
				"USER_ACTIVE" => array("FIELD" => "U.ACTIVE", "TYPE" => "char", "FROM" => "INNER JOIN b_user U ON (UA.USER_ID = U.ID)"),
				"USER_NAME" => array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UA.USER_ID = U.ID)"),
				"USER_LAST_NAME" => array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UA.USER_ID = U.ID)"),
				"USER_EMAIL" => array("FIELD" => "U.EMAIL", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UA.USER_ID = U.ID)"),
				"USER_USER" => array("FIELD" => "U.LOGIN,U.NAME,U.LAST_NAME,U.EMAIL,U.ID", "WHERE_ONLY" => "Y", "TYPE" => "string", "FROM" => "INNER JOIN b_user U ON (UA.USER_ID = U.ID)")
			);
		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_user_account UA ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql = 
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sale_user_account UA ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sale_user_account UA ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if (strlen($arSqls["GROUPBY"]) <= 0)
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				// FOR MYSQL!!! ANOTHER CODE FOR ORACLE
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])>0)
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

	function Add($arFields)
	{
		global $DB;

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1)=="=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleUserAccount::CheckFields("ADD", $arFields, 0))
			return false;

		$dbEvents = GetModuleEvents("sale", "OnBeforeUserAccountAdd");
		while ($arEvent = $dbEvents->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
			{
				return false;
			}
		}

		$arInsert = $DB->PrepareInsert("b_sale_user_account", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($arInsert[0])>0) $arInsert[0] .= ", ";
			$arInsert[0] .= $key;
			if (strlen($arInsert[1])>0) $arInsert[1] .= ", ";
			$arInsert[1] .= $value;
		}

		$strSql = "INSERT INTO b_sale_user_account(".$arInsert[0].") VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = (int)$DB->LastID();
		$_SESSION["SALE_BASKET_NUM_PRODUCTS"][SITE_ID] = 0;

		$dbEvents = GetModuleEvents("sale", "OnAfterUserAccountAdd");
		while ($arEvent = $dbEvents->Fetch())
		{
			ExecuteModuleEventEx($arEvent, Array($ID, $arFields));
		}

		return $ID;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1)=="=")
			{
				$arFields1[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleUserAccount::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$dbEvents = GetModuleEvents("sale", "OnBeforeUserAccountUpdate");
		while ($arEvent = $dbEvents->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
			{
				return false;
			}
		}

		$arOldUserAccount = CSaleUserAccount::GetByID($ID);

		$strUpdate = $DB->PrepareUpdate("b_sale_user_account", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if (strlen($strUpdate)>0) $strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		$strSql = "UPDATE b_sale_user_account SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		unset($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$ID]);
		unset($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$arOldUserAccount["USER_ID"]."_".$arOldUserAccount["CURRENCY"]]);

		$dbEvents = GetModuleEvents("sale", "OnAfterUserAccountUpdate");
		while ($arEvent = $dbEvents->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
		}

		return $ID;
	}
}