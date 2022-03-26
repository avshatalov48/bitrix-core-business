<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// A < E < I < M < Q < U < Y
// A - NO ACCESS		E - READ			I - ANSWER
// M - NEW TOPIC		Q - MODERATE	U - EDIT			Y - FULL_ACCESS

if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return false;
elseif (!CModule::IncludeModule("socialnetwork")):
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return false;
endif;

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
/***************** BASE ********************************************/
	$arParams["FID"] = intval((intval($arParams["FID"]) <= 0 ? $_REQUEST["FID"] : $arParams["FID"]));
	$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
	$arParams["MODE"] = ($arParams["SOCNET_GROUP_ID"] > 0 ? "GROUP" : "USER");
	$arParams["USER_ID"] = intval(intval($arParams["USER_ID"]) > 0 ? $arParams["USER_ID"] : $USER->GetID());
	$arParams["SORT_BY"] = (empty($arParams["SORT_BY"]) ? "LAST_POST_DATE" : $arParams["SORT_BY"]);
$arParams["SORT_ORDER"] = mb_strtoupper($arParams["SORT_ORDER"] == "ASC"? "ASC" : "DESC");
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"topic" => "PAGE_NAME=topic&FID=#FID#&TID=#TID#", 
		"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#", 
		"user" => "PAGE_NAME=user&UID=#UID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
			$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPageParam($URL_VALUE,
				array("PAGE_NAME", "FID", "TID", "UID", "GID", "MID", "ACTION", "sessid", "SEF_APPLICATION_CUR_PAGE_URL", 
					"AJAX_TYPE", "AJAX_CALL", BX_AJAX_PARAM_ID, "result", "order"));
		$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
// ************************* ADDITIONAL ****************************************************************
	$arParams["TOPICS_COUNT"] = intval($arParams["TOPICS_COUNT"]) > 0 ? intval($arParams["TOPICS_COUNT"]) : 6;
	$arParams["DATE_FORMAT"] = trim(empty($arParams["DATE_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")) : $arParams["DATE_FORMAT"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")):$arParams["DATE_TIME_FORMAT"]);
// *************************/Input params***************************************************************

//************** SocNet Activity ***********************************/
if (($arParams["MODE"] == "GROUP" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum")) ||
	($arParams["MODE"] != "GROUP" && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "forum"))):
	ShowError(GetMessage("FORUM_SONET_MODULE_NOT_AVAIBLE"));
	return false;
endif;

//************** Forum *********************************************/
$arResult["FORUM"] = CForumNew::GetByID($arParams["FID"]);
$arParams["PERMISSION"] = CForumNew::GetUserPermission($arParams["FID"], $USER->GetUserGroupArray());

if (!$arResult["FORUM"] || count($arResult["FORUM"]) <= 0):
	ShowError("Bad Forum");
	return false;
endif;

//************** Permission ****************************************/
if ($arParams["PERMISSION"] < "Y")
{
	$sPermission = $arParams["PERMISSION"];

	$arParams['PERMISSION'] = \Bitrix\Socialnetwork\Helper\Forum\ComponentHelper::getForumPermission([
		'ENTITY_TYPE' => ($arParams['MODE'] === 'GROUP' ? SONET_ENTITY_GROUP : SONET_ENTITY_USER),
		'ENTITY_ID' => ($arParams['MODE'] === 'GROUP' ? $arParams['SOCNET_GROUP_ID'] : $arParams['USER_ID']),
	]);

	if ("E" <= $sPermission && $arParams["PERMISSION"] < $sPermission)
	{
		$arParams["PERMISSION"] = $sPermission;
	}
}
if (!CForumNew::CanUserViewForum($arParams["FID"], $USER->GetUserGroupArray(), $arParams["PERMISSION"])):
	ShowError(GetMessage("FORUM_SONET_NO_ACCESS"));
	return false;
endif;
/********************************************************************
				Default params
********************************************************************/
$by = $arParams["SORT_BY"]; $order = $arParams["SORT_ORDER"];
$by = ($by == "LAST_POST_DATE" && $arParams["PERMISSION"] >= "Q" ? "ABS_LAST_POST_DATE" : "LAST_POST_DATE");
$arResult["Topics"] = array();
$arFilter = array(
	"FORUM_ID" => $arParams["FID"], 
	"APPROVED" => "Y", 
	"STATE" => "Y", 
	"SOCNET_GROUP_ID" => false);

if ($arParams["MODE"] == "GROUP")
	$arFilter["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
else
	$arFilter["OWNER_ID"] = $arParams["USER_ID"];

$bFirst = true;
$db_res = CForumTopic::GetListEx(array($by => $order), $arFilter,
	false, false, array("nTopCount"=>$arParams["TOPICS_COUNT"]));
while($arTopic = $db_res->GetNext())
{
	if($bFirst)
	{
		$arTopic["FIRST"] = "Y";
		$bFirst = false;
	}
	
	$arTopic["read"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_TOPIC"], 
		array("TID" => $arTopic["ID"], "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"]));

	$arTopic["read_last_message"] = CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
		array("TID" => $arTopic["ID"], "MID" => intval($arTopic["LAST_MESSAGE_ID"]), "UID" => $arParams["USER_ID"], "GID" => $arParams["SOCNET_GROUP_ID"]));
		
	if (intval($arTopic["LAST_POSTER_ID"]) > 0)
	{
		$arTopic["LAST_POSTER_HREF"] = CComponentEngine::MakePathFromTemplate(
			$arParams["URL_TEMPLATES_USER"], array("UID" => $arTopic["LAST_POSTER_ID"]));
		
	}
	// ********************************************************************
	$arTopic["numMessages"] = $arTopic["POSTS"] + 1;
	if (trim($arTopic["LAST_POST_DATE"]) <> '')
	{
		$arTopic["LAST_POST_DATE"] = CForumFormat::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arTopic["LAST_POST_DATE"], CSite::GetDateFormat()));
	}
	$arResult["Topics"][] = $arTopic;
}

$this->IncludeComponentTemplate();

?>