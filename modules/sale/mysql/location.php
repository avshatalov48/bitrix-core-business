<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/location.php");

use Bitrix\Sale\Location;

class CSaleLocation extends CAllSaleLocation
{
	public static function GetList($arOrder = array("SORT"=>"ASC", "COUNTRY_NAME_LANG"=>"ASC", "CITY_NAME_LANG"=>"ASC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (is_string($arGroupBy) && mb_strlen($arGroupBy) == 2)
		{
			$arFilter["LID"] = $arGroupBy;
			$arGroupBy = false;

			$arSelectFields = array("ID", "COUNTRY_ID", "REGION_ID", "CITY_ID", "SORT", "COUNTRY_NAME_ORIG", "COUNTRY_SHORT_NAME", "COUNTRY_NAME_LANG", "CITY_NAME_ORIG", "CITY_SHORT_NAME", "CITY_NAME_LANG", "REGION_NAME_ORIG", "REGION_SHORT_NAME", "REGION_NAME_LANG", "COUNTRY_NAME", "CITY_NAME", "REGION_NAME", "LOC_DEFAULT");
		}

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "COUNTRY_ID", "REGION_ID", "CITY_ID", "SORT", "COUNTRY_NAME_ORIG", "COUNTRY_SHORT_NAME", "REGION_NAME_ORIG", "CITY_NAME_ORIG", "REGION_SHORT_NAME", "CITY_SHORT_NAME", "COUNTRY_LID", "COUNTRY_NAME", "REGION_LID", "CITY_LID", "REGION_NAME", "CITY_NAME", "LOC_DEFAULT");

		if(!is_array($arOrder))
			$arOrder = array();

		foreach ($arOrder as $key => $dir)
		{
			if (!in_array($key, $arSelectFields))
				$arSelectFields[] = $key;
		}

		$arFilter = self::getFilterForGetList($arFilter);
		$arFields = self::getFieldMapForGetList($arFilter);

		// <-- FIELDS

		$arSqls = CSaleOrder::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sale_location L ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sale_location L ".
			"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sale_location L ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
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

	public static function GetByID($ID, $strLang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
			return parent::GetByID($ID, $strLang);

		global $DB;

		$ID = intval($ID);
		/*$strSql =
			"SELECT L.ID, L.COUNTRY_ID, L.CITY_ID, L.SORT, ".
			"	LC.NAME as COUNTRY_NAME_ORIG, LC.SHORT_NAME as COUNTRY_SHORT_NAME, LCL.NAME as COUNTRY_NAME_LANG, ".
			"	LG.NAME as CITY_NAME_ORIG, LG.SHORT_NAME as CITY_SHORT_NAME, LGL.NAME as CITY_NAME_LANG, ".
			"	IF(LCL.ID IS NULL, LC.NAME, LCL.NAME) as COUNTRY_NAME, ".
			"	IF(LGL.ID IS NULL, LG.NAME, LGL.NAME) as CITY_NAME ".
			"FROM b_sale_location L ".
			"	LEFT JOIN b_sale_location_country LC ON (L.COUNTRY_ID = LC.ID) ".
			"	LEFT JOIN b_sale_location_city LG ON (L.CITY_ID = LG.ID) ".
			"	LEFT JOIN b_sale_location_country_lang LCL ON (LC.ID = LCL.COUNTRY_ID AND LCL.LID = '".$DB->ForSql($strLang, 2)."') ".
			"	LEFT JOIN b_sale_location_city_lang LGL ON (LG.ID = LGL.CITY_ID AND LGL.LID = '".$DB->ForSql($strLang, 2)."') ".
			"WHERE L.ID = ".$ID." ";*/

		$strSql = "
		SELECT L.ID, L.COUNTRY_ID, L.CITY_ID, L.SORT, LC.NAME as COUNTRY_NAME_ORIG, LC.SHORT_NAME as COUNTRY_SHORT_NAME, LCL.NAME as COUNTRY_NAME_LANG,
		LG.NAME as CITY_NAME_ORIG, LG.SHORT_NAME as CITY_SHORT_NAME, LGL.NAME as CITY_NAME_LANG,
		L.REGION_ID, LR.NAME as REGION_NAME_ORIG, LR.SHORT_NAME as REGION_SHORT_NAME, LRL.NAME as REGION_NAME_LANG,
		IF(LCL.ID IS NULL, LC.NAME, LCL.NAME) as COUNTRY_NAME,
		IF(LGL.ID IS NULL, LG.NAME, LGL.NAME) as CITY_NAME,
		IF(LRL.ID IS NULL, LR.NAME, LRL.NAME) as REGION_NAME
		FROM b_sale_location L
			LEFT JOIN b_sale_location_country LC ON (L.COUNTRY_ID = LC.ID)
			LEFT JOIN b_sale_location_city LG ON (L.CITY_ID = LG.ID)
			LEFT JOIN b_sale_location_country_lang LCL ON (LC.ID = LCL.COUNTRY_ID AND LCL.LID = '".$DB->ForSql($strLang, 2)."')
			LEFT JOIN b_sale_location_city_lang LGL ON (LG.ID = LGL.CITY_ID AND LGL.LID = '".$DB->ForSql($strLang, 2)."')
			LEFT JOIN b_sale_location_region LR ON (L.REGION_ID = LR.ID)
			LEFT JOIN b_sale_location_region_lang LRL ON (LR.ID = LRL.REGION_ID AND LRL.LID = '".$DB->ForSql($strLang, 2)."')
		WHERE L.ID = ".$ID." ";

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	public static function GetCountryList($arOrder = Array("NAME_LANG"=>"ASC"), $arFilter=Array(), $strLang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
			return self::GetLocationTypeList('COUNTRY', $arOrder, $arFilter, $strLang);

		global $DB;
		$arSqlSearch = Array();

		if(!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$countFilterKey = count($filter_keys);
		for($i=0; $i < $countFilterKey; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if ($val == '') continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = mb_substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch(ToUpper($key))
			{
			case "ID":
				$arSqlSearch[] = "C.ID ".($bInvert?"<>":"=")." ".intval($val)." ";
				break;
			case "NAME":
				$arSqlSearch[] = "C.NAME ".($bInvert?"<>":"=")." '".$val."' ";
				break;
			}
		}

		$strSqlSearch = "";
		$countSqlSearch = count($arSqlSearch);
		for($i=0; $i < $countSqlSearch; $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql =
			"SELECT DISTINCT C.ID, C.NAME as NAME_ORIG, C.SHORT_NAME, CL.NAME as NAME, ".
			"	IF(CL.ID IS NULL, C.NAME, CL.NAME) as NAME_LANG ".
			"FROM b_sale_location_country C ".
			"	LEFT JOIN b_sale_location_country_lang CL ON (C.ID = CL.COUNTRY_ID AND CL.LID = '".$DB->ForSql($strLang, 2)."') ".
			(
				$arOrder["SORT"] <> ''
				?
				"	LEFT JOIN b_sale_location SL ON (SL.COUNTRY_ID = C.ID AND (SL.CITY_ID = 0 OR ISNULL(SL.CITY_ID))) "
				:
				""
			).
			"WHERE 1 = 1 ".
			"	".$strSqlSearch." ";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = ToUpper($by);
			$order = ToUpper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "SORT") $arSqlOrder[] = " SL.SORT ".$order;
			elseif ($by == "ID") $arSqlOrder[] = " C.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " C.NAME ".$order." ";
			elseif ($by == "SHORT_NAME") $arSqlOrder[] = " C.SHORT_NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " CL.NAME ".$order." ";
				$by = "NAME_LANG";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$countSqlOrder = count($arSqlOrder);
		for ($i=0; $i < $countSqlOrder; $i++)
		{
			if ($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ", ";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	/**
	* The function select all region
	*
	* @param array $arOrder sorting an array of results
	* @param array $arFilter filtered an array of results
	* @param string $strLang language regions of the sample
	* @return true false
	*/
	public static function GetRegionList($arOrder = Array("NAME_LANG"=>"ASC"), $arFilter=Array(), $strLang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
			return self::GetLocationTypeList('REGION', $arOrder, $arFilter, $strLang);

		global $DB;
		$arSqlSearch = Array();

		if(!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$countFilterKey = count($filter_keys);
		for($i=0; $i < $countFilterKey; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if ($val == '') continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = mb_substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch(ToUpper($key))
			{
				case "ID":
					$arSqlSearch[] = "C.ID ".($bInvert?"<>":"=")." ".intval($val)." ";
					break;
				case "NAME":
					$arSqlSearch[] = "C.NAME ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "COUNTRY_ID":
					$arSqlSearch[] = "SL.COUNTRY_ID ".($bInvert?"<>":"=")." '".$val."' ";
					break;
			}
		}

		$strSqlSearch = "";
		$countSqlSearch = count($arSqlSearch);
		for($i=0; $i < $countSqlSearch; $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql =
			"SELECT C.ID, C.NAME as NAME_ORIG, C.SHORT_NAME, CL.NAME as NAME, ".
			"	IF(CL.ID IS NULL, C.NAME, CL.NAME) as NAME_LANG ".
			"FROM b_sale_location_region C ".
			"	LEFT JOIN b_sale_location_region_lang CL ON (C.ID = CL.REGION_ID AND CL.LID = '".$DB->ForSql($strLang, 2)."') ".
			"	LEFT JOIN b_sale_location SL ON (SL.REGION_ID = C.ID AND (SL.CITY_ID = 0 OR ISNULL(SL.CITY_ID))) ".
			"WHERE 1 = 1 ".
			"	".$strSqlSearch." ";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = ToUpper($by);
			$order = ToUpper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "SORT") $arSqlOrder[] = " SL.SORT ".$order;
			elseif ($by == "ID") $arSqlOrder[] = " C.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " C.NAME ".$order." ";
			elseif ($by == "SHORT_NAME") $arSqlOrder[] = " C.SHORT_NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " CL.NAME ".$order." ";
				$by = "NAME_LANG";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$countSqlOrder = count($arSqlOrder);
		for ($i=0; $i < $countSqlOrder; $i++)
		{
			if ($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ", ";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	/**
	 * The function select all cities
	 *
	 * @param array $arOrder sorting an array of results
	 * @param array $arFilter filtered an array of results
	 * @param string $strLang language regions of the sample
	 * @return true false
	 */
	public static function GetCityList($arOrder = Array("NAME_LANG"=>"ASC"), $arFilter=Array(), $strLang = LANGUAGE_ID)
	{
		if(self::isLocationProMigrated())
			return self::GetLocationTypeList('CITY', $arOrder, $arFilter, $strLang);

		global $DB;
		$arSqlSearch = Array();

		if(!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$countFilterKey = count($filter_keys);
		for($i=0; $i < $countFilterKey; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if ($val == '') continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = mb_substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch(ToUpper($key))
			{
				case "ID":
					$arSqlSearch[] = "C.ID ".($bInvert?"<>":"=")." ".intval($val)." ";
					break;
				case "NAME":
					$arSqlSearch[] = "C.NAME ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "REGION_ID":
					$arSqlSearch[] = "SL.REGION_ID ".($bInvert?"<>":"=")." '".$val."' ";
					break;
			}
		}

		$strSqlSearch = "";
		$countSqlSearch = count($arSqlSearch);
		for($i=0; $i < $countSqlSearch; $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql =
			"SELECT C.ID, C.NAME as NAME_ORIG, C.SHORT_NAME, CL.NAME as NAME, ".
			"	IF(CL.ID IS NULL, C.NAME, CL.NAME) as NAME_LANG ".
			"FROM b_sale_location_city C ".
			"	LEFT JOIN b_sale_location_city_lang CL ON (C.ID = CL.CITY_ID AND CL.LID = '".$DB->ForSql($strLang, 2)."') ".
			"	LEFT JOIN b_sale_location SL ON (SL.CITY_ID = C.ID) ".
			"WHERE 1 = 1 ".
			"	".$strSqlSearch." ";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = ToUpper($by);
			$order = ToUpper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "SORT") $arSqlOrder[] = " SL.SORT ".$order;
			elseif ($by == "ID") $arSqlOrder[] = " C.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " C.NAME ".$order." ";
			elseif ($by == "SHORT_NAME") $arSqlOrder[] = " C.SHORT_NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " CL.NAME ".$order." ";
				$by = "NAME_LANG";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$countSqlOrder = count($arSqlOrder);
		for ($i=0; $i < $countSqlOrder; $i++)
		{
			if ($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ", ";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	// have to use old table as a temporal place to store countries, kz add of a country doesn`t mean add of a location
	public static function AddCountry($arFields)
	{
		global $DB;

		if (!CSaleLocation::CountryCheckFields("ADD", $arFields))
			return false;

		if(self::isLocationProMigrated())
		{
			return self::AddLocationUnattached('COUNTRY', $arFields);
		}

		foreach (GetModuleEvents('sale', 'OnBeforeCountryAdd', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($arFields))===false)
				return false;
		}

		$arInsert = $DB->PrepareInsert("b_sale_location_country", $arFields);
		$strSql =
			"INSERT INTO b_sale_location_country(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		$db_lang = CLangAdmin::GetList('sort', 'asc', array("ACTIVE" => "Y"));
		while ($arLang = $db_lang->Fetch())
		{
			if ($arFields[$arLang['LID']])
			{
				$arInsert = $DB->PrepareInsert("b_sale_location_country_lang", $arFields[$arLang["LID"]]);
				$strSql =
					"INSERT INTO b_sale_location_country_lang(COUNTRY_ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}

		foreach (GetModuleEvents('sale', 'OnCountryAdd', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	// have to use old table as a temporal place to store cities, kz we don`t know yet which country\region a newly-created city belongs to
	public static function AddCity($arFields)
	{
		global $DB;

		if (!CSaleLocation::CityCheckFields("ADD", $arFields))
			return false;

		if(self::isLocationProMigrated())
		{
			return self::AddLocationUnattached('CITY', $arFields);
		}

		foreach (GetModuleEvents('sale', 'OnBeforeCityAdd', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($arFields))===false)
				return false;
		}

		$arInsert = $DB->PrepareInsert("b_sale_location_city", $arFields);
		$strSql =
			"INSERT INTO b_sale_location_city(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		$db_lang = CLangAdmin::GetList('sort', 'asc', array("ACTIVE" => "Y"));
		while ($arLang = $db_lang->Fetch())
		{
			if ($arFields[$arLang["LID"]])
			{
				$arInsert = $DB->PrepareInsert("b_sale_location_city_lang", $arFields[$arLang["LID"]]);
				$strSql =
					"INSERT INTO b_sale_location_city_lang(CITY_ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}

		foreach (GetModuleEvents('sale', 'OnCityAdd', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	// have to use old table as a temporal place to store region, kz we don`t know yet which country a newly-created region belongs to
	public static function AddRegion($arFields)
	{
		global $DB;

		if (!CSaleLocation::RegionCheckFields("ADD", $arFields))
			return false;

		if(self::isLocationProMigrated())
		{
			return self::AddLocationUnattached('REGION', $arFields);
		}

		foreach (GetModuleEvents('sale', 'OnBeforeRegionAdd', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($arFields))===false)
				return false;
		}

		$arInsert = $DB->PrepareInsert("b_sale_location_region", $arFields);
		$strSql =
			"INSERT INTO b_sale_location_region(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		$db_lang = CLangAdmin::GetList('sort', 'asc', array("ACTIVE" => "Y"));
		while ($arLang = $db_lang->Fetch())
		{
			if ($arFields[$arLang["LID"]])
			{
				$arInsert = $DB->PrepareInsert("b_sale_location_region_lang", $arFields[$arLang["LID"]]);
				$strSql =
					"INSERT INTO b_sale_location_region_lang(REGION_ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}

		foreach (GetModuleEvents('sale', 'OnRegionAdd', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	public static function AddLocation($arFields)
	{
		global $DB;

		if (!CSaleLocation::LocationCheckFields("ADD", $arFields))
			return false;

		if(self::isLocationProMigrated())
		{
			return self::RebindLocationTriplet($arFields);
		}

		// make IX_B_SALE_LOC_CODE feel happy
		$arFields['CODE'] = 'randstr'.rand(999, 99999);

		foreach (GetModuleEvents('sale', 'OnBeforeLocationAdd', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($arFields))===false)
				return false;
		}

		$arInsert = $DB->PrepareInsert("b_sale_location", $arFields);
		$strSql =
			"INSERT INTO b_sale_location(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		// make IX_B_SALE_LOC_CODE feel happy
		Location\LocationTable::update($ID, array('CODE' => $ID));

		foreach (GetModuleEvents('sale', 'OnLocationAdd', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}
}
