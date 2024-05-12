<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

CJSCore::Init(array("core"));
// Template params
/********************************************************************
				Input params
********************************************************************/
/***************** URL *********************************************/
	$res = $arResult;
	$URL_NAME_DEFAULT = array(
			"active" => "PAGE_NAME=active",
			"forums" => "PAGE_NAME=forums&GID=#GID#",
			"help" => "PAGE_NAME=help",
			"index" => "",
			"list" => "PAGE_NAME=list&FID=#FID#",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"rules" =>"PAGE_NAME=rules",
			"search" => "PAGE_NAME=search",
			"subscr_list" => "PAGE_NAME=subscr_list",
			"pm_folder" => "PAGE_NAME=pm_folder",
			"user_list" => "PAGE_NAME=user_list");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$keyUrlTemplate = 'URL_TEMPLATES_' . mb_strtoupper($URL);
		$keyUrlTemplateUmp = "~" . $keyUrlTemplate;
		if (!isset($res[$keyUrlTemplate]) || trim($res[$keyUrlTemplate]) == '')
		{
			$res[$keyUrlTemplate] = $GLOBALS["APPLICATION"]->GetCurPage() . "?" . $URL_VALUE;
		}
		$res[$keyUrlTemplateUmp] = $res[$keyUrlTemplate];
		$res[$keyUrlTemplate] = htmlspecialcharsbx($res[$keyUrlTemplateUmp]);
	}
	$res["URL"] = array(
		"ACTIVE" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_ACTIVE"], array()),
		"FORUMS" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_FORUMS"], array("GID" => "#GID#")),
		"FORUM" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_LIST"], array("FID" => "#FID#")),
		"INDEX" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_INDEX"], array()),
		"~INDEX" => CComponentEngine::MakePathFromTemplate($res["~URL_TEMPLATES_INDEX"], array()),
		"MESSAGES" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_PM_FOLDER"], array()),
		"PROFILE" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $GLOBALS["USER"]->GetID())),
		"RULES" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_RULES"], array()),
		"SEARCH" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_SEARCH"], array()),
		"SUBSCRIBES" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_SUBSCR_LIST"], array()),
		"TOPICS" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_LIST"], array("FID" => 0)),
		"USERS" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_USER_LIST"], array()));
	$arResult["URL_TEMPLATES"] = $res["URL"];
/***************** ADDITIONAL **************************************/
/********************************************************************
				/Input params
********************************************************************/
// Rapid Access $arParams["SHOW_FORUMS"] == "Y"
if (($_GET["rapid_access"] ?? 'N') == "Y"):
	$url = "";
	if (mb_strpos($_GET["FID"], "GID_") !== false):
		$iGid = intval(mb_substr($_GET["FID"], 4));
		if ($iGid > 0):
			$url = str_replace("#GID#", $iGid, $arResult["URL_TEMPLATES"]["FORUMS"]);
		endif;
	elseif (intval($_GET["FID"]) > 0):
		$url = str_replace("#FID#", intval($_GET["FID"]), $arResult["URL_TEMPLATES"]["FORUM"]);
	endif;
	$url = str_replace(array("rapid_access=Y", "&&"), "", (empty($url) ? $arResult["URL_TEMPLATES"]["INDEX"] : $url));

	LocalRedirect($url);
endif;



// Show Page
if ($this->__page !== "menu"):
	$sTempatePage = $this->__page;
	$sTempateFile = $this->__file;
	$this->__component->IncludeComponentTemplate("menu");
	$this->__page = $sTempatePage;
	$this->__file = $sTempateFile;

	if (isset($arParams["SEO_USER"]) && $arParams["SEO_USER"] == "TEXT" && mb_strtolower($this->__page) == "profile_view" &&
		$GLOBALS["USER"]->GetId() != $arResult["UID"] && $GLOBALS["APPLICATION"]->GetGroupRight("forum") < "W")
	{
		$APPLICATION->AuthForm("");
	}

	if ($arParams["SHOW_FORUM_USERS"] != "N" && in_array(mb_strtolower($this->__page), array("profile", "profile_view", "subscr_list", "user_post"))):
		$GLOBALS["APPLICATION"]->AddChainItem(GetMessage("F_USERS"), CComponentEngine::MakePathFromTemplate($res["~URL_TEMPLATES_USER_LIST"], array()));
	endif;
else:
	return true;
endif;
/********************************************************************
				Input params
********************************************************************/
$arThemes = array();
$sTemplateDirFull = preg_replace("'[\\\\/]+'", "/", dirname(realpath(__FILE__))."/");
$dir = $sTemplateDirFull."themes/";
if (is_dir($dir) && $directory = opendir($dir)):

	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[] = $file;
	}
	closedir($directory);
endif;
$sTemplateDir = $this->__component->__template->__folder;
$sTemplateDir = preg_replace("'[\\\\/]+'", "/", $sTemplateDir."/");

$arParams["SEO_USER"] = (isset($arParams["SEO_USER"]) && in_array($arParams["SEO_USER"], array("Y", "N", "TEXT")) ? $arParams["SEO_USER"] : "Y");
$arParams["SHOW_FORUM_USERS"] = (isset($arParams["SHOW_FORUM_USERS"]) && $arParams["SHOW_FORUM_USERS"] == "N" ? "N" : "Y");
$arParams["SHOW_AUTH_FORM"] = (isset($arParams["SHOW_AUTH_FORM"]) && $arParams["SHOW_AUTH_FORM"] == "N" ? "N" : "Y");
$arParams["SHOW_NAVIGATION"] = (isset($arParams["SHOW_NAVIGATION"]) && $arParams["SHOW_NAVIGATION"] == "N" ? "N" : "Y");
$arParams["SHOW_SUBSCRIBE_LINK"] = (isset($arParams["SHOW_SUBSCRIBE_LINK"]) && $arParams["SHOW_SUBSCRIBE_LINK"] == "Y" ? "Y" : "N");
$arParams["SHOW_LEGEND"] = (isset($arParams["SHOW_LEGEND"]) && $arParams["SHOW_LEGEND"] == "N" ? "N" : "Y");
$arParams["SHOW_STATISTIC"] = (isset($arParams["SHOW_STATISTIC"]) && $arParams["SHOW_STATISTIC"] ?? 'Y') == "N" ? "N" : "Y";
$arParams["SHOW_STATISTIC_BLOCK"] = (isset($arParams["SHOW_STATISTIC"]) && $arParams["SHOW_STATISTIC"] == "Y" ? array("STATISTIC", "BIRTHDAY", "USERS_ONLINE") : array());
$arParams["SHOW_NAME_LINK"] = "Y";
$arParams["SHOW_FORUMS"] = (isset($arParams["SHOW_FORUMS"]) && $arParams["SHOW_FORUMS"] ?? 'Y' == "N" ? "N" : "Y");
$arParams["SHOW_FIRST_POST"] = (isset($arParams["SHOW_FIRST_POST"]) && $arParams["SHOW_FIRST_POST"] == "Y" ? "Y" : "N");
$arParams["SHOW_AUTHOR_COLUMN"] = (isset($arParams["SHOW_AUTHOR_COLUMN"]) && $arParams["SHOW_AUTHOR_COLUMN"] == "Y" ? "Y" : "N");
$arParams["TMPLT_SHOW_ADDITIONAL_MARKER"] = isset($arParams["TMPLT_SHOW_ADDITIONAL_MARKER"]) ? trim($arParams["TMPLT_SHOW_ADDITIONAL_MARKER"]) : '';
if (!is_set($arParams, "SMILES_COUNT"))
	$arParams["SMILES_COUNT"] = 100;
$arParams["SMILES_COUNT"] = intval($arParams["SMILES_COUNT"]);

$arParams["WORD_LENGTH"] = isset($arParams["WORD_LENGTH"]) ? intval($arParams["WORD_LENGTH"]) : null;
$arParams["WORD_WRAP_CUT"] = isset($arParams["WORD_WRAP_CUT"]) ? intval($arParams["WORD_WRAP_CUT"]) : null;
$arParams["PATH_TO_SMILE"] = "";
$arParams["PATH_TO_ICON"] = "";
$arParams["PAGE_NAVIGATION_TEMPLATE"] = isset($arParams["PAGE_NAVIGATION_TEMPLATE"]) ? trim($arParams["PAGE_NAVIGATION_TEMPLATE"]) : null;
$arParams["PAGE_NAVIGATION_TEMPLATE"] = (empty($arParams["PAGE_NAVIGATION_TEMPLATE"]) ? "forum" : $arParams["PAGE_NAVIGATION_TEMPLATE"]);
$arParams["PAGE_NAVIGATION_WINDOW"] = intval(isset($arParams["PAGE_NAVIGATION_WINDOW"]) && intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 5);
$arParams["THEME"] = isset($arParams["THEME"]) ? trim($arParams["THEME"]) : null;
if (empty($arParams["THEME"])):
	$arParams["THEME"] = (in_array("blue", $arThemes) ? "blue" : $arThemes[0]);
elseif (!in_array($arParams["THEME"], $arThemes)):
	$val = str_replace(array("\\", "//"), "/", "/".$arParams["THEME"]."/");
	if (!(is_dir($_SERVER['DOCUMENT_ROOT'].$val) && is_file($_SERVER['DOCUMENT_ROOT'].$val."style.css"))):
		$arParams["THEME"] = (in_array("blue", $arThemes) ? "blue" : $arThemes[0]);
	else:
		$arParams["THEME"] = $val;
	endif;
endif;
/********************************************************************
				/Input params
********************************************************************/
if (in_array($arParams["THEME"], $arThemes)):
	$date = @filemtime($dir.$arParams["THEME"]."/style.css");
	$GLOBALS['APPLICATION']->SetAdditionalCSS($sTemplateDir.'themes/'.$arParams["THEME"].'/style.css?'.$date);
else:
	$date = @filemtime($_SERVER['DOCUMENT_ROOT'].$arParams["THEME"]."/style.css");
	$GLOBALS['APPLICATION']->SetAdditionalCSS($arParams["THEME"].'/style.css?'.$date);
endif;
$date = @filemtime($sTemplateDirFull."styles/additional.css");
$GLOBALS['APPLICATION']->SetAdditionalCSS($sTemplateDir.'styles/additional.css?'.$date);
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/main/utils.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/forum.interface/templates/.default/script.js");

$file = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/lang/".LANGUAGE_ID."/result_modifier.php")));
if (!file_exists($file))
	$file = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/lang/en/result_modifier.php")));
if(file_exists($file)):
	global $MESS;
	include_once($file);
endif;
$arResult["GROUPS"] = array();
if ($arParams["SHOW_FORUMS"] == "Y" && in_array($this->__page, array("forums", "list", "read"))):

	CModule::IncludeModule("forum");
	$arResult["GROUPS"] = CForumGroup::GetByLang(LANGUAGE_ID);
	if (!function_exists("__array_merge"))
	{
		function __array_merge($arr1, $arr2, $deep = false)
		{
			$arResult = $arr1;
			static $ii = 0;
			$ii++;
			$deep = ($deep == false ? 0 : $deep);
			foreach ($arr2 as $key2 => $val2)
			{
				if (!array_key_exists($key2, $arResult))
				{
					$arResult[$key2] = $val2;
					continue;
				}
				elseif ($val2 == $arResult[$key2])
					continue;
				elseif (!is_array($arResult[$key2]))
					$arResult[$key2] = array($arResult[$key2]);
				$deep++;
				$arResult[$key2] = __array_merge($arResult[$key2], $val2, $deep);
			}
			return $arResult;
		}
	}
	if (!function_exists("__array_stretch"))
	{
		function __array_stretch($arGroup, $depth = 0)
		{
			$arResult = array();

			if (isset($arGroup["ID"]) && intval($arGroup["ID"]) > 0)
			{
				$arResult["GROUP_".$arGroup["ID"]] = $arGroup;
				unset($arResult["GROUP_".$arGroup["ID"]]["GROUPS"]);
				unset($arResult["GROUP_".$arGroup["ID"]]["FORUM"]);
				$arResult["GROUP_".$arGroup["ID"]]["DEPTH"] = $depth;
				$arResult["GROUP_".$arGroup["ID"]]["TYPE"] = "GROUP";
			}
			if (array_key_exists("FORUMS", $arGroup))
			{
				foreach ($arGroup["FORUMS"] as $res)
				{
					$arResult["FORUM_".$res["ID"]] = $res;
					$arResult["FORUM_".$res["ID"]]["DEPTH"] = $depth;
					$arResult["FORUM_".$res["ID"]]["TYPE"] = "FORUM";
				}
			}

			if (array_key_exists("GROUPS", $arGroup))
			{
				$depth++;
				foreach ($arGroup["GROUPS"] as $key => $val)
				{
					$res = __array_stretch($arGroup["GROUPS"][$key], $depth);
					$arResult = array_merge($arResult, $res);
				}
			}
			return $arResult;
		}
	}
	$res = array();
	$cache = new CPHPCache();
	$cache_path_main = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$this->__component->__name."/");

	foreach ($arParams["FID"] as $key => $val)
	{
		if (intval($val) > 0)
			$res[] = $val;
	}
	$arParams["FID_RANGE"] = $res;


	$arFilter = array();
	$arForums = array();
	if ($arParams["SHOW_FORUM_ANOTHER_SITE"] == "N" || $GLOBALS["APPLICATION"]->GetGroupRight("forum") < "W")
		$arFilter["LID"] = SITE_ID;
	if (!empty($arParams["FID_RANGE"]))
		$arFilter["@ID"] = $arParams["FID_RANGE"];
	if ($GLOBALS["APPLICATION"]->GetGroupRight("forum") < "W"):
		$arFilter["PERMS"] = array($GLOBALS["USER"]->GetGroups(), 'A');
		$arFilter["ACTIVE"] = "Y";
	endif;
	$cache_id = "forum_forums_".serialize($arFilter);
	if(($tzOffset = CTimeZone::GetOffset()) <> 0)
		$cache_id .= "_".$tzOffset;
	$cache_path = $cache_path_main."forums";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		$arForums = CForumCacheManager::Expand($res["arForums"]);
	}
	$arForums = (is_array($arForums) ? $arForums : array());
	if (empty($arForums))
	{
		$db_res = CForumNew::GetListEx(array("FORUM_GROUP_SORT"=>"ASC", "FORUM_GROUP_ID"=>"ASC", "SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
		if ($db_res && ($res = $db_res->GetNext()))
		{
			do
			{
				$arForums[$res["ID"]] = $res;
			} while ($res = $db_res->GetNext());
		}
		if ($arParams["CACHE_TIME"] > 0):
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("arForums" => CForumCacheManager::Compress($arForums)));
		endif;
	}
	$arResult["FORUMS"] = $arForums;
	$arGroupsForums = array();
	$arGroups = array();
	foreach ($arResult["FORUMS"] as $key => $res)
	{
		$arGroupsForums[$res["FORUM_GROUP_ID"]][$key] = $res;
	}
	foreach ($arGroupsForums as $PARENT_ID => $res)
	{
		$bResult = true;
		$res = array("FORUMS" => $res);
		while ($PARENT_ID > 0)
		{
			if (!array_key_exists($PARENT_ID, $arResult["GROUPS"]))
			{
				$bResult = false;
				$PARENT_ID = false;
				break;
			}
			$res = array($PARENT_ID => __array_merge($arResult["GROUPS"][$PARENT_ID], $res));
			$PARENT_ID = $arResult["GROUPS"][$PARENT_ID]["PARENT_ID"];
			$res = array("GROUPS" => $res);
			if ($PARENT_ID > 0)
				$res = __array_merge($arResult["GROUPS"][$PARENT_ID], $res);
		}
		if ($bResult == true)
			$arGroups = __array_merge($arGroups, $res);
	}
	$arResult["GROUPS_FORUMS"] = __array_stretch($arGroups);
endif;
?><script>
//<![CDATA[
	BX.message({
		F_LOAD : '<?=GetMessageJS("F_LOAD")?>',
		FORUMJS_TITLE : '<?=CUtil::JSEscape(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"]))?> - '
	});
//]]>
</script>
