<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/event_user_view.php");

class CSocNetEventUserView extends CAllSocNetEventUserView
{
	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function Add($arFields)
	{
		global $DB;

		if (!CSocNetEventUserView::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_sonet_event_user_view", $arFields);

		if ($arInsert[0] <> '')
		{
			$strSql =
				"INSERT INTO b_sonet_event_user_view(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql);
		}

		return true;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetList($arOrder = Array("ENTITY_ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ENTITY_TYPE", "ENTITY_ID", "EVENT_ID", "USER_ID", "USER_ANONYMOUS");

		static $arFields = array(
			"ENTITY_TYPE" => Array("FIELD" => "EUV.ENTITY_TYPE", "TYPE" => "string"),
			"ENTITY_ID" => Array("FIELD" => "EUV.ENTITY_ID", "TYPE" => "int"),
			"EVENT_ID" => Array("FIELD" => "EUV.EVENT_ID", "TYPE" => "string"),
			"USER_ID" => Array("FIELD" => "EUV.USER_ID", "TYPE" => "int"),
			"USER_ANONYMOUS" => Array("FIELD" => "EUV.USER_ANONYMOUS", "TYPE" => "string"),
		);

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_sonet_event_user_view EUV ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}


		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_sonet_event_user_view EUV ".
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
				"FROM b_sonet_event_user_view EUV ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				// MYSQL ONLY!!!
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

			$dbRes = $DB->Query($strSql);
		}

		return $dbRes;
	}
}
