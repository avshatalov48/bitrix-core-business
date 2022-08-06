<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/group_features_perms.php");

class CSocNetFeaturesPerms extends CAllSocNetFeaturesPerms
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function Add($arFields)
	{
		global $DB, $CACHE_MANAGER;

		$arFields1 = \Bitrix\Socialnetwork\Util::getEqualityFields($arFields);

		if (!CSocNetFeaturesPerms::CheckFields("ADD", $arFields))
		{
			return false;
		}

		$db_events = GetModuleEvents("socialnetwork", "OnBeforeSocNetFeaturesPermsAdd");
		while ($arEvent = $db_events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array($arFields)) === false)
			{
				return false;
			}
		}

		$arInsert = $DB->PrepareInsert("b_sonet_features2perms", $arFields);
		\Bitrix\Socialnetwork\Util::processEqualityFieldsToInsert($arFields1, $arInsert);

		$ID = false;
		if ($arInsert[0] <> '')
		{
			$strSql =
				"INSERT INTO b_sonet_features2perms(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = intval($DB->LastID());

			$events = GetModuleEvents("socialnetwork", "OnSocNetFeaturesPermsAdd");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));
			}

			if (
				intval($arFields["FEATURE_ID"]) > 0
				&& defined("BX_COMP_MANAGED_CACHE")
			)
			{
				$CACHE_MANAGER->ClearByTag('sonet_features2perms');
				$CACHE_MANAGER->ClearByTag("sonet_feature_".$arFields["FEATURE_ID"]);
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
			$arSelectFields = array("ID", "FEATURE_ID", "OPERATION_ID", "ROLE");

		static $arFields = array(
			"ID" => Array("FIELD" => "GFP.ID", "TYPE" => "int"),
			"FEATURE_ID" => Array("FIELD" => "GFP.FEATURE_ID", "TYPE" => "int"),
			"OPERATION_ID" => Array("FIELD" => "GFP.OPERATION_ID", "TYPE" => "string"),
			"ROLE" => Array("FIELD" => "GFP.ROLE", "TYPE" => "string"),
			"FEATURE_ENTITY_TYPE" => Array("FIELD" => "GF.ENTITY_TYPE", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_features GF ON (GFP.FEATURE_ID = GF.ID)"),
			"FEATURE_ENTITY_ID" => Array("FIELD" => "GF.ENTITY_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_sonet_features GF ON (GFP.FEATURE_ID = GF.ID)"),
			"FEATURE_FEATURE" => Array("FIELD" => "GF.FEATURE", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_features GF ON (GFP.FEATURE_ID = GF.ID)"),
			"FEATURE_FEATURE_NAME" => Array("FIELD" => "GF.FEATURE_NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_features GF ON (GFP.FEATURE_ID = GF.ID)"),
			"FEATURE_ACTIVE" => Array("FIELD" => "GF.ACTIVE", "TYPE" => "string", "FROM" => "INNER JOIN b_sonet_features GF ON (GFP.FEATURE_ID = GF.ID)"),
		);

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_features2perms GFP ".
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
			"FROM b_sonet_features2perms GFP ".
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
				"FROM b_sonet_features2perms GFP ".
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
	
	function GetAvaibleEntity($entityType, $feature, $role, $operation, $active, $visible, $siteID)
	{
		global $DB;
		
		if($entityType == '' || $role == '' || $operation == '')
			return false;
		if($entityType == '')
			$entityType = "G";
		if($active == '')
			$active = "Y";
		if($visible == '')
			$visible = "Y";
		if($siteID == '')
			$siteID = SITE_ID;		
			
		$strSql = "select b.ID as ID,
					b.ENTITY_TYPE as ENTITY_TYPE,
					b.ENTITY_ID as ENTITY_ID,
					b.FEATURE as FEATURE,
					b.ACTIVE as FEATURE_ACTIVE,
					p.OPERATION_ID as OPERATION_ID,
					p.ROLE as ROLE ";
		if($entityType == "G")
			$strSql .= ", g.SITE_ID as GROUP_SITE_ID,
					g.NAME as GROUP_NAME,
					g.VISIBLE as GROUP_VISIBLE,
					g.OWNER_ID as GROUP_OWNER_ID ";
		$strSql .= " from b_sonet_features b ".
					"LEFT JOIN b_sonet_features2perms p ON (b.ID = p.FEATURE_ID AND ". 
					"p.ROLE = '".$DB->ForSQL($role)."' AND p.OPERATION_ID = '".$DB->ForSQL($operation)."') ";
		if($entityType == "G")
			$strSql .= "INNER JOIN b_sonet_group g ON (g.ID = b.ENTITY_ID) ";
		$strSql .= "WHERE ".
					"b.FEATURE='".$DB->ForSQL($feature)."' AND ".
					"b.ACTIVE = '".$DB->ForSQL($active)."' AND ".
					"b.ENTITY_TYPE = '".$DB->ForSQL($entityType)."' ";

		if($entityType == "G")
			$strSql .= " AND g.ACTIVE = 'Y' AND ".
						"g.VISIBLE= 'Y' AND ". 
						"g.SITE_ID= '".$DB->ForSQL($siteID)."'";

		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $dbRes;
	}
}
