<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/classes/general/sitepath.php");

// 2012-04-16 Checked/modified for compatibility with new data model
class CSitePath extends CAllSitePath
{
	/*************** ADD, UPDATE, DELETE *****************/
	// 2012-04-16 Checked/modified for compatibility with new data model
	public static function Add($arFields)
	{
		global $DB;

		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				unset($arFields[$key]);
				$arFields['~' . mb_substr($key, 1)] = $value;
			}
		}

		if (!CSitePath::CheckFields("ADD", $arFields))
			return false;

		if ($arFields)
		{
			return $DB->Add('b_learn_site_path', $arFields);
		}
		else
		{
			return false;
		}
	}

	// 2012-04-16 Checked/modified for compatibility with new data model
	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSitePath::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_learn_site_path", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($strUpdate <> '')
				$strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		if ($strUpdate <> '')
		{
			$strSql =
				"UPDATE b_learn_site_path SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql);

			unset($GLOBALS["LEARNING_SITE_PATH"]["LEARNING_SITE_PATH_CACHE_".$ID]);

			return $ID;
		}

		return False;
	}

	//*************** SELECT *********************/
	// 2012-04-16 Checked/modified for compatibility with new data model
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "SITE_ID", "PATH", "TYPE");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "P.ID", "TYPE" => "int"),
				"SITE_ID" => array("FIELD" => "P.SITE_ID", "TYPE" => "string"),
				"PATH" => array("FIELD" => "P.PATH", "TYPE" => "string"),
				"TYPE" => array("FIELD" => "P.TYPE", "TYPE" => "string"),
			);
		// <-- FIELDS

		$arSqls = CSitePath::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_learn_site_path P ".
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
			"FROM b_learn_site_path P ".
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
				"FROM b_learn_site_path P ".
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
				// MYSQL only
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();

			//echo "!2.2!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT " . (int) $arNavStartParams["nTopCount"];

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql);
		}

		return $dbRes;
	}
}
