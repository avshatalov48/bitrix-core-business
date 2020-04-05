<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

use Bitrix\Main\UrlRewriter;

/**
 * @deprecated Use \Bitrix\Main\UrlRewriter.
 */
class CUrlRewriter
{
	public static function GetList($arFilter = array(), $arOrder = array())
	{
		global $APPLICATION;

		if (isset($arFilter["SITE_ID"]))
		{
			$siteId = $arFilter["SITE_ID"];
			unset($arFilter["SITE_ID"]);
		}
		else
		{
			$siteId = SITE_ID;
		}

		if (array_key_exists("QUERY", $arFilter) && $arFilter["QUERY"] === false)
		{
			$arFilter["QUERY"] = $APPLICATION->GetCurPage();
		}

		return UrlRewriter::getList($siteId, $arFilter, $arOrder);
	}

	public static function Add($arFields)
	{
		if (isset($arFields["SITE_ID"]))
		{
			$siteId = $arFields["SITE_ID"];
		}
		else
		{
			$siteId = SITE_ID;
		}

		UrlRewriter::add($siteId, $arFields);
	}

	public static function Update($arFilter, $arFields)
	{
		global $APPLICATION;

		if (isset($arFilter["SITE_ID"]))
		{
			$siteId = $arFilter["SITE_ID"];
			unset($arFilter["SITE_ID"]);
		}
		else
		{
			$siteId = SITE_ID;
		}

		if (array_key_exists("QUERY", $arFilter) && $arFilter["QUERY"] === false)
		{
			$arFilter["QUERY"] = $APPLICATION->GetCurPage();
		}

		UrlRewriter::update($siteId, $arFilter, $arFields);
	}

	public static function Delete($arFilter)
	{
		global $APPLICATION;

		if (isset($arFilter["SITE_ID"]))
		{
			$siteId = $arFilter["SITE_ID"];
			unset($arFilter["SITE_ID"]);
		}
		else
		{
			$siteId = SITE_ID;
		}

		if (array_key_exists("QUERY", $arFilter) && $arFilter["QUERY"] === false)
		{
			$arFilter["QUERY"] = $APPLICATION->GetCurPage();
		}

		if(isset($arFilter["ID"]) && $arFilter["ID"] == "NULL")
		{
			unset($arFilter["ID"]);
			$arFilter["!ID"] = '';
		}

		UrlRewriter::delete($siteId, $arFilter);
	}

	public static function ReIndexAll($max_execution_time = 0, $NS = array())
	{
		return UrlRewriter::reindexAll($max_execution_time, $NS);
	}

	public static function ReindexFile($path, $SEARCH_SESS_ID="", $max_file_size = 0)
	{
		CMain::InitPathVars($site, $path);

		if($site === false)
		{
			$site = SITE_ID;
		}

		$DOC_ROOT = CSite::GetSiteDocRoot($site);

		return UrlRewriter::reindexFile($site, $DOC_ROOT, $path, $max_file_size);
	}

	public static function CheckPath($path)
	{
		return UrlRewriter::checkPath($path);
	}
}
