<?php

IncludeModuleLangFile(__FILE__);

$GLOBALS["BLOG_SITE_PATH"] = Array();

class CAllBlogSitePath
{
	/*************** ADD, UPDATE, DELETE *****************/
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "PATH") || $ACTION=="ADD") && $arFields["PATH"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GSP_EMPTY_PATH"), "EMPTY_PATH");
			return false;
		}
		elseif (is_set($arFields, "PATH"))
		{
			$arFields["PATH"] = trim(str_replace("\\", "/", $arFields["PATH"]));
		}

		if ((is_set($arFields, "SITE_ID") || $ACTION=="ADD") && $arFields["SITE_ID"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GSP_EMPTY_SITE_ID"), "EMPTY_SITE_ID");
			return false;
		}
		elseif (is_set($arFields, "SITE_ID"))
		{
			$dbResult = CSite::GetByID($arFields["SITE_ID"]);
			if (!$dbResult->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["SITE_ID"], GetMessage("BLG_GSP_ERROR_NO_SITE")), "ERROR_NO_SITE");
				return false;
			}
		}

		if(is_set($arFields, "SITE_ID") && $arFields["SITE_ID"] <> '' && is_set($arFields, "TYPE") && $arFields["TYPE"] <> '')
		{
			$dbPath = CBlogSitePath::GetList(array(), array("SITE_ID" => $arFields["SITE_ID"], "TYPE" => $arFields["TYPE"]));
			if($dbPath->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("BLG_GSP_ERROR_DUPLICATE"), "ERROR_DUPLICATE");
				return false;

			}
		}

		return True;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);

		$arPath = CBlogSitePath::GetByID($ID);
		if ($arPath)
			unset($GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH1_CACHE_".$arPath["SITE_ID"]]);

		unset($GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH_CACHE_".$ID]);

		return $DB->Query("DELETE FROM b_blog_site_path WHERE ID = ".$ID."", true);
	}

	//*************** SELECT *********************/
	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);

		if (isset($GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH_CACHE_".$ID]) && is_array($GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH_CACHE_".$ID]) && is_set($GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH_CACHE_".$ID], "ID"))
		{
			return $GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH_CACHE_".$ID];
		}
		else
		{
			$strSql =
				"SELECT P.ID, P.SITE_ID, P.PATH, P.TYPE ".
				"FROM b_blog_site_path P ".
				"WHERE P.ID = ".$ID."";
			$dbResult = $DB->Query($strSql);
			if ($arResult = $dbResult->Fetch())
			{
				$GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH_CACHE_".$ID] = $arResult;
				$GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH1_CACHE_".$arResult["SITE_ID"]] = $arResult;
				return $arResult;
			}
		}

		return False;
	}

	public static function GetBySiteID($siteID)
	{
		global $DB;

		$siteID = Trim($siteID);
		if ($siteID == '')
			return False;

		if (isset($GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH1_CACHE_".$siteID]) && is_array($GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH1_CACHE_".$siteID]) && is_set($GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH1_CACHE_".$siteID], "ID"))
		{
			return $GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH1_CACHE_".$siteID];
		}
		else
		{
			$strSql =
				"SELECT P.ID, P.SITE_ID, P.PATH, P.TYPE ".
				"FROM b_blog_site_path P ".
				"WHERE P.SITE_ID = '".$DB->ForSql($siteID)."' AND P.TYPE is null";
			$dbResult = $DB->Query($strSql);
			while($arResult = $dbResult->Fetch())
			{
				$GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH1_CACHE_".$siteID] = $arResult;
				$GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH_CACHE_".$arResult["ID"]] = $arResult;
				return $arResult;
			}
		}

		return False;
	}
	
	public static function DeleteBySiteID($siteID)
	{
		global $DB;

		$siteID = Trim($siteID);
		if ($siteID == '')
			return False;

		$dbPath = CBlogSitePath::GetList(Array(), Array("SITE_ID" => $siteID));
		while($arPath = $dbPath -> Fetch())
		{
			unset($GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH1_CACHE_".$arPath["SITE_ID"]]);
			unset($GLOBALS["BLOG_SITE_PATH"]["BLOG_SITE_PATH_CACHE_".$arPath["ID"]]);
			return $DB->Query("DELETE FROM b_blog_site_path WHERE ID = ".$arPath["ID"]."", true);
		}
		
		return true;
	}
}
