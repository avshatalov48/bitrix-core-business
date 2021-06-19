<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/general/blog_post_category.php");

class CBlogPostCategory extends CAllBlogPostCategory
{
	/*************** ADD, UPDATE, DELETE *****************/
	public static function Add($arFields)
	{
		global $DB;

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CBlogPostCategory::CheckFields("ADD", $arFields))
		{
			return false;
		}

		$arInsert = $DB->PrepareInsert("b_blog_post_category", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($arInsert[0] <> '')
				$arInsert[0] .= ", ";
			$arInsert[0] .= $key;
			if ($arInsert[1] <> '')
				$arInsert[1] .= ", ";
			$arInsert[1] .= $value;
		}

		if ($arInsert[0] <> '')
		{
			$strSql =
				"INSERT INTO b_blog_post_category(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			$ID = intval($DB->LastID());

			return $ID;
		}

		return False;
	}

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

		if (!CBlogPostCategory::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_blog_post_category", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($strUpdate <> '')
				$strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		if ($strUpdate <> '')
		{
			$strSql =
				"UPDATE b_blog_post_category SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

			return $ID;
		}

		return False;
	}

	//*************** SELECT *********************/
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "BLOG_ID", "POST_ID", "CATEGORY_ID", "NAME");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "C.ID", "TYPE" => "int"),
				"BLOG_ID" => array("FIELD" => "C.BLOG_ID", "TYPE" => "int"),
				"POST_ID" => array("FIELD" => "C.POST_ID", "TYPE" => "int"),
				"CATEGORY_ID" => array("FIELD" => "C.CATEGORY_ID", "TYPE" => "int"),
				"NAME" => array("FIELD" => "CC.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_blog_category CC ON (CC.ID = C.CATEGORY_ID)"),
				"POST_PUBLISH_STATUS" => array("FIELD" => "P.PUBLISH_STATUS", "TYPE" => "string", "FROM" => "INNER JOIN b_blog_post P ON (P.ID = C.POST_ID)"),
				
			);
		// <-- FIELDS

		$arSqls = CBlog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_blog_post_category C ".
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
			"FROM b_blog_post_category C ".
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
				"FROM b_blog_post_category C ".
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
