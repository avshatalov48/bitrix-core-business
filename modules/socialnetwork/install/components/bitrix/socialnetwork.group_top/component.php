<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

if (strLen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_SEARCH"] = trim($arParams["PATH_TO_GROUP_SEARCH"]);
if (strlen($arParams["PATH_TO_GROUP_SEARCH"]) <= 0)
	$arParams["PATH_TO_GROUP_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_search");

$arParams["ITEMS_COUNT"] = IntVal($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 10;

$arParams["DATE_TIME_FORMAT"] = Trim($arParams["DATE_TIME_FORMAT"]);
$arParams["DATE_TIME_FORMAT"] = ((StrLen($arParams["DATE_TIME_FORMAT"]) <= 0) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$arParams["DISPLAY_PICTURE"] = (($arParams["DISPLAY_PICTURE"] != "N") ? "Y" : "N");
$arParams["DISPLAY_IMAGE"] = $arParams["DISPLAY_PICTURE"];
$arParams["DISPLAY_DESCRIPTION"] = (($arParams["DISPLAY_DESCRIPTION"] != "N") ? "Y" : "N");
$arParams["DISPLAY_NUMBER_OF_MEMBERS"] = (($arParams["DISPLAY_NUMBER_OF_MEMBERS"] != "N") ? "Y" : "N");
$arParams["DISPLAY_SUBJECT"] = (($arParams["DISPLAY_SUBJECT"] != "N") ? "Y" : "N");

$arParams["FILTER_MY"] = (($arParams["FILTER_MY"] != "Y") ? "N" : "Y");

if ($arParams["FILTER_MY"] != "Y")
{
	if ($this->StartResultCache(false, false))
	{
		if (!CModule::IncludeModule("socialnetwork"))
		{
			$this->AbortResultCache();
			ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
			return;
		}

		$arResult["Urls"]["GroupSearch"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_SEARCH"], array());

		$dbGroups = CSocNetGroup::GetList(
			array("DATE_ACTIVITY" => "DESC", "NAME" => "ASC"),
			array("SITE_ID" => SITE_ID, "VISIBLE" => "Y", "ACTIVE" => "Y"),
			false,
			array("nTopCount" => $arParams["ITEMS_COUNT"]),
			array("ID", "NAME", "DESCRIPTION", "DATE_ACTIVITY", "IMAGE_ID", "NUMBER_OF_MEMBERS", "SUBJECT_NAME")
		);

		$arResult["Groups"] = array();
		while ($arGroup = $dbGroups->GetNext())
		{
			$arGroup["GROUP_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroup["ID"]));

			if (intval($arGroup["IMAGE_ID"]) <= 0)
				$arGroup["IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);

			$arImage = CSocNetTools::InitImage($arGroup["IMAGE_ID"], 50, "/bitrix/images/socialnetwork/nopic_group_50.gif", 50, $arGroup["GROUP_URL"], true);
			$arGroup["IMAGE_FILE"] = $arImage["FILE"];
			$arGroup["IMAGE_IMG"] = $arImage["IMG"];

			$arGroup["FULL_DATE_CHANGE_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arGroup["DATE_ACTIVITY"], CSite::GetDateFormat("FULL")));

			$arResult["Groups"][] = $arGroup;
		}

		$this->IncludeComponentTemplate();
	}
}
else
{
	if (!CModule::IncludeModule("socialnetwork"))
	{
		ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
		return;
	}

	$arResult["Urls"]["GroupSearch"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_SEARCH"], array());

	$dbGroups = CSocNetUserToGroup::GetList(
		array("GROUP_NAME" => "ASC"),
		array("USER_ID" => $GLOBALS["USER"]->GetID(), "<=ROLE" => SONET_ROLES_USER, "GROUP_SITE_ID" => SITE_ID, "GROUP_ACTIVE" => "Y"),
		false,
		array("nTopCount" => $arParams["ITEMS_COUNT"]),
		array("ID", "GROUP_ID", "GROUP_NAME", "GROUP_DESCRIPTION", "GROUP_IMAGE_ID", "GROUP_DATE_ACTIVITY")
	);

	$arResult["Groups"] = array();
	while ($arGroup = $dbGroups->GetNext())
	{
		$arGroup["GROUP_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroup["GROUP_ID"]));

		$arImage = CSocNetTools::InitImage($arGroup["GROUP_IMAGE_ID"], 50, "/bitrix/images/socialnetwork/nopic_group_50.gif", 50, $arGroup["GROUP_URL"], true);
		$arGroup["IMAGE_FILE"] = $arImage["FILE"];
		$arGroup["IMAGE_IMG"] = $arImage["IMG"];

		$arGroup["FULL_DATE_CHANGE_FORMATED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arGroup["GROUP_DATE_ACTIVITY"], CSite::GetDateFormat("FULL")));

		$arResult["Groups"][] = array(
			"GROUP_URL" => $arGroup["GROUP_URL"],
			"IMAGE_FILE" => $arGroup["IMAGE_FILE"],
			"IMAGE_IMG" => $arGroup["IMAGE_IMG"],
			"FULL_DATE_CHANGE_FORMATED" => $arGroup["FULL_DATE_CHANGE_FORMATED"],
			"ID" => $arGroup["GROUP_ID"],
			"NAME" => $arGroup["GROUP_NAME"],
			"DESCRIPTION" => $arGroup["GROUP_DESCRIPTION"],
			"IMAGE_ID" => $arGroup["GROUP_IMAGE_ID"],
			"DATE_ACTIVITY" => $arGroup["GROUP_DATE_ACTIVITY"],
		);
	}

	$this->IncludeComponentTemplate();
}
?>