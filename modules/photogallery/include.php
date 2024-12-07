<?
##############################################
# Bitrix Site Manager IBlock                 #
# Copyright (c) 2002-2007 Bitrix             #
# https://www.bitrixsoft.com                 #
# mailto:admin@bitrixsoft.com                #
##############################################
CModule::AddAutoloadClasses(
	"photogallery",
	array(
		"CPGalleryInterface" => "tools/components_lib.php",
		"CPhotogalleryElement" => "classes/general/element.php",
		"CRatingsComponentsPhotogallery" => "classes/general/ratings_components.php",
		"CPhotogalleryNotifySchema" => "classes/general/photo_notify_schema.php",
	)
);
if (!isset($GLOBALS["PHOTOGALLERY_VARS"]) || !is_array($GLOBALS["PHOTOGALLERY_VARS"]))
{
	$GLOBALS["PHOTOGALLERY_VARS"] = array(
		"arSections" => array(),
		"arGalleries" => array(),
		"arIBlock" => array());
}

IncludeModuleLangFile(__FILE__);

// Spisok oshibok
// fatal'nye oshibki
// 100 - oshibka pri proverke sessii
// ne najdeny elementy
// 101 - ne najden infoblok
// 102 - ne najdena sekciya
// 103 - ne najden element infobloka
// 104 - ne najdena fotogalereya
// 105 - ne najden pol'zovatel'


// net prav
// 110 - dlya dostupa neobhodimo avtorizovat'sya
// 111 - net dostupa k infobloku
// 112 - net prav dlya sozdaniya galerei
// 113 - net prav dlya sozdaniya ewe odnoj galerei (single)


// Oshibki dannyh
// 200 - pustoj zapros
// 201 - ne ukazany obyazatel'nye parametry

// Pol'zovatel'skie oshibki (500-600)

function PhotoShowError($arError, $arShowFields = array("ID", "NAME"), $bShowErrorCode = false)
{
	$bShowErrorCode = ($bShowErrorCode === true ? true : false);
	$sReturn = "";
	$tmp = false;
	$arRes = array();
	if (empty($arError))
		return $sReturn;

	if (!is_array($arError))
	{
		$sReturn = $arError;
	}
	else
	{
		if (isset($arError["title"]))
			$sReturn = $arError["title"];

		if (isset($arError["code"]))
		{
			if (empty($sReturn))
			{
				$str = GetMessage("P_ERROR_".$arError["code"]);
				if (!empty($str))
					$sReturn = $str;
			}
			$sReturn .= ($bShowErrorCode ? " [CODE: ".$arError["code"]."]" : "");
		}

		if (isset($arError["DATA"]) || isset($arError["data"]))
		{
			$tmp = (isset($arError["DATA"]) ? $arError["DATA"] : $arError["data"]);

			if (!empty($arShowFields) && is_array($arShowFields))
			{
				if (in_array("ID", $arShowFields) && !empty($tmp["ID"]))
				{
					$arRes[] = "ID: ".$tmp["ID"];
					$tmp["ID"] = false;
				}

				foreach ($arShowFields as $key)
				{
					if (empty($tmp["~".$key]) && empty($tmp[$key]))
						continue;
					$arRes[] = $key.": ".(!empty($tmp["~".$key]) ? htmlspecialcharsbx($tmp["~".$key]) : $tmp[$key]);
				}
			}
			else
			{
				$arRes[] = $tmp;
			}
			if (!empty($arRes))
				$sReturn .= " (".implode(", ", $arRes).")";
		}
	}
	return $sReturn;
}

function PhotoGetBrowser()
{
	$Browser = "";
	$str = mb_strtolower($_SERVER['HTTP_USER_AGENT']);
	if (mb_strpos($str, "opera") !== false)
		$Browser = "opera";
	elseif (mb_strpos($str, "msie") !== false)
	{
		$Browser = "ie";
		if (mb_strpos($str, "win") !== false)
			$Browser = "win_ie";
	}
	return $Browser;
}

function PhotoDateFormat($format="", $timestamp="")
{
	if (empty($timestamp))
		return "";
	if(LANG=="en")
	{
		return date($format, $timestamp);
	}
	elseif(preg_match_all("/[FMlD]/", $format, $matches))
	{
		$ar = preg_split("/[FMlD]/", $format);
		$result = "";
		foreach($matches[0] as $i=>$match)
		{
			switch($match)
			{
				case "F":$match=GetMessage("P_MONTH_".date("n", $timestamp));break;
				case "M":$match=GetMessage("P_MON_".date("n", $timestamp));break;
				case "l":$match=GetMessage("P_DAY_OF_WEEK_".date("w", $timestamp));break;
				case "D":$match=GetMessage("P_DOW_".date("w", $timestamp));break;
			}
			$result .= date($ar[$i], $timestamp).$match;
		}
		$result .= date($ar[count($ar)-1], $timestamp);
		return $result;
	}
	else
		return date($format, $timestamp);
}

function PhotoFormatDate($strDate, $format="DD.MM.YYYY HH:MI:SS", $new_format="DD.MM.YYYY HH:MI:SS")
{
	global $DB;

	$strDate = trim($strDate);

	$new_format = str_replace("MI","I", $new_format);
	$new_format = preg_replace("/([DMYIHS])\\1+/isu", "\\1", $new_format);
	$new_format_len = mb_strlen($new_format);
	$arFormat = preg_split('/[^0-9A-Za-z]/', mb_strtoupper($format));
	$arDate = preg_split('/[^0-9]/', $strDate);
	$arParsedDate=Array();
	$bound = min(count($arFormat), count($arDate));

	for($i=0; $i<$bound; $i++)
	{
		if(preg_match("/[^0-9]/", $arDate[$i], $matches))
			$r = $DB->ForSql($arDate[$i], 4);
		else
			$r = intval($arDate[$i]);

		$arParsedDate[mb_substr($arFormat[$i], 0, 2)] = $r;
	}
	if (intval($arParsedDate["DD"])<=0 || intval($arParsedDate["MM"])<=0 || intval($arParsedDate["YY"])<=0)
		return false;

	$strResult = "";

	if(intval($arParsedDate["YY"])>1970 && intval($arParsedDate["YY"])<2038)
	{
		$ux_time = mktime(
				intval($arParsedDate["HH"]),
				intval($arParsedDate["MI"]),
				intval($arParsedDate["SS"]),
				intval($arParsedDate["MM"]),
				intval($arParsedDate["DD"]),
				intval($arParsedDate["YY"])
				);

		for ($i = 0; $i < $new_format_len; $i++)
		{
			switch(mb_substr($new_format, $i, 1))
			{
				case "F":
					$match = GetMessage("P_MONTH_".date("n", $ux_time));
					break;
				case "M":
					$match = GetMessage("P_MON_".date("n", $ux_time));
					break;
				case "l":
					$match = GetMessage("P_DAY_OF_WEEK_".date("w", $ux_time));
					break;
				case "D":
					$match = GetMessage("P_DOW_".date("w", $ux_time));
					break;
				default:
					$match = date(mb_substr($new_format, $i, 1), $ux_time);
					break;
			}
			$strResult .= $match;
		}
	}
	else
	{
		if($arParsedDate["MM"]<1 || $arParsedDate["MM"]>12)
			$arParsedDate["MM"] = 1;
		for ($i = 0; $i < $new_format_len; $i++)
		{
			switch(mb_substr($new_format, $i, 1))
			{
				case "F":
					$match = str_pad($arParsedDate["MM"], 2, "0", STR_PAD_LEFT);
					if(intval($arParsedDate["MM"]) > 0)
					{
						$match = GetMessage("P_MONTH_".intval($arParsedDate["MM"]));
					}
					break;
				case "M":
					$match = str_pad($arParsedDate["MM"], 2, "0", STR_PAD_LEFT);
					if(intval($arParsedDate["MM"]) > 0)
					{
						$match = GetMessage("P_MON_".intval($arParsedDate["MM"]));
					}
					break;
				case "l":
					$match = str_pad($arParsedDate["DD"], 2, "0", STR_PAD_LEFT);
					if(intval($arParsedDate["DD"]) > 0)
					{
						$match = GetMessage("P_DAY_OF_WEEK_".intval($arParsedDate["DD"]));
					}
					break;
				case "D":
					$match = str_pad($arParsedDate["DD"], 2, "0", STR_PAD_LEFT);
					if(intval($arParsedDate["DD"]) > 0)
					{
						$match = GetMessage("P_DOW_".intval($arParsedDate["DD"]));
					}
					break;
				case "d":
					$match = str_pad($arParsedDate["DD"], 2, "0", STR_PAD_LEFT);
					break;
				case "m":
					$match = str_pad($arParsedDate["MM"], 2, "0", STR_PAD_LEFT);
					break;
				case "j":
					$match = intval($arParsedDate["MM"]);
					break;
				case "Y":
					$match = str_pad($arParsedDate["YY"], 4, "0", STR_PAD_LEFT);
					break;
				case "y":
					$match = mb_substr($arParsedDate["YY"], 2);
					break;
				case "H":
					$match = str_pad($arParsedDate["HH"], 2, "0", STR_PAD_LEFT);
					break;
				case "i":
					$match = str_pad($arParsedDate["MI"], 2, "0", STR_PAD_LEFT);
					break;
				case "S":
					$match = str_pad($arParsedDate["SS"], 2, "0", STR_PAD_LEFT);
					break;
				case "g":
					$match = intval($arParsedDate["HH"]);
					if($match > 12)
					{
						$match = $match - 12;
					}
				case "a":
				case "A":
					$match = intval($arParsedDate["HH"]);
					if($match > 12)
					{
						$match = ($match - 12)." PM";
					}
					else
					{
						$match .= " AM";
					}

					if(mb_substr($new_format,$i,1) == "a")
					{
						$match = mb_strtolower($match);
					}

				default:
					$match = mb_substr($new_format,$i,1);
					break;
			}
			$strResult .= $match;
		}
	}
	return $strResult;
}

function PClearComponentCache($components, $arSite = array())
{
	if (empty($components))
		return false;

	if (
		!is_array($arSite)
		&& !empty($arSite)
	)
	{
		$arSite = array($arSite);
	}

	if (empty($arSite))
	{
		$arSite = array(SITE_ID);
	}

	if (is_array($components))
		$aComponents = $components;
	else
		$aComponents = explode(",", $components);

	foreach($aComponents as $component_name)
	{
		$add_path = "";
		if (mb_strpos($component_name, "/") !== false)
		{
			$add_path = mb_substr($component_name,mb_strpos($component_name, "/"));
			$component_name = mb_substr($component_name,0,mb_strpos($component_name, "/"));
		}
		$componentRelativePath = CComponentEngine::MakeComponentPath($component_name);

		if ($componentRelativePath <> '')
		{
			BXClearCache(true, "/".$componentRelativePath.$add_path);
			foreach ($arSite as $siteId)
			{
				BXClearCache(true, "/".$siteId.$componentRelativePath.$add_path);
			}
		}
	}
	BXClearCache(true, "/photogallery");
	foreach ($arSite as $siteId)
	{
		BXClearCache(true, "/".$siteId."/photogallery");
	}
}

function PClearComponentCacheEx($iblockId = false, $arSections = array(), $arGalleries = array(), $arUsers = array(), $clearCommon = true)
{
	if (!$iblockId)
		return;

	$arCache = array();
	$arCache[] = "photogallery";
	if ($clearCommon)
	{
		$arCache[] = "search.page";
		$arCache[] = "search.tags.cloud";
		$arCache[] = "photogallery/".$iblockId;
		$arCache[] = "photogallery/".$iblockId."/pemission";
		$arCache[] = "photogallery.detail.comment/".$iblockId;
		$arCache[] = "photogallery.gallery.list/".$iblockId;
	}

	if (is_array($arSections))
	{
		$arSections = array_unique($arSections);
		foreach($arSections as $sectionId)
			$arCache[] = "photogallery/".$iblockId."/section".intval($sectionId);
	}
	$arCache[] = "photogallery/".$iblockId."/section".intval($sectionId);

	if(is_array($arGalleries))
	{
		$arGalleries = array_unique($arGalleries);
		foreach($arGalleries as $galleryCode)
			$arCache[] = "photogallery/".$iblockId."/gallery".$galleryCode; // todo: secure galleryCode!!!!
	}

	if (is_array($arUsers))
	{
		$arUsers = array_unique($arUsers);
		foreach($arUsers as $userId)
			$arCache[] = "photogallery/".$iblockId."/user".intval($userId);
	}

	$arSite = array();

	$rsIblockSite = CIBlock::GetSite($iblockId);
	while($arIblockSite = $rsIblockSite->Fetch())
	{
		$arSite[] = $arIblockSite["SITE_ID"];
	}

	PClearComponentCache($arCache, $arSite);
}
?>