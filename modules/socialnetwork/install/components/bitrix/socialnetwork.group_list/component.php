<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";
if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_SEARCH"] = trim($arParams["PATH_TO_GROUP_SEARCH"]);
if (strlen($arParams["PATH_TO_GROUP_SEARCH"]) <= 0)
	$arParams["PATH_TO_GROUP_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_search");

$arParams["PATH_TO_GROUP_CREATE"] = trim($arParams["PATH_TO_GROUP_CREATE"]);
if (strlen($arParams["PATH_TO_GROUP_CREATE"]) <= 0)
	$arParams["PATH_TO_GROUP_CREATE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_create&".$arParams["USER_VAR"]."=#user_id#");

$arParams["ITEMS_COUNT"] = IntVal($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 20;

$arParams["DATE_TIME_FORMAT"] = Trim($arParams["DATE_TIME_FORMAT"]);
$arParams["DATE_TIME_FORMAT"] = ((StrLen($arParams["DATE_TIME_FORMAT"]) <= 0) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$arParams["SUBJECT_ID"] = IntVal($arParams["SUBJECT_ID"]);

if ($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("SONET_C2411_PAGE_TITLE"));

if ($arParams["SET_NAV_CHAIN"] != "N")
	$APPLICATION->AddChainItem(GetMessage("SONET_C2411_PAGE_TITLE"));

$arResult["Urls"]["GroupSearch"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_SEARCH"], array());
$arResult["Urls"]["GroupCreate"] = "";
$arResult["ALLOW_CREATE_GROUP"] = false;
if ($GLOBALS["USER"]->IsAuthorized())
{
	$arResult["Urls"]["GroupCreate"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_CREATE"], array("user_id" => $GLOBALS["USER"]->GetID()));
	$arResult["ALLOW_CREATE_GROUP"] = (CSocNetUser::IsCurrentUserModuleAdmin() || $GLOBALS["APPLICATION"]->GetGroupRight("socialnetwork", false, "Y", "Y", array(SITE_ID, false)) >= "K");
}

$arResult["SEARCH_RESULT"] = Array();

$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false);
$arNavigation = CDBResult::GetNavParams($arNavParams);

$arFilterTmp = array("SITE_ID" => SITE_ID, "ACTIVE" => "Y");
if (!CSocNetUser::IsCurrentUserModuleAdmin())
	$arFilterTmp["CHECK_PERMISSIONS"] = $GLOBALS["USER"]->GetID();
if ($arParams["SUBJECT_ID"] > 0)
	$arFilterTmp["SUBJECT_ID"] = $arParams["SUBJECT_ID"];

$dbGroups = CSocNetGroup::GetList(
	array("NAME" => "ASC"),
	$arFilterTmp,
	false,
	$arNavParams,
	array("ID", "NAME", "DESCRIPTION", "DATE_ACTIVITY", "IMAGE_ID", "NUMBER_OF_MEMBERS", "SUBJECT_NAME", "CLOSED")
);

while ($arGroup = $dbGroups->GetNext())
{
	$arGroup["TITLE_FORMATED"] = $arGroup["NAME"];
	$arGroup["BODY_FORMATED"] = $arGroup["DESCRIPTION"];

	$arGroup["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroup["ID"]));

	if (intval($arGroup["IMAGE_ID"]) <= 0)
		$arGroup["IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);
				
	$arImage = CSocNetTools::InitImage($arGroup["IMAGE_ID"], 100, "/bitrix/images/socialnetwork/nopic_group_100.gif", 100, $arGroup["URL"], true);

	$arGroup["IMAGE_FILE"] = $arImage["FILE"];
	$arGroup["IMAGE_IMG"] = $arImage["IMG"];

	$arGroup["FULL_DATE_CHANGE_FORMATED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arGroup["DATE_ACTIVITY"], CSite::GetDateFormat("FULL")));
	
	$arGroup["ARCHIVE"] = $arGroup["CLOSED"];
	$arResult["SEARCH_RESULT"][] = $arGroup;
}

$arResult["NAV_STRING"] = $dbGroups->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C2411_NAV"), "", false);


$arResult["Subjects"] = array();
$dbSubjects = CSocNetGroupSubject::GetList(
	array("SORT"=>"ASC", "NAME" => "ASC"),
	array("SITE_ID" => SITE_ID),
	false,
	false,
	array("ID", "NAME")
);
while ($arSubject = $dbSubjects->Fetch())
	$arResult["Subjects"][$arSubject["ID"]] = $arSubject["NAME"];

$this->IncludeComponentTemplate();
?>