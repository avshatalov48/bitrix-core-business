<?php

class CIBlockRSS extends CAllIBlockRSS
{
	public static function GetCache($cacheKey)
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$db_res = $DB->Query(
			"SELECT CACHE, case when CACHE_DATE > " . $helper->getCurrentDateTimeFunction() . " then 'Y' else 'N' end as VALID ".
			"FROM b_iblock_cache ".
			"WHERE CACHE_KEY = '".$DB->ForSql($cacheKey, 0)."' ");
		return $db_res->Fetch();
	}

	public static function Add($IBLOCK_ID, $NODE, $NODE_VALUE)
	{
		global $DB;
		$IBLOCK_ID = intval($IBLOCK_ID);
		$DB->Query(
			"INSERT INTO b_iblock_rss (IBLOCK_ID, NODE, NODE_VALUE) ".
			"VALUES(".$IBLOCK_ID.", '".$DB->ForSql($NODE, 50)."', '".$DB->ForSql($NODE_VALUE, 255)."')");
	}

	public static function UpdateCache($cacheKey, $CACHE, $HOURS_CACHE, $bCACHED)
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		if(is_array($HOURS_CACHE) && array_key_exists("minutes", $HOURS_CACHE))
			$TTL = intval($HOURS_CACHE["minutes"]) * 60;
		else
			$TTL = intval($HOURS_CACHE) * 3600;

		if ($bCACHED)
		{
			$db_res = $DB->Query(
				"UPDATE b_iblock_cache SET ".
				"	CACHE = '".$DB->ForSql($CACHE, 0)."', ".
				"	CACHE_DATE = " . $helper->addSecondsToDateTime(intval($TTL)) .
				" WHERE CACHE_KEY = '".$DB->ForSql($cacheKey, 0)."' ");
		}
		else
		{
			$db_res = $DB->Query(
				"INSERT INTO b_iblock_cache (CACHE_KEY, CACHE, CACHE_DATE) ".
				"VALUES('".$DB->ForSql($cacheKey, 0)."', '".$DB->ForSql($CACHE, 0)."', " . $helper->addSecondsToDateTime(intval($TTL)) . ") ");
		}
		$db_res = $DB->Query("DELETE from b_iblock_cache WHERE CACHE_DATE < " . $helper->getCurrentDateTimeFunction());
	}

	public static function GetRSSText($arIBLOCK, $LIMIT_NUM = false, $LIMIT_DAY = false, $yandex = false)
	{
		global $DB;

		$strRes = "";

		$protocol = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? 'https://' : 'http://';

		$serverName = "";

		if (isset($arIBLOCK["SERVER_NAME"]) && $arIBLOCK["SERVER_NAME"] <> '')
			$serverName = $arIBLOCK["SERVER_NAME"];

		if ($serverName == '' && !isset($arIBLOCK["SERVER_NAME"]))
		{
			$dbSite = CSite::GetList('', '', array("LID" => $arIBLOCK["LID"]));
			if ($arSite = $dbSite->Fetch())
				$serverName = $arSite["SERVER_NAME"];
		}

		if ($serverName == '')
		{
			if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
				$serverName = SITE_SERVER_NAME;
			else
				$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
		}

		$strRes .= "<channel>\n";
		$strRes .= "<title>".htmlspecialcharsbx($arIBLOCK["NAME"])."</title>\n";
		$strRes .= "<link>".$protocol.htmlspecialcharsbx($serverName)."</link>\n";
		$strRes .= "<description>".htmlspecialcharsbx($arIBLOCK["DESCRIPTION"])."</description>\n";
		$strRes .= "<lastBuildDate>".date("r")."</lastBuildDate>\n";
		$strRes .= "<ttl>".$arIBLOCK["RSS_TTL"]."</ttl>\n";

		$db_img_arr = CFile::GetFileArray($arIBLOCK["PICTURE"]);
		if ($db_img_arr)
		{
			if(mb_substr($db_img_arr["SRC"], 0, 1) == "/")
				$strImage = $protocol.$serverName.$db_img_arr["SRC"];
			else
				$strImage = $db_img_arr["SRC"];

			if ($yandex)
			{
				$strRes .= "<yandex:logo>".htmlspecialcharsbx($strImage)."</yandex:logo>\n";
				$squareSize = min($db_img_arr["WIDTH"], $db_img_arr["HEIGHT"]);
				if ($squareSize > 0)
				{
					$squarePicture = CFile::ResizeImageGet(
						$db_img_arr,
						array("width" => $squareSize, "height" => $squareSize),
						BX_RESIZE_IMAGE_EXACT
					);
					if ($squarePicture)
					{
						if(mb_substr($squarePicture["src"], 0, 1) == "/")
							$squareImage = $protocol.$serverName.$squarePicture["src"];
						else
							$squareImage = $squarePicture["src"];
						$strRes .= "<yandex:logo type=\"square\">".htmlspecialcharsbx($squareImage)."</yandex:logo>\n";
					}
				}
			}
			else
			{
				$strRes .= "<image>\n";
				$strRes .= "<title>".htmlspecialcharsbx($arIBLOCK["NAME"])."</title>\n";
				$strRes .= "<url>".htmlspecialcharsbx($strImage)."</url>\n";
				$strRes .= "<link>".$protocol.htmlspecialcharsbx($serverName)."</link>\n";
				$strRes .= "<width>".$db_img_arr["WIDTH"]."</width>\n";
				$strRes .= "<height>".$db_img_arr["HEIGHT"]."</height>\n";
				$strRes .= "</image>\n";
			}
		}

		$arNodes = array();
		$db_res = $DB->Query("SELECT NODE, NODE_VALUE FROM b_iblock_rss WHERE IBLOCK_ID = ".intval($arIBLOCK["ID"]));
		while ($db_res_arr = $db_res->Fetch())
		{
			$arNodes[$db_res_arr["NODE"]] = $db_res_arr["NODE_VALUE"];
		}

		$formatActiveDates = CPageOption::GetOptionString("iblock", "FORMAT_ACTIVE_DATES", "-");
		CPageOption::SetOptionString("iblock", "FORMAT_ACTIVE_DATES", "FULL");

		$nav = $LIMIT_NUM > 0? array("nTopCount" => $LIMIT_NUM): false;

		$arFilter = array(
			"IBLOCK_ID" => $arIBLOCK["ID"],
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
		);
		if ($LIMIT_DAY !== false)
		{
			$date = new \Bitrix\Main\Type\DateTime();
			$date->add("- $LIMIT_DAY days");
			$arFilter["ACTIVE_FROM"] = $date->toString();
		}

		CTimeZone::Disable();
		$items = CIBlockElement::GetList(array("ACTIVE_FROM"=>"DESC", "SORT"=>"ASC", "ID"=>"DESC"), $arFilter, false, $nav);
		CTimeZone::Enable();

		CPageOption::SetOptionString("iblock", "FORMAT_ACTIVE_DATES", $formatActiveDates);

		while ($arItem = $items->GetNext())
		{
			$props = CIBlockElement::GetProperty($arIBLOCK["ID"], $arItem["ID"], "sort", "asc", Array("ACTIVE"=>"Y", "NON_EMPTY"=>"Y"));
			$arProps = Array();
			while ($arProp = $props->Fetch())
			{
				if ($arProp["CODE"] <> '')
					$arProps[$arProp["CODE"]] = Array("NAME"=>htmlspecialcharsbx($arProp["NAME"]), "VALUE"=>htmlspecialcharsex($arProp["VALUE"]));
				else
					$arProps[$arProp["ID"]] = Array("NAME"=>htmlspecialcharsbx($arProp["NAME"]), "VALUE"=>htmlspecialcharsex($arProp["VALUE"]));
			}

			$arLinkProp = $arProps["DOC_LINK"];

			$strRes .= "<item>\n";
			if ($arNodes["title"] <> '')
			{
				$strRes .= "<title>".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["title"], $arProps, $arItem))."</title>\n";
			}
			else
			{
				$strRes .= "<title>".htmlspecialcharsbx($arItem["~NAME"])."</title>\n";
			}
			if ($arNodes["link"] <> '')
			{
				$strRes .= "<link>".CIBlockRSS::ExtractProperties($arNodes["link"], $arProps, $arItem)."</link>\n";
			}
			else
			{
				$strRes .= "<link>".$protocol.htmlspecialcharsbx($serverName).(($arLinkProp["VALUE"]) ? $arLinkProp["VALUE"] : $arItem["DETAIL_PAGE_URL"])."</link>\n";
			}
			if ($arNodes["description"] <> '')
			{
				$strRes .= "<description>".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["description"], $arProps, $arItem))."</description>\n";
			}
			else
			{
				$strRes .= "<description>".(($arItem["PREVIEW_TEXT"] || $yandex) ? htmlspecialcharsbx($arItem["PREVIEW_TEXT"]) : htmlspecialcharsbx($arItem["DETAIL_TEXT"]))."</description>\n";
			}
			if ($arNodes["enclosure"] <> '')
			{
				$strRes .= "<enclosure url=\"".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["enclosure"], $arProps, $arItem))."\" length=\"".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["enclosure_length"], $arProps, $arItem))."\" type=\"".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["enclosure_type"], $arProps, $arItem))."\"/>\n";
			}
			else
			{
				$db_img_arr = CFile::GetFileArray($arItem["PREVIEW_PICTURE"]);
				if ($db_img_arr)
				{
					if(mb_substr($db_img_arr["SRC"], 0, 1) == "/")
						$strImage = $protocol.$serverName.$db_img_arr["SRC"];
					else
						$strImage = $db_img_arr["SRC"];

					$strRes .= "<enclosure url=\"".htmlspecialcharsbx($strImage)."\" length=\"".$db_img_arr["FILE_SIZE"]."\" type=\"".$db_img_arr["CONTENT_TYPE"]."\" width=\"".$db_img_arr["WIDTH"]."\" height=\"".$db_img_arr["HEIGHT"]."\"/>\n";
				}
			}
			if ($arNodes["category"] <> '')
			{
				$strRes .= "<category>".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["category"], $arProps, $arItem))."</category>\n";
			}
			else
			{
				$strPath = "";
				$nav = CIBlockSection::GetNavChain(
					$arIBLOCK["ID"],
					$arItem["IBLOCK_SECTION_ID"],
					[
						'ID',
						'NAME',
					],
					true
				);
				foreach ($nav as $ar_nav)
				{
					$strPath .= $ar_nav["NAME"]."/";
				}
				unset($ar_nav, $nav);
				if ($strPath !== '')
				{
					$strRes .= "<category>".htmlspecialcharsbx($strPath)."</category>\n";
				}
			}
			if ($yandex)
			{
				$strRes .= "<yandex:full-text>".htmlspecialcharsbx($arItem["DETAIL_TEXT"])."</yandex:full-text>\n";
			}
			if ($arNodes["pubDate"] <> '')
			{
				$strRes .= "<pubDate>".htmlspecialcharsbx(CIBlockRSS::ExtractProperties($arNodes["pubDate"], $arProps, $arItem))."</pubDate>\n";
			}
			else
			{
				if ($arItem["ACTIVE_FROM"] <> '')
				{
					$strRes .= "<pubDate>".date("r", MkDateTime($DB->FormatDate($arItem["ACTIVE_FROM"], Clang::GetDateFormat("FULL"), "DD.MM.YYYY H:I:S"), "d.m.Y H:i:s"))."</pubDate>\n";
				}
				else
				{
					$strRes .= "<pubDate>".date("r")."</pubDate>\n";
				}
			}
			$strRes .= "</item>\n";
		}
		$strRes .= "</channel>\n";
		return $strRes;
	}

	// Agent
	public static function PreGenerateRSS($IBLOCK_ID, $yandex = true)
	{
		global $DB;

		$protocol = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? 'https://' : 'http://';

		$strSql =
			"SELECT DISTINCT B.*, C.CHARSET, S.SERVER_NAME, ".$DB->DateToCharFunction("B.TIMESTAMP_X")." as TIMESTAMP_X ".
			"FROM b_iblock B LEFT JOIN b_iblock_group IBG ON IBG.IBLOCK_ID=B.ID ".
			"	LEFT JOIN b_lang S ON S.LID=B.LID ".
			"	LEFT JOIN b_culture C ON C.ID=S.CULTURE_ID ".
			"WHERE B.ID = ".intval($IBLOCK_ID).
			"	AND IBG.GROUP_ID IN (2) ".
			"	AND IBG.PERMISSION>='R'".
			"	AND (IBG.PERMISSION='X' OR B.ACTIVE='Y')";
		$dbr = $DB->Query($strSql);
		$bAccessable = False;
		if (($arIBlock = $dbr->GetNext()) && ($arIBlock["RSS_FILE_ACTIVE"]=="Y" && !$yandex || $arIBlock["RSS_YANDEX_ACTIVE"]=="Y" && $yandex))
			$bAccessable = True;

		if (!$bAccessable) return "";

		$strRes = "";
		$strRes .= "<"."?xml version=\"1.0\" encoding=\"".$arIBlock["CHARSET"]."\"?".">\n";
		$strRes .= "<rss version=\"2.0\"";
//		$strRes .= "<rss version=\"2.0\" xmlns=\"http://backend.userland.com/rss2\"";
		if ($yandex)
		{
			$strRes .= ' xmlns:yandex="'.$protocol.'news.yandex.ru"';
		}
		$strRes .= ">\n";

		$limit_num = false;
		$limit_day = 2;
		if (!$yandex)
		{
			$limit_num = false;
			if ($arIBlock["RSS_FILE_LIMIT"] <> '' && intval($arIBlock["RSS_FILE_LIMIT"])>0)
				$limit_num = intval($arIBlock["RSS_FILE_LIMIT"]);

			$limit_day = false;
			if ($arIBlock["RSS_FILE_DAYS"] <> '' && intval($arIBlock["RSS_FILE_DAYS"])>0)
				$limit_day = intval($arIBlock["RSS_FILE_DAYS"]);
		}
		$strRes .= CIBlockRSS::GetRSSText($arIBlock, $limit_num, $limit_day, $yandex);

		$strRes .= "</rss>\n";

		$rss_file = $_SERVER["DOCUMENT_ROOT"].COption::GetOptionString("iblock", "path2rss", "/upload/");
		if ($yandex)
			$rss_file .= "yandex_rss_".intval($arIBlock["ID"]).".xml";
		else
			$rss_file .= "iblock_rss_".intval($arIBlock["ID"]).".xml";
		$fp = fopen($rss_file, "w");
		fwrite($fp, $strRes);
		fclose($fp);

		global $pPERIOD;
		$pPERIOD = intval($arIBlock["RSS_TTL"])*60*60;
		return "CIBlockRSS::PreGenerateRSS(".$IBLOCK_ID.", ".($yandex?"true":"false").");";
	}
}
