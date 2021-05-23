<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */
$this->setFrameMode(false);

if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
	$APPLICATION->SetTitle(GetMessage("CC_BLL_TITLE_TEXT_CLAIM"));
else
	$APPLICATION->SetTitle(GetMessage("CC_BLL_TITLE_TEXT_LISTS"));

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CC_BLL_MODULE_NOT_INSTALLED"));
	return;
}

$lists_perm = CListPermissions::CheckAccess(
	$USER,
	$arParams["~IBLOCK_TYPE_ID"],
	false,
	$arParams["~SOCNET_GROUP_ID"]
);
if($lists_perm < 0)
{
	switch($lists_perm)
	{
	case CListPermissions::WRONG_IBLOCK_TYPE:
		ShowError(GetMessage("CC_BLL_WRONG_IBLOCK_TYPE"));
		return;
	case CListPermissions::WRONG_IBLOCK:
		ShowError(GetMessage("CC_BLL_WRONG_IBLOCK"));
		return;
	case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
		ShowError(GetMessage("CC_BLL_LISTS_FOR_SONET_GROUP_DISABLED"));
		return;
	default:
		ShowError(GetMessage("CC_BLL_UNKNOWN_ERROR"));
		return;
	}
}
elseif($lists_perm <= CListPermissions::ACCESS_DENIED)
{
	ShowError(GetMessage("CC_BLL_ACCESS_DENIED"));
	return;
}

$arParams["CAN_EDIT"] = $lists_perm >= CListPermissions::IS_ADMIN;

if(isset($arParams["SOCNET_GROUP_ID"]) && $arParams["SOCNET_GROUP_ID"] > 0)
	$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
else
	$arParams["SOCNET_GROUP_ID"] = "";

$arResult["~LISTS_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array("0", $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LISTS_URL"]
);
$arResult["LISTS_URL"] = htmlspecialcharsbx($arResult["~LISTS_URL"]);

$arResult["~LIST_EDIT_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array("0", $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_EDIT_URL"]
);
$arResult["LIST_EDIT_URL"] = htmlspecialcharsbx($arResult["~LIST_EDIT_URL"]);

global $CACHE_MANAGER;
if($this->StartResultCache(0/*disable cache because it's individual for each user*/, $USER->GetUserGroupArray()))
{
	$CACHE_MANAGER->StartTagCache($this->GetCachePath());
	$CACHE_MANAGER->RegisterTag("lists_list_any");

	$arOrder = array(
		"SORT" => "ASC",
		"NAME" => "ASC",
	);
	$arFilter = array(
		"ACTIVE" => "Y",
		"TYPE" => $arParams["~IBLOCK_TYPE_ID"],
		"CHECK_PERMISSIONS" => ($lists_perm >= CListPermissions::IS_ADMIN || $arParams["SOCNET_GROUP_ID"]? "N": "Y"), //This cancels iblock permissions for trusted users
	);
	if($arParams["SOCNET_GROUP_ID"])
		$arFilter["=SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
	else
		$arFilter["SITE_ID"] = SITE_ID;

	$arResult["ITEMS"] = array();
	$rsLists = CIBlock::GetList($arOrder, $arFilter);
	while($ar = $rsLists->GetNext())
	{
		$ar["~LIST_URL"] = CHTTP::urlAddParams(str_replace(
			array("#list_id#", "#section_id#", "#group_id#"),
			array($ar["ID"], "0", $arParams["SOCNET_GROUP_ID"]),
			$arParams["~LIST_URL"]
		), array("list_section_id" => ""));
		$ar["LIST_URL"] = htmlspecialcharsbx($ar["~LIST_URL"]);

		$ar["~LIST_EDIT_URL"] = str_replace(
			array("#list_id#", "#group_id#"),
			array($ar["ID"], $arParams["SOCNET_GROUP_ID"]),
			$arParams["~LIST_EDIT_URL"]
		);
		$ar["LIST_EDIT_URL"] = htmlspecialcharsbx($ar["~LIST_EDIT_URL"]);

		$arResult["ITEMS"][] = $ar;
	}

	$CACHE_MANAGER->EndTagCache();
	$this->IncludeComponentTemplate();
}
