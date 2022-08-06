<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/group_features.php");

class CSocNetFeatures extends CAllSocNetFeatures
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function Add($arFields)
	{
		global $DB, $CACHE_MANAGER, $SONET_FEATURES_CACHE;

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetFeatures::CheckFields("ADD", $arFields))
		{
			return false;
		}

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetFeaturesAdd");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array($arFields))===false)
			{
				return false;
			}
		}

		$arInsert = $DB->PrepareInsert("b_sonet_features", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToInsert($arFields1, $arInsert);

		$ID = false;
		if ($arInsert[0] <> '')
		{
			$strSql =
				"INSERT INTO b_sonet_features(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = intval($DB->LastID());

			if (array_key_exists("ENTITY_TYPE", $arFields) && array_key_exists("ENTITY_ID", $arFields))
			{
				unset($SONET_FEATURES_CACHE[$arFields["ENTITY_TYPE"]][$arFields["ENTITY_ID"]]);
			}

			$events = GetModuleEvents("socialnetwork", "OnSocNetFeaturesAdd");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));
			}

			if (
				defined("BX_COMP_MANAGED_CACHE")
				&& array_key_exists("ENTITY_TYPE", $arFields) 
				&& array_key_exists("ENTITY_ID", $arFields)
			)
			{
				$CACHE_MANAGER->ClearByTag('sonet_features');
				$CACHE_MANAGER->ClearByTag("sonet_features_".$arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]);
			}
		}

		return $ID;
	}

	
	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
		{
			$arSelectFields = array("ID", "ENTITY_TYPE", "ENTITY_ID", "FEATURE", "FEATURE_NAME", "ACTIVE", "DATE_CREATE", "DATE_UPDATE");
		}

		static $arFields = array(
			"ID" => Array("FIELD" => "GF.ID", "TYPE" => "int"),
			"ENTITY_TYPE" => Array("FIELD" => "GF.ENTITY_TYPE", "TYPE" => "string"),
			"ENTITY_ID" => Array("FIELD" => "GF.ENTITY_ID", "TYPE" => "int"),
			"FEATURE" => Array("FIELD" => "GF.FEATURE", "TYPE" => "string"),
			"FEATURE_NAME" => Array("FIELD" => "GF.FEATURE_NAME", "TYPE" => "string"),
			"ACTIVE" => Array("FIELD" => "GF.ACTIVE", "TYPE" => "string"),
			"DATE_CREATE" => Array("FIELD" => "GF.DATE_CREATE", "TYPE" => "datetime"),
			"DATE_UPDATE" => Array("FIELD" => "GF.DATE_UPDATE", "TYPE" => "datetime"),
			"GROUP_NAME" => Array("FIELD" => "G.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_group G ON (GF.GROUP_ID = G.ID)"),
		);

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_features GF ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
			{
				return $arRes["CNT"];
			}
			else
			{
				return False;
			}
		}


		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sonet_features GF ".
			"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_sonet_features GF ".
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
				// ÒÎËÜÊÎ ÄËß MYSQL!!! ÄËß ORACLE ÄÐÓÃÎÉ ÊÎÄ
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}
}
