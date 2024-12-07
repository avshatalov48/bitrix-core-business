<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/blog/general/blog_image.php");

class CBlogImage extends CAllBlogImage
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

		if (!CBlogImage::CheckFields("ADD", $arFields))
			return false;

		if (is_array($arFields['FILE_ID']))
		{
			if (
				array_key_exists("FILE_ID", $arFields)
				&& is_array($arFields["FILE_ID"])
				&& (
					!array_key_exists("MODULE_ID", $arFields["FILE_ID"])
					|| $arFields["FILE_ID"]["MODULE_ID"] == ''
				)
			)
				$arFields["FILE_ID"]["MODULE_ID"] = "blog";

			$prefix = "blog";
			if($arFields["URL"] <> '')
				$prefix .= "/".$arFields["URL"];

			CFile::SaveForDB($arFields, "FILE_ID", $prefix);
		}

		if (
			isset($arFields['FILE_ID']) &&
			( intval($arFields['FILE_ID']) == $arFields['FILE_ID'] )
		)
		{
			$arInsert = $DB->PrepareInsert("b_blog_image", $arFields);

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
					"INSERT INTO b_blog_image(".$arInsert[0].") ".
					"VALUES(".$arInsert[1].")";
				$DB->Query($strSql);

				$ID = intval($DB->LastID());

				return $ID;
			}
		}
		else
		{
				$GLOBALS["APPLICATION"]->ThrowException("Error Adding file by CFile::SaveForDB");
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
		if (!CBlogImage::CheckFields("UPDATE", $arFields, $ID))
			return false;
		$strUpdate = $DB->PrepareUpdate("b_blog_image", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($strUpdate <> '')
				$strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}
		if ($strUpdate <> '')
		{
			$strSql =
				"UPDATE b_blog_image SET ".
				"	".$strUpdate." ".
				"WHERE ID = ".$ID." ";
			$DB->Query($strSql);

			unset($GLOBALS["BLOG_IMAGE"]["BLOG_IMAGE_CACHE_".$ID]);

			return $ID;
		}

		return False;
	}

	//*************** SELECT *********************/
	public static function GetList($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "FILE_ID", "POST_ID", "BLOG_ID", "USER_ID", "TITLE", "TIMESTAMP_X", "IMAGE_SIZE");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "G.ID", "TYPE" => "int"),
				"FILE_ID" => array("FIELD" => "G.FILE_ID", "TYPE" => "int"),
				"POST_ID" => array("FIELD" => "G.POST_ID", "TYPE" => "int"),
				"BLOG_ID" => array("FIELD" => "G.BLOG_ID", "TYPE" => "int"),
				"USER_ID" => array("FIELD" => "G.USER_ID", "TYPE" => "int"),
				"TITLE" => array("FIELD" => "G.TITLE", "TYPE" => "string"),
				"TIMESTAMP_X" => array("FIELD" => "G.TIMESTAMP_X", "TYPE" => "datetime"),
				"IMAGE_SIZE" => array("FIELD" => "G.IMAGE_SIZE", "TYPE" => "int"),
				"IS_COMMENT" => array("FIELD" => "G.IS_COMMENT", "TYPE" => "string"),
				"COMMENT_ID" => array("FIELD" => "G.COMMENT_ID", "TYPE" => "int"),
			);
		// <-- FIELDS

		$arSqls = CBlog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_blog_image G ".
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
			"FROM b_blog_image G ".
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
				"FROM b_blog_image G ".
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
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();
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
