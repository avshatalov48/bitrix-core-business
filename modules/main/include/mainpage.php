<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/start.php");

class CMainPage
{
	public static function GetSiteByHost()
	{
		$cur_host = $_SERVER["HTTP_HOST"];
		$arURL = parse_url("http://" . $cur_host);
		if ($arURL["scheme"] == '' && $arURL["host"] != '')
		{
			$CURR_DOMAIN = $arURL["host"];
		}
		else
		{
			$CURR_DOMAIN = $cur_host;
		}

		if (mb_strpos($CURR_DOMAIN, ':') > 0)
		{
			$CURR_DOMAIN = mb_substr($CURR_DOMAIN, 0, mb_strpos($CURR_DOMAIN, ':'));
		}
		$CURR_DOMAIN = trim($CURR_DOMAIN, "\t\r\n\0 .");

		global $DB;
		$strSql =
			"SELECT L.LID as SITE_ID ".
			"FROM b_lang L, b_lang_domain LD ".
			"WHERE L.ACTIVE='Y' ".
			"	AND L.LID=LD.LID ".
			"	AND '" . $DB->ForSql("." . $CURR_DOMAIN, 255) . "' LIKE " . $DB->Concat("'%.'", "LD.DOMAIN") . " " .
			"ORDER BY " . $DB->Length("LD.DOMAIN") . " DESC, L.SORT";

		$res = $DB->Query($strSql);
		if ($ar_res = $res->Fetch())
		{
			return $ar_res["SITE_ID"];
		}

		$sl = CSite::GetDefList();
		while ($slang = $sl->Fetch())
		{
			if ($slang["DEF"] == "Y")
			{
				return $slang["SITE_ID"];
			}
		}

		return false;
	}

	public static function GetSiteByAcceptLanguage($compare_site_id=false)
	{
		$site_id = false;
		$last_site_id = '';
		$arSites = [];

		$rsSites = CSite::GetDefList();
		while ($arSite = $rsSites->Fetch())
		{
			$last_site_id = $arSite["ID"];
			if ($arSite["DEF"] == "Y")
			{
				$site_id = $arSite["ID"];
			}
			$arSites[] = $arSite;
		}

		$arUserLang = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
		if (is_array($arUserLang))
		{
			foreach ($arUserLang as $user_lid)
			{
				$user_lid = mb_strtolower(mb_substr($user_lid, 0, 2));
				foreach ($arSites as $arSite)
				{
					$sid = ($compare_site_id) ? mb_strtolower($arSite["ID"]) : mb_strtolower($arSite["LANGUAGE_ID"]);
					if ($user_lid == $sid)
					{
						return $arSite["ID"];
					}
				}
			}
		}
		if ($site_id == '')
		{
			return $last_site_id;
		}
		return $site_id;
	}

	public static function RedirectToSite($site)
	{
		if ($site == '')
		{
			return;
		}
		$db_site = CSite::GetByID($site);
		if ($arSite = $db_site->Fetch())
		{
			$arSite["DIR"] = rtrim($arSite["DIR"], ' \/');
			if ($arSite["DIR"] != '')
			{
				LocalRedirect(($arSite["SERVER_NAME"] != '' ? "http://" . $arSite["SERVER_NAME"] : '') . $arSite["DIR"] . $_SERVER["REQUEST_URI"], true);
			}
		}
	}

	public static function GetIncludeSitePage($site)
	{
		if ($site == '')
		{
			return false;
		}
		$db_site = CSite::GetByID($site);
		if ($arSite = $db_site->Fetch())
		{
			$arSite["DIR"] = rtrim($arSite["DIR"], ' \/');
			$cur_page = GetPagePath();
			if ($arSite["DIR"] != '')
			{
				$_SERVER["REQUEST_URI"] = $arSite["DIR"] . $cur_page;
				return $_SERVER["DOCUMENT_ROOT"] . $_SERVER["REQUEST_URI"];
			}
		}
		return false;
	}
}
