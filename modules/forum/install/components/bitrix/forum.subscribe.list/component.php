<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("FSL_NO_MODULE"));
	return 0;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("FSL_AUTH"));
	return 0;
endif;

	$strErrorMessage = "";
	$strOKMessage = "";
	$bVarsFromForm = false;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["UID"] = intval($_REQUEST["UID"]);
	$arParams["UID"] = intval((!CForumUser::IsAdmin() || $arParams["UID"] <= 0) ? $USER->GetID() : $arParams["UID"]);
$arParams["ACTION"] = mb_strtoupper($_REQUEST["ACTION"]);
/***************** URL *********************************************/
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	$URL_NAME_DEFAULT = array(
			"list" => "PAGE_NAME=list&FID=#FID#",
			"read" => "PAGE_NAME=read&FID=#FID#&TID=#TID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#",
			"subscr_list" => "PAGE_NAME=subscr_list",
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
			$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	// Data and data-time format
	$arParams["TOPICS_PER_PAGE"] = intval($arParams["TOPICS_PER_PAGE"] > 0 ? $arParams["TOPICS_PER_PAGE"] : COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"));
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : false);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["PAGE_NAVIGATION_WINDOW"] = intval(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 11);
/***************** STANDART ****************************************/
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
/********************************************************************
				/Input params
********************************************************************/
$arResult["USER"] = array();
$db_res = CForumUser::GetList(
	array(),
	array("USER_ID" => $arParams["UID"], "SHOW_ABC" => ""),
	array("sNameTemplate" => $arParams["NAME_TEMPLATE"])
);
if ($db_res && $res = $db_res->GetNext())
	$arResult["USER"] = $res;
if (empty($arResult["USER"])):
	ShowError(str_replace("#UID#", $arParams["UID"], GetMessage("FSL_NO_DUSER")));
	return false;
endif;
/********************************************************************
				Action
********************************************************************/
$arError = array(); $arNote = array();
if ($arParams["ACTION"] == "DEL")
{
	$arParams["SID"] = (is_array($_REQUEST["SID"]) ? $_REQUEST["SID"] : array($_REQUEST["SID"]));
	if (!check_bitrix_sessid()):
		$arError[] = GetMessage("F_ERR_SESS_FINISH");
	elseif (empty($arParams["SID"])):
		$arError[] = GetMessage("F_EMPTY_SUBSCRIBES");
	else:
		foreach ($arParams["SID"] as $res):
			if (!CForumSubscribe::CanUserDeleteSubscribe($res, $USER->GetUserGroupArray(), $USER->GetID())):
				$arError[] = str_replace("#SID#", $res, GetMessage("FSL_NO_SPERMS"));
			elseif(!CForumSubscribe::Delete($res)):
				$arError[] = str_replace("#SID#", $res, GetMessage("FSL_NO_DELETE"));
			else:
				$arNote[] = str_replace("#SID#", $res, GetMessage("FSL_SUCC_DELETE"));
			endif;
		endforeach;
	endif;
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
$arResult["CURRENT_PAGE"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_SUBSCR_LIST"], array());
$arResult["~FORUMS"] = $arResult["FORUMS"] = array();
$arResult["~TOPICS"] = $arResult["TOPICS"] = array();
$arResult["ERROR_MESSAGE"] = implode("\n", $arError);
$arResult["OK_MESSAGE"] =  implode("\n", $arNote);
$arResult["sessid"] = bitrix_sessid_get();
$arResult["SHOW_SUBSCRIBE_LIST"] = "N";
$arResult["SUBSCRIBE_LIST"] = array();
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$db_res = CForumSubscribe::GetListEx(
	array("FORUM_ID" => "ASC", "TOPIC_ID" => "ASC", "START_DATE" => "ASC"),
	array("USER_ID" => $arParams["UID"]),
	array(
		"bDescPageNumbering" => false,
		"nPageSize" => $arParams["TOPICS_PER_PAGE"]
	)
);
$db_res->NavStart($arParams["TOPICS_PER_PAGE"]);
$db_res->nPageWindow = $arParams["PAGE_NAVIGATION_WINDOW"];
$db_res->bShowAll = false;
$arResult["NAV_RESULT"] = $db_res;
$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("F_SUBSCRIBE"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
if ($db_res && $res = $db_res->GetNext())
{
	$arResult["SHOW_SUBSCRIBE_LIST"] = "Y";
	do
	{
		$arResult["~FORUMS"][] = $res["FORUM_ID"];
		$arResult["~TOPICS"][] = $res["TOPIC_ID"];

		$res["START_DATE"] = trim($res["START_DATE"]);
		if ($res["START_DATE"] <> '')
			$res["START_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["START_DATE"], CSite::GetDateFormat()));;
			
		$res["SUBSCRIBE_TYPE"] = (intval($res["TOPIC_ID"]) > 0 ? "TOPIC" : ($res["NEW_TOPIC_ONLY"] == "Y" ? "NEW_TOPIC_ONLY" : "ALL_MESSAGES"));
		$res["LAST_SEND"] = intval($res["LAST_SEND"]);
		
		$res["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_READ"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "TITLE_SEO" => $res["TITLE_SEO"], "MID" => "s"));
		$res["list"] =  CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], array("FID" => $res["FORUM_ID"]));
		$res["read_last_send"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
			array("FID" => $res["FORUM_ID"], "TID" => $res["TOPIC_ID"], "TITLE_SEO" => $res["TITLE_SEO"],  "MID" => intval($res["LAST_SEND"]))).
				"#message".intval($res["LAST_SEND"]);
		$res["subscr_delete"] = ForumAddPageParams($arResult["CURRENT_PAGE"], 
						array("SID" => $res["ID"], "ACTION" => "DEL"))."&amp;".bitrix_sessid_get();
		$res["URL"] = array(
			"TOPIC" => $res["read"], 
			"FORUM" => $res["list"], 
			"LAST_MESSAGE" => $res["read_last_send"], 
			"DELETE" => $res["subscr_delete"]);
		$arResult["SUBSCRIBE_LIST"][] = $res;
	}while ($res = $db_res->GetNext());
}

if (!empty($arResult["~FORUMS"]))
{
	$arResult["~FORUMS"] = array_unique($arResult["~FORUMS"]);
	$db_res = CForumNew::GetListEx(array("SORT" => "ASC"), array("@ID" => $arResult["~FORUMS"]));
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do {
			$arResult["FORUMS"][$res["ID"]] = $res;
		} while ($res = $db_res->GetNext());
	}
}
if (!empty($arResult["~TOPICS"]))
{
	$arResult["~TOPICS"] = array_unique(array_diff($arResult["~TOPICS"], array(0, "")));
	$db_res = CForumTopic::GetListEx(array("SORT" => "ASC"), array("@ID" => $arResult["~TOPICS"]));
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do {
			$arResult["TOPICS"][$res["ID"]] = $res;
		} while ($res = $db_res->GetNext());
	}
}
foreach($arResult["SUBSCRIBE_LIST"] as $key => $res)
{
	$arResult["SUBSCRIBE_LIST"][$key]["FORUM_INFO"] = $arResult["FORUMS"][$res["FORUM_ID"]];
	$arResult["SUBSCRIBE_LIST"][$key]["TOPIC_INFO"] = $arResult["TOPICS"][$res["TOPIC_ID"]];
}
/********************************************************************
				/Data
********************************************************************/
/*******************************************************************/
$this->IncludeComponentTemplate();
/*******************************************************************/
if ($arParams["SET_NAVIGATION"] != "N"):
	$APPLICATION->AddChainItem($arResult["USER"]["SHOW_ABC"],
		CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $arParams["UID"])));
	$APPLICATION->AddChainItem(GetMessage("FSL_TITLE"));
endif;
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("FSL_TITLE"));
?>