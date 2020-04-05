<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/start.php");
error_reporting(COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE));
class CMainPage
{
	// определяет сайт по HTTP_HOST в таблице сайтов
	function GetSiteByHost()
	{
		$cur_host = $_SERVER["HTTP_HOST"];
		$arURL = parse_url("http://".$cur_host);
		if($arURL["scheme"]=="" && strlen($arURL["host"])>0)
			$CURR_DOMAIN = $arURL["host"];
		else
			$CURR_DOMAIN = $cur_host;

		if(strpos($CURR_DOMAIN, ':')>0)
			$CURR_DOMAIN = substr($CURR_DOMAIN, 0, strpos($CURR_DOMAIN, ':'));
		$CURR_DOMAIN = Trim($CURR_DOMAIN, "\t\r\n\0 .");

		global $DB;
		$strSql =
			"SELECT L.LID as SITE_ID ".
			"FROM b_lang L, b_lang_domain LD ".
			"WHERE L.ACTIVE='Y' ".
			"	AND L.LID=LD.LID ".
			"	AND '".$DB->ForSql($CURR_DOMAIN, 255)."' LIKE ".$DB->Concat("'%'", "LD.DOMAIN")." ".
			"ORDER BY ".$DB->Length("LD.DOMAIN")." DESC, L.SORT";

		$res = $DB->Query($strSql);
		if($ar_res = $res->Fetch())
			return $ar_res["SITE_ID"];

		$sl = CSite::GetDefList();
		while ($slang = $sl->Fetch())
			if($slang["DEF"]=="Y")
				return $slang["SITE_ID"];

		return false;
	}

	// определяет сайт по HTTP_ACCEPT_LANGUAGE
	function GetSiteByAcceptLanguage($compare_site_id=false)
	{
		$site_id = false;
		$arUserLang = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
		$rsSites = CSite::GetDefList();
		while($arSite = $rsSites->Fetch())
		{
			$last_site_id = $arSite["ID"];
			if($arSite["DEF"]=="Y")
				$site_id = $arSite["ID"];
			$arSites[] = $arSite;
		}
		if(is_array($arUserLang))
		{
			foreach($arUserLang as $user_lid)
			{
				$user_lid = strtolower(substr($user_lid, 0, 2));
				foreach($arSites as $arSite)
				{
					$sid = ($compare_site_id) ? strtolower($arSite["ID"]) : strtolower($arSite["LANGUAGE_ID"]);
					if($user_lid==$sid)
						return $arSite["ID"];
				}
			}
		}
		if(strlen($site_id)<=0)
			return $last_site_id;
		return $site_id;
	}

	// делает перенаправление на сайт
	function RedirectToSite($site)
	{
		if(strlen($site)<=0) return false;
		$db_site = CSite::GetByID($site);
		if($arSite = $db_site->Fetch())
		{
			$arSite["DIR"] = RTrim($arSite["DIR"], ' \/');
			if(strlen($arSite["DIR"])>0)
				LocalRedirect((strlen($arSite["SERVER_NAME"])>0?"http://".$arSite["SERVER_NAME"]:"").$arSite["DIR"].$_SERVER["REQUEST_URI"], true);
		}
	}

	// подключает страницу с папки другого сайта
	function GetIncludeSitePage($site)
	{
		if(strlen($site)<=0) return false;
		$db_site = CSite::GetByID($site);
		if($arSite = $db_site->Fetch())
		{
			$arSite["DIR"] = RTrim($arSite["DIR"], ' \/');
			$cur_page = GetPagePath();
			if(strlen($arSite["DIR"])>0)
			{
				global $REQUEST_URI;
				$REQUEST_URI = $arSite["DIR"].$cur_page;
				$_SERVER["REQUEST_URI"] = $REQUEST_URI;
				return $_SERVER["DOCUMENT_ROOT"].$REQUEST_URI;
			}
		}
		return false;
	}
}
?>