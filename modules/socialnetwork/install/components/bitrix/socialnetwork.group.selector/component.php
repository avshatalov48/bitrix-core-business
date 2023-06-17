<?
if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
require_once("functions.php");

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
}

$arResult["SELECTED"] = array();
if ($groupId = intval($arParams["SELECTED"] ?? null))
{
	$arFilter = array("ID" => $groupId);
	if(!CSocNetUser::IsCurrentUserModuleAdmin())
		$arFilter["CHECK_PERMISSIONS"] = $GLOBALS["USER"]->GetID();

	$rsGroup = CSocNetGroup::GetList(Array(), $arFilter, false, false, array("ID", "SITE_ID", "NAME", "DESCRIPTION", "DATE_CREATE", "DATE_UPDATE", "ACTIVE", "VISIBLE", "OPENED", "CLOSED", "SUBJECT_ID", "OWNER_ID", "KEYWORDS", "IMAGE_ID", "NUMBER_OF_MEMBERS", "INITIATE_PERMS", "SPAM_PERMS", "DATE_ACTIVITY", "SUBJECT_NAME", "UF_*"));
	if ($arGroup = $rsGroup->Fetch())
	{
		$arResult["SELECTED"][] = group2JSItem($arGroup);
	}
}

CSocNetTools::InitGlobalExtranetArrays();

$arResult["LAST_GROUPS"] = array();
$arGroupsFilter = array("SITE_ID" => SITE_ID);
$arPopupOptions = CUserOptions::GetOption("socialnetwork", "groups_popup", array());
if (
	is_array($arPopupOptions)
	&& ($arPopupOptions["last_selected"] ?? '') <> ''
)
{
	$arFilter = array("SITE_ID" => SITE_ID, "ID" => array_unique(explode(',', $arPopupOptions["last_selected"])));
	if(!CSocNetUser::IsCurrentUserModuleAdmin())
	{
		$arFilter["CHECK_PERMISSIONS"] = $GLOBALS["USER"]->GetID();
	}

	$rsGroups = CSocNetGroup::GetList(array("NAME" => "ASC"), $arFilter);
	while($arGroup = $rsGroups->Fetch())
	{
		if (
			isset($GLOBALS["arExtranetGroupID"])
			&& is_array($GLOBALS["arExtranetGroupID"])
			&& in_array($arGroup["ID"], $GLOBALS["arExtranetGroupID"])
		)
		{
			$arGroup["IS_EXTRANET"] = "Y";
		}

		$arGroupTmp = group2JSItem($arGroup);
		if (!in_array($arGroupTmp, $arResult["SELECTED"]))
		{
			$arResult["LAST_GROUPS"][] = $arGroupTmp;
		}
	}
	$arResult["LAST_GROUPS"] = array_slice($arResult["LAST_GROUPS"], 0, 10);
}
$arResult["LAST_GROUPS"] = array_merge($arResult["SELECTED"], $arResult["LAST_GROUPS"]);

$arParams["GROUPS_PAGE_SIZE"] = 50;

$myGroupCache = new CPHPCache;
$cachePath = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName);
$cacheId = "socnet_user_groups_".SITE_ID.'_'.$arParams["GROUPS_PAGE_SIZE"]."_".$USER->GetID();
$cacheTime = 31536000;

if ($myGroupCache->InitCache($cacheTime, $cacheId, $cachePath))
{
	$vars = $myGroupCache->GetVars();
	$arResult["MY_GROUPS"] = $vars["arMyGroups"];
}
else
{
	if (defined("BX_COMP_MANAGED_CACHE"))
	{
		$GLOBALS["CACHE_MANAGER"]->StartTagCache($cachePath);
		$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_user2group_U".$USER->GetID());
		$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_group");
	}

	$arResult["MY_GROUPS"] = array();
	$rsGroups = CSocNetUserToGroup::GetList(
		array("GROUP_NAME" => "ASC"),
		array(
			"USER_ID" => $USER->GetID(),
			"<=ROLE" => SONET_ROLES_USER,
			"GROUP_SITE_ID" => SITE_ID,
			"GROUP_ACTIVE" => "Y"
		),
		false,
		array("nPageSize" => $arParams["GROUPS_PAGE_SIZE"], "bDescPageNumbering" => false),
		array("ID", "GROUP_ID", "GROUP_NAME", "GROUP_DESCRIPTION", "GROUP_IMAGE_ID")
	);
	while($arGroup = $rsGroups->Fetch())
	{
		if (
			isset($GLOBALS["arExtranetGroupID"])
			&& is_array($GLOBALS["arExtranetGroupID"])
			&& in_array($arGroup["GROUP_ID"], $GLOBALS["arExtranetGroupID"])
		)
		{
			$arGroup["GROUP_IS_EXTRANET"] = "Y";
		}

		$arResult["MY_GROUPS"][] = group2JSItem($arGroup, "GROUP_");
	}

	if (defined("BX_COMP_MANAGED_CACHE"))
		$GLOBALS["CACHE_MANAGER"]->EndTagCache();

	$myGroupCache->StartDataCache($cacheTime, $cacheId, $cachePath);
	$myGroupCache->EndDataCache(array("arMyGroups" => $arResult["MY_GROUPS"]));
}

if (isset($arParams["FEATURES_PERMS"]) && sizeof($arParams["FEATURES_PERMS"]) == 2)
{
	filterByFeaturePerms($arResult["LAST_GROUPS"], $arParams["FEATURES_PERMS"]);
	filterByFeaturePerms($arResult["SELECTED"], $arParams["FEATURES_PERMS"]);
	filterByFeaturePerms($arResult["MY_GROUPS"], $arParams["FEATURES_PERMS"]);
}

if (
	!isset($GLOBALS["arExtranetGroupID"])
	|| !isset($GLOBALS["arExtranetUserID"])
)
{
	CSocNetTools::InitGlobalExtranetArrays();
}

$this->IncludeComponentTemplate();
?>
