<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
global $CACHE_MANAGER, $USER_FIELD_MANAGER;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (!array_key_exists("MAX_ITEMS", $arParams) || intval($arParams["MAX_ITEMS"]) <= 0)
	$arParams["MAX_ITEMS"] = 6;

$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_MODS"] = trim($arParams["PATH_TO_GROUP_MODS"]);
if (strlen($arParams["PATH_TO_GROUP_MODS"]) <= 0)
	$arParams["PATH_TO_GROUP_MODS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_mods&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_USERS"] = trim($arParams["PATH_TO_GROUP_USERS"]);
if (strlen($arParams["PATH_TO_GROUP_USERS"]) <= 0)
	$arParams["PATH_TO_GROUP_USERS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_users&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_EDIT"] = trim($arParams["PATH_TO_GROUP_EDIT"]);
if (strlen($arParams["PATH_TO_GROUP_EDIT"]) <= 0)
	$arParams["PATH_TO_GROUP_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_edit&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_REQUEST_SEARCH"] = trim($arParams["PATH_TO_GROUP_REQUEST_SEARCH"]);
if (strlen($arParams["PATH_TO_GROUP_REQUEST_SEARCH"]) <= 0)
	$arParams["PATH_TO_GROUP_REQUEST_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_request_search&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_REQUESTS"] = trim($arParams["PATH_TO_GROUP_REQUESTS"]);
if (strlen($arParams["PATH_TO_GROUP_REQUESTS"]) <= 0)
	$arParams["PATH_TO_GROUP_REQUESTS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_requests&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_BAN"] = trim($arParams["PATH_TO_GROUP_BAN"]);
if (strlen($arParams["PATH_TO_GROUP_BAN"]) <= 0)
	$arParams["PATH_TO_GROUP_BAN"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_ban&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_BLOG"] = trim($arParams["PATH_TO_GROUP_BLOG"]);
if(strlen($arParams["PATH_TO_GROUP_BLOG"])<=0)
	$arParams["PATH_TO_GROUP_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_blog&".$arParams["GROUP_VAR"]."=#group_id#");
	
$arParams["PATH_TO_GROUP_MICROBLOG"] = trim($arParams["PATH_TO_GROUP_MICROBLOG"]);
if(strlen($arParams["PATH_TO_GROUP_MICROBLOG"])<=0)
	$arParams["PATH_TO_GROUP_MICROBLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_microblog&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_PHOTO"] = trim($arParams["PATH_TO_GROUP_PHOTO"]);
if(strlen($arParams["PATH_TO_GROUP_PHOTO"])<=0)
	$arParams["PATH_TO_GROUP_PHOTO"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_photo&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_FORUM"] = trim($arParams["PATH_TO_GROUP_FORUM"]);
if(strlen($arParams["PATH_TO_GROUP_FORUM"])<=0)
	$arParams["PATH_TO_GROUP_FORUM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_forum&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_CALENDAR"] = trim($arParams["PATH_TO_GROUP_CALENDAR"]);
if(strlen($arParams["PATH_TO_GROUP_CALENDAR"])<=0)
	$arParams["PATH_TO_GROUP_CALENDAR"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_calendar&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_TASKS"] = trim($arParams["PATH_TO_GROUP_TASKS"]);
if(strlen($arParams["PATH_TO_GROUP_TASKS"])<=0)
	$arParams["PATH_TO_GROUP_TASKS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_tasks&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_FILES"] = trim($arParams["PATH_TO_GROUP_FILES"]);
if(strlen($arParams["PATH_TO_GROUP_FILES"])<=0)
	$arParams["PATH_TO_GROUP_FILES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_files&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_CONTENT_SEARCH"] = trim($arParams["PATH_TO_GROUP_CONTENT_SEARCH"]);
if(strlen($arParams["PATH_TO_GROUP_CONTENT_SEARCH"])<=0)
	$arParams["PATH_TO_GROUP_CONTENT_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_content_search&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["USE_MAIN_MENU"] = (isset($arParams["USE_MAIN_MENU"]) ? $arParams["USE_MAIN_MENU"] : false);

if ($arParams["USE_MAIN_MENU"] == "Y" && !array_key_exists("MAIN_MENU_TYPE", $arParams))
	$arParams["MAIN_MENU_TYPE"] = "left";

$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);

if (
	$arGroup 
	&& is_array($arGroup) 
	&& $arGroup["ACTIVE"] == "Y"

)
{
	$arGroupSites = array();
	$rsGroupSite = CSocNetGroup::GetSite($arGroup["ID"]);
	while ($arGroupSite = $rsGroupSite->Fetch())
		$arGroupSites[] = $arGroupSite["LID"];

	if (in_array(SITE_ID, $arGroupSites))
	{
		$arResult["Group"] = $arGroup;

		if (CModule::IncludeModule("extranet") && !CExtranet::IsExtranetSite() && in_array(CExtranet::GetExtranetSiteID(), $arGroupSites))
			$arResult["Group"]["IS_EXTRANET"] = "Y";

		if ($arResult["Group"]["CLOSED"] == "Y" && COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y")
			$arResult["HideArchiveLinks"] = true;

		$arResult["CurrentUserPerms"] = CSocNetUserToGroup::InitUserPerms($USER->GetID(), $arResult["Group"], CSocNetUser::IsCurrentUserModuleAdmin());

		if (
			$arResult["CurrentUserPerms"] != false
			&& $arResult["CurrentUserPerms"]["UserCanViewGroup"]
		)
		{
			$arResult["bSubscribed"] = (
				in_array($arResult["CurrentUserPerms"]["UserRole"], \Bitrix\Socialnetwork\UserToGroupTable::getRolesMember())
					? CSocNetSubscription::IsUserSubscribed($USER->GetID(), "SG".$arParams["GROUP_ID"])
					: false
			);

			$arResult["Urls"]["Edit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["View"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["UserRequestGroup"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_REQUEST_GROUP"], array("group_id" => $arResult["Group"]["ID"], "user_id" => $USER->GetID()));
			$arResult["Urls"]["GroupRequestSearch"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_REQUEST_SEARCH"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupRequests"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_REQUESTS"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupMods"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_MODS"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupUsers"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_USERS"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupBan"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BAN"], array("group_id" => $arResult["Group"]["ID"]));

			$arResult["Urls"]["Blog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["Microblog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_MICROBLOG"], array("group_id" => $arResult["Group"]["ID"]));

			$arResult["Urls"]["Photo"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_PHOTO"], array("group_id" => $arResult["Group"]["ID"]));

			$arResult["Urls"]["Forum"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_FORUM"], array("group_id" => $arResult["Group"]["ID"]));

			$arResult["Urls"]["Calendar"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_CALENDAR"], array("group_id" => $arResult["Group"]["ID"]));

			$arResult["Urls"]["Tasks"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_TASKS"], array("group_id" => $arResult["Group"]["ID"]));

			$arResult["Urls"]["Files"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_FILES"], array(
				"group_id" => $arResult["Group"]["ID"], "path" => ""));

			$arResult["Urls"]["content_search"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_CONTENT_SEARCH"], array("group_id" => $arResult["Group"]["ID"]));

			$arResult["ActiveFeatures"] = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_GROUP, $arResult["Group"]["ID"]);

			$arResult["CanView"]["files"] = array_key_exists("files", $arResult["ActiveFeatures"]);
			if($arResult["CanView"]["files"])
			{
				$diskEnabled = CModule::includeModule('disk') && \Bitrix\Disk\Driver::isSuccessfullyConverted();
				if($diskEnabled)
				{
					$arResult["Urls"]["Files"] = CComponentEngine::makePathFromTemplate($arParams["PATH_TO_GROUP_DISK"], array(
						"group_id" => $arResult["Group"]["ID"],
						"PATH" => ""
					));
				}
			}

			$arResult["CanView"]["tasks"] = (array_key_exists("tasks", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "tasks", "view", CSocNetUser::IsCurrentUserModuleAdmin()));
			$arResult["CanView"]["calendar"] = (array_key_exists("calendar", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "calendar", "view", CSocNetUser::IsCurrentUserModuleAdmin()));
			$arResult["CanView"]["forum"] = (array_key_exists("forum", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "forum", "view", CSocNetUser::IsCurrentUserModuleAdmin()));
			$arResult["CanView"]["microblog"] = (array_key_exists("microblog", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "blog", "view_post", CSocNetUser::IsCurrentUserModuleAdmin()));
			$arResult["CanView"]["blog"] = (array_key_exists("blog", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "blog", "view_post", CSocNetUser::IsCurrentUserModuleAdmin()));
			$arResult["CanView"]["photo"] = (array_key_exists("photo", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "photo", "view", CSocNetUser::IsCurrentUserModuleAdmin()));
			$arResult["CanView"]["content_search"] = (array_key_exists("search", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "search", "view", CSocNetUser::IsCurrentUserModuleAdmin()));

			$arResult["Title"]["blog"] = ((array_key_exists("blog", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["blog"]) > 0) ? $arResult["ActiveFeatures"]["blog"] : GetMessage("SONET_UM_BLOG"));
			$arResult["Title"]["microblog"] = ((array_key_exists("microblog", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["microblog"]) > 0) ? $arResult["ActiveFeatures"]["microblog"] : GetMessage("SONET_UM_MICROBLOG"));
			$arResult["Title"]["photo"] = ((array_key_exists("photo", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["photo"]) > 0) ? $arResult["ActiveFeatures"]["photo"] : GetMessage("SONET_UM_PHOTO"));
			$arResult["Title"]["forum"] = ((array_key_exists("forum", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["forum"]) > 0) ? $arResult["ActiveFeatures"]["forum"] : GetMessage("SONET_UM_FORUM"));
			$arResult["Title"]["calendar"] = ((array_key_exists("calendar", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["calendar"]) > 0) ? $arResult["ActiveFeatures"]["calendar"] : GetMessage("SONET_UM_CALENDAR"));
			$arResult["Title"]["tasks"] = ((array_key_exists("tasks", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["tasks"]) > 0) ? $arResult["ActiveFeatures"]["tasks"] : GetMessage("SONET_UM_TASKS"));
			$arResult["Title"]["files"] = ((array_key_exists("files", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["files"]) > 0) ? $arResult["ActiveFeatures"]["files"] : GetMessage("SONET_UM_FILES"));
			$arResult["Title"]["content_search"] = ((array_key_exists("search", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["search"]) > 0) ? $arResult["ActiveFeatures"]["search"] : GetMessage("SONET_UM_SEARCH"));

			$a = array_keys($arResult["Urls"]);
			foreach ($a as $v)
			{
				$arResult["Urls"][strtolower($v)] = $arResult["Urls"][$v];
			}

			if (
				!empty($this->__parent)
				&& !empty($this->__parent->arParams)
				&& isset($this->__parent->arParams["SEF_MODE"])
			)
			{
				$arResult["SEF_MODE"] = $this->__parent->arParams["SEF_MODE"];
			}
			$arResult["PAGE_VAR"] = $arParams["PAGE_VAR"];

			$events = GetModuleEvents("socialnetwork", "OnFillSocNetMenu");
			while ($arEvent = $events->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array(&$arResult, array("ENTITY_TYPE" => SONET_ENTITY_GROUP, "ENTITY_ID" => $arResult["Group"]["ID"])));
			}

			$this->IncludeComponentTemplate();
		}
	}
}

$arReturn = array(
	"GROUP" => $arResult["Group"],
	"CURRENT_USER_PERMS" => $arResult["CurrentUserPerms"],
	"IS_SUBSCRIBED" => $arResult["bSubscribed"]
);

return $arReturn;
?>