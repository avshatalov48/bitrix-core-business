<?
class CAllCatalogImport
{
	public static function CheckFields($ACTION, &$arFields)
	{
		global $DB;
		global $USER;

		$ACTION = strtoupper($ACTION);
		if ('UPDATE' != $ACTION && 'ADD' != $ACTION)
			return false;

		if ((is_set($arFields, "FILE_NAME") || $ACTION=="ADD") && strlen($arFields["FILE_NAME"])<=0)
			return false;
		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && strlen($arFields["NAME"])<=0)
			return false;

		if ((is_set($arFields, "IN_MENU") || $ACTION=="ADD") && $arFields["IN_MENU"]!="Y")
			$arFields["IN_MENU"]="N";
		if ((is_set($arFields, "DEFAULT_PROFILE") || $ACTION=="ADD") && $arFields["DEFAULT_PROFILE"]!="Y")
			$arFields["DEFAULT_PROFILE"]="N";
		if ((is_set($arFields, "IN_AGENT") || $ACTION=="ADD") && $arFields["IN_AGENT"]!="Y")
			$arFields["IN_AGENT"]="N";
		if ((is_set($arFields, "IN_CRON") || $ACTION=="ADD") && $arFields["IN_CRON"]!="Y")
			$arFields["IN_CRON"]="N";
		if ((is_set($arFields, "NEED_EDIT") || $ACTION=="ADD") && $arFields["NEED_EDIT"] != "Y")
			$arFields["NEED_EDIT"]="N";

		$arFields["IS_EXPORT"] = "N";

		$intUserID = 0;
		$boolUserExist = CCatalog::IsUserExists();
		if ($boolUserExist)
			$intUserID = intval($USER->GetID());
		$strDateFunction = $DB->GetNowFunction();
		$boolNoUpdate = false;
		if (isset($arFields['=LAST_USE']) && $strDateFunction == $arFields['=LAST_USE'])
		{
			$arFields['~LAST_USE'] = $strDateFunction;
			$boolNoUpdate = ('UPDATE' == $ACTION);
		}
		foreach ($arFields as $key => $value)
		{
			if (0 == strncmp($key, '=', 1))
				unset($arFields[$key]);
		}

		if (array_key_exists('TIMESTAMP_X', $arFields))
			unset($arFields['TIMESTAMP_X']);
		if (array_key_exists('DATE_CREATE', $arFields))
			unset($arFields['DATE_CREATE']);

		if ('ADD' == $ACTION)
		{
			$arFields['~TIMESTAMP_X'] = $strDateFunction;
			$arFields['~DATE_CREATE'] = $strDateFunction;
			if ($boolUserExist)
			{
				if (!array_key_exists('CREATED_BY', $arFields) || intval($arFields["CREATED_BY"]) <= 0)
					$arFields["CREATED_BY"] = $intUserID;
				if (!array_key_exists('MODIFIED_BY', $arFields) || intval($arFields["MODIFIED_BY"]) <= 0)
					$arFields["MODIFIED_BY"] = $intUserID;
			}
		}
		if ('UPDATE' == $ACTION)
		{
			if (array_key_exists('CREATED_BY', $arFields))
				unset($arFields['CREATED_BY']);
			if ($boolNoUpdate)
			{
				if (array_key_exists('MODIFIED_BY',$arFields))
					unset($arFields['MODIFIED_BY']);
			}
			else
			{
				if ($boolUserExist)
				{
					if (!array_key_exists('MODIFIED_BY', $arFields) || intval($arFields["MODIFIED_BY"]) <= 0)
						$arFields["MODIFIED_BY"] = $intUserID;
				}
				$arFields['~TIMESTAMP_X'] = $strDateFunction;
			}
		}

		return true;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;
		return $DB->Query("DELETE FROM b_catalog_export WHERE ID = ".$ID." AND IS_EXPORT = 'N'", true, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	public static function GetList($arOrder = array("ID" => "ASC"), $arFilter = array(), $bCount = false)
	{
		global $DB;
		$arSqlSearch = array();

		if (!is_array($arFilter))
			$filter_keys = array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i = 0, $intCount = count($filter_keys); $i < $intCount; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if (strlen($val)<=0)
				continue;

			$bInvert = false;
			$key = $filter_keys[$i];
			if (substr($key,0,1) == "!")
			{
				$key = substr($key, 1);
				$bInvert = true;
			}

			switch(strtoupper($key))
			{
			case "ID":
				$arSqlSearch[] = "CE.ID ".($bInvert?"<>":"=")." ".(int)$val."";
				break;
			case "FILE_NAME":
				$arSqlSearch[] = "CE.FILE_NAME ".($bInvert?"<>":"=")." '".$val."'";
				break;
			case "NAME":
				$arSqlSearch[] = "CE.NAME ".($bInvert?"<>":"=")." '".$val."'";
				break;
			case "DEFAULT_PROFILE":
				$arSqlSearch[] = "CE.DEFAULT_PROFILE ".($bInvert?"<>":"=")." '".$val."'";
				break;
			case "IN_MENU":
				$arSqlSearch[] = "CE.IN_MENU ".($bInvert?"<>":"=")." '".$val."'";
				break;
			case "IN_AGENT":
				$arSqlSearch[] = "CE.IN_AGENT ".($bInvert?"<>":"=")." '".$val."'";
				break;
			case "IN_CRON":
				$arSqlSearch[] = "CE.IN_CRON ".($bInvert?"<>":"=")." '".$val."'";
				break;
			case 'NEED_EDIT':
				$arSqlSearch[] = "CE.NEED_EDIT ".($bInvert?"<>":"=")." '".$val."'";
				break;
			case 'CREATED_BY':
				$arSqlSearch[] = "CE.CREATED_BY ".($bInvert?"<>":"=")." '".intval($val)."'";
				break;
			case 'MODIFIED_BY':
				$arSqlSearch[] = "CE.MODIFIED_BY ".($bInvert?"<>":"=")." '".intval($val)."'";
				break;
			}
		}

		$strSqlSearch = "";
		if (!empty($arSqlSearch))
		{
			$strSqlSearch = ' AND ('.implode(') AND (', $arSqlSearch).') ';
		}

		$strSqlSelect =
			"SELECT CE.ID, CE.FILE_NAME, CE.NAME, CE.IN_MENU, CE.IN_AGENT, ".
			"	CE.IN_CRON, CE.SETUP_VARS, CE.DEFAULT_PROFILE, CE.LAST_USE, CE.NEED_EDIT, ".
			"	".$DB->DateToCharFunction("CE.LAST_USE", "FULL")." as LAST_USE_FORMAT, ".
			" CE.CREATED_BY, CE.MODIFIED_BY, ".$DB->DateToCharFunction('CE.TIMESTAMP_X', 'FULL').' as TIMESTAMP_X, '.$DB->DateToCharFunction('CE.DATE_CREATE', 'FULL').' as DATE_CREATE ';

		$strSqlFrom =
			"FROM b_catalog_export CE ";

		if ($bCount)
		{
			$strSql =
				"SELECT COUNT(CE.ID) as CNT ".
				$strSqlFrom.
				"WHERE CE.IS_EXPORT = 'N' ".
				$strSqlSearch;
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$iCnt = 0;
			if ($ar_res = $db_res->Fetch())
			{
				$iCnt = (int)$ar_res["CNT"];
			}
			return $iCnt;
		}

		$strSql =
			$strSqlSelect.
			$strSqlFrom.
			"WHERE CE.IS_EXPORT = 'N' ".
			$strSqlSearch;

		$arSqlOrder = array();
		$arOrderKeys = array();
		foreach ($arOrder as $by=>$order)
		{
			$by = strtoupper($by);
			$order = strtoupper($order);
			if ($order!="ASC") $order = "DESC";
			if (!in_array($by, $arOrderKeys))
			{
				if ($by == "NAME") $arSqlOrder[] = "CE.NAME ".$order;
				elseif ($by == "FILE_NAME") $arSqlOrder[] = "CE.FILE_NAME ".$order;
				elseif ($by == "DEFAULT_PROFILE") $arSqlOrder[] = "CE.DEFAULT_PROFILE ".$order;
				elseif ($by == "IN_MENU") $arSqlOrder[] = "CE.IN_MENU ".$order;
				elseif ($by == "LAST_USE") $arSqlOrder[] = "CE.LAST_USE ".$order;
				elseif ($by == "IN_AGENT") $arSqlOrder[] = "CE.IN_AGENT ".$order;
				elseif ($by == "IN_CRON") $arSqlOrder[] = "CE.IN_CRON ".$order;
				elseif ($by == "NEED_EDIT") $arSqlOrder[] = "CE.NEED_EDIT ".$order;
				else
				{
					$by = "ID";
					if (in_array($by, $arOrderKeys))
						continue;
					$arSqlOrder[] = "CE.ID ".$order;
				}
				$arOrderKeys[] = $by;
			}
		}

		$strSqlOrder = "";
		if (!empty($arSqlOrder))
		{
			$strSqlOrder = ' ORDER BY '.implode(', ', $arSqlOrder);
		}

		$strSql .= $strSqlOrder;

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	public static function GetByID($ID)
	{
		global $DB;

		$strSql =
			"SELECT CE.ID, CE.FILE_NAME, CE.NAME, CE.IN_MENU, CE.IN_AGENT, ".
			"	CE.IN_CRON, CE.SETUP_VARS, CE.DEFAULT_PROFILE, CE.LAST_USE, CE.NEED_EDIT, ".
			"	".$DB->DateToCharFunction("CE.LAST_USE", "FULL")." as LAST_USE_FORMAT, ".
			" CE.CREATED_BY, CE.MODIFIED_BY, ".$DB->DateToCharFunction('CE.TIMESTAMP_X', 'FULL').' as TIMESTAMP_X, '.$DB->DateToCharFunction('CE.DATE_CREATE', 'FULL').' as DATE_CREATE '.
			"FROM b_catalog_export CE ".
			"WHERE CE.ID = ".intval($ID)." ".
			"	AND CE.IS_EXPORT = 'N'";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return false;
	}

	public static function PreGenerateImport($profile_id)
	{
		global $DB;

		$profile_id = (int)$profile_id;
		if ($profile_id <= 0)
			return false;

		$ar_profile = CCatalogImport::GetByID($profile_id);
		if ((!$ar_profile) || ('Y' == $ar_profile['NEED_EDIT']))
			return false;

		$strFile = CATALOG_PATH2IMPORTS.$ar_profile["FILE_NAME"]."_run.php";
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$strFile))
		{
			$strFile = CATALOG_PATH2IMPORTS_DEF.$ar_profile["FILE_NAME"]."_run.php";
			if (!file_exists($_SERVER["DOCUMENT_ROOT"].$strFile))
			{
				CCatalogDiscountSave::Enable();
				return false;
			}
		}

		$bFirstLoadStep = true;

		if (!defined("CATALOG_LOAD_NO_STEP"))
			define("CATALOG_LOAD_NO_STEP", true);

		$strImportErrorMessage = "";
		$strImportOKMessage = "";

		$bAllDataLoaded = true;

		$arSetupVars = array();
		$intSetupVarsCount = 0;
		if ('Y' != $ar_profile["DEFAULT_PROFILE"])
		{
			parse_str($ar_profile["SETUP_VARS"], $arSetupVars);
			if (!empty($arSetupVars) && is_array($arSetupVars))
				$intSetupVarsCount = extract($arSetupVars, EXTR_SKIP);
		}

		global $arCatalogAvailProdFields;
		$arCatalogAvailProdFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_ELEMENT);
		global $arCatalogAvailPriceFields;
		$arCatalogAvailPriceFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_CATALOG);
		global $arCatalogAvailValueFields;
		$arCatalogAvailValueFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_PRICE);
		global $arCatalogAvailQuantityFields;
		$arCatalogAvailQuantityFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_PRICE_EXT);
		global $arCatalogAvailGroupFields;
		$arCatalogAvailGroupFields = CCatalogCSVSettings::getSettingsFields(CCatalogCSVSettings::FIELDS_SECTION);

		global $defCatalogAvailProdFields;
		$defCatalogAvailProdFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_ELEMENT);
		global $defCatalogAvailPriceFields;
		$defCatalogAvailPriceFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_CATALOG);
		global $defCatalogAvailValueFields;
		$defCatalogAvailValueFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_PRICE);
		global $defCatalogAvailQuantityFields;
		$defCatalogAvailQuantityFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_PRICE_EXT);
		global $defCatalogAvailGroupFields;
		$defCatalogAvailGroupFields = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_SECTION);
		global $defCatalogAvailCurrencies;
		$defCatalogAvailCurrencies = CCatalogCSVSettings::getDefaultSettings(CCatalogCSVSettings::FIELDS_CURRENCY);

		CCatalogDiscountSave::Disable();
		include($_SERVER["DOCUMENT_ROOT"].$strFile);
		CCatalogDiscountSave::Enable();

		CCatalogImport::Update($profile_id, array(
			"=LAST_USE" => $DB->GetNowFunction()
			)
		);

		return "CCatalogImport::PreGenerateImport(".$profile_id.");";
	}
}