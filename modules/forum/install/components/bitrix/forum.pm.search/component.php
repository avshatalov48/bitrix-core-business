<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("PM_AUTH"));
	return 0;
elseif (intval(COption::GetOptionString("forum", "UsePMVersion", "2")) <= 0):
	ShowError(GetMessage("F_NO_PM"));
	CHTTP::SetStatus("404 Not Found");
	return 0;
endif;

if(!function_exists("GetUserName"))
{
	function GetUserName($USER_ID, $sNameTemplate = "")
	{
		$sNameTemplate = str_replace(array("#NOBR#","#/NOBR#"), "", (!empty($sNameTemplate) ? $sNameTemplate : CSite::GetDefaultNameFormat()));
		if (intval($USER_ID) <= 0)
		{
			$db_res = CUser::GetByLogin($USER_ID);
			$ar_res = $db_res->Fetch();
			$USER_ID = $ar_res["ID"];
		}
		return CForumUser::GetFormattedNameByUserID($USER_ID, $sNameTemplate);
	}
}

if(!function_exists("__UnEscape"))
{
	function __UnEscape(&$item, $key)
	{
		if(is_array($item))
			array_walk($item, '__UnEscape');
		else
		{
			if(strpos($item, "%u") !== false)
				$item = $GLOBALS["APPLICATION"]->UnJSEscape($item);
		}
	}
}

array_walk($_REQUEST, '__UnEscape');
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$UID = $arParams["UID"] = intVal($_REQUEST["UID"]);
	$mode = $_REQUEST["mode"];
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
		"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
		"pm_list" => "PAGE_NAME=pm_list&FID=#FID#",
		"pm_read" => "PAGE_NAME=pm_read&MID=#MID#",
		"pm_edit" => "PAGE_NAME=pm_edit&MID=#MID#",
		"pm_search" => "PAGE_NAME=pm_search");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "FID", "TID", "UID", BX_AJAX_PARAM_ID));
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
// ************************* ADDITIONAL ****************************************************************
	$arParams["NAME_TEMPLATE"] = str_replace(array("#NOBR#","#/NOBR#"), "",
		(!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : CSite::GetDefaultNameFormat()));
	$arParams["PM_USER_PAGE"] = intVal($arParams["PM_USER_PAGE"] > 0 ? $arParams["PM_USER_PAGE"] : 10);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
// *************************/Input params***************************************************************

		$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PM_SEARCH"], array());
// *****************************************************************************************
	$arResult["sessid"] = bitrix_sessid_post();
	$arResult["SITE_CHARSET"] = SITE_CHARSET;
// *****************************************************************************************
	$arResult["~search_template"] = trim($_REQUEST["search_template"]);
	if (!empty($arResult["~search_template"]))
		$arResult["~search_template"] = preg_replace("/[%]+/", "%", "%".str_replace("*", "%", $arResult["~search_template"])."%");
	$arResult["search_template"] = htmlspecialcharsbx($_REQUEST["search_template"]);
// *****************************************************************************************
	$arResult["SHOW_SEARCH_RESULT"] = "N";
	$arResult["SEARCH_RESULT"] = array();
	if (!empty($arResult["~search_template"]) && $arResult["~search_template"] != "%")
	{
		$arResult["SHOW_SEARCH_RESULT"] = "Y";
		$reqSearch = CForumUser::SearchUser(
			$arResult["~search_template"],
			array(
				"bDescPageNumbering" => false,
				"bShowAll" => false,
				"nPageSize" => $arParams["PM_USER_PAGE"],
				"sNameTemplate" => $arParams["NAME_TEMPLATE"]));

		$reqSearch->NavStart($arParams["PM_USER_PAGE"], false);
		$arResult["NAV_RESULT"] = $reqSearch;
		$arResult["NAV_STRING"] = $reqSearch->GetPageNavStringEx($navComponentObject, GetMessage("PM_SEARCH_RESULT"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
		
		if ($reqSearch && ($res = $reqSearch->GetNext()))
		{
			do 
			{
				$arResult["SEARCH_RESULT"][] = array_merge(
					array(
						"link" => ForumAddPageParams(
							$arResult["CURRENT_PAGE"], 
							array("search_insert" => "Y", "UID" => intVal($res["ID"]), "sessid" => bitrix_sessid()))), 
					$res);
			}
			while ($res = $reqSearch->GetNext());
		}
	}
	$arResult["SHOW_SELF_CLOSE"] = "N";

	if (($_REQUEST["search_insert"] == "Y" && intval($UID) > 0) || !empty($_REQUEST["search_by_login"]))
	{

		if (empty($_REQUEST["search_by_login"]))
		{
			$db_res = CForumUser::GetList(
				array(),
				array("USER_ID" => $UID, "SHOW_ABC" => ""),
				array("sNameTemplate" => $arParams["NAME_TEMPLATE"])
			);
			if ($db_res && ($res = $db_res->GetNext()))
			{
				$arResult["SHOW_SELF_CLOSE"] = "Y";
				$arResult["UID"] = $UID;
				$arResult["SHOW_NAME"] = $res["SHOW_ABC"];
				$arResult["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $UID));
			}
		}
		else
		{
			$arResult["SHOW_SELF_CLOSE"] = "Y";
			$arResult["SHOW_MODE"] = "none";

			$db_res = CForumUser::GetList(
				array("ID" => "DESC"),
				array("SHOW_ABC" => str_replace(array("*", "%"), "", $_REQUEST["search_by_login"])),
				array("sNameTemplate" => $arParams["NAME_TEMPLATE"])
			);
			if ($db_res && ($res = $db_res->getNext()))
			{
				$arResult["SHOW_MODE"] = "full";
				$arResult["SHOW_NAME"] = $res["SHOW_ABC"];
				$arResult["profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $res["USER_ID"]));
				$arResult["UID"] = $res["USER_ID"];
			}
			else
			{
				$db_res = CUser::GetByLogin($_REQUEST["search_by_login"]);
				if ($db_res && ($res = $db_res->GetNext()))
				{
					$arResult["SHOW_MODE"] = "light";
					$arResult["SHOW_NAME"] = GetUserName($res["ID"], $arParams["NAME_TEMPLATE"]);
					$arResult["UID"] = $res["ID"];
				}
			}
		}
//		$arResult["SHOW_NAME"] = htmlspecialcharsback($arResult["SHOW_NAME"]);
	}
// *****************************************************************************************

$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");
	$this->IncludeComponentTemplate();
die();
// *****************************************************************************************
?>