<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

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

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Integration\Intranet\Settings;
use Bitrix\Socialnetwork\UserToGroupTable;

if (!Loader::includeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (
	!array_key_exists("MAX_ITEMS", $arParams)
	|| (int)$arParams["MAX_ITEMS"] <= 0
)
{
	$arParams["MAX_ITEMS"] = 6;
}

$arParams["GROUP_ID"] = (int)$arParams["GROUP_ID"];

$arParams["SET_NAV_CHAIN"] = (isset($arParams["SET_NAV_CHAIN"]) && $arParams["SET_NAV_CHAIN"] === "N" ? "N" : "Y");

if ($arParams["GROUP_VAR"] == '')
{
	$arParams["GROUP_VAR"] = "group_id";
}

if ($arParams["PAGE_VAR"] == '')
{
	$arParams["PAGE_VAR"] = "page";
}

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if ($arParams["PATH_TO_GROUP"] === '')
{
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_MODS"] = trim($arParams["PATH_TO_GROUP_MODS"]);
if ($arParams["PATH_TO_GROUP_MODS"] === '')
{
	$arParams["PATH_TO_GROUP_MODS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_mods&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_USERS"] = trim($arParams["PATH_TO_GROUP_USERS"]);
if ($arParams["PATH_TO_GROUP_USERS"] === '')
{
	$arParams["PATH_TO_GROUP_USERS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_users&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_EDIT"] = trim($arParams["PATH_TO_GROUP_EDIT"]);
if ($arParams["PATH_TO_GROUP_EDIT"] === '')
{
	$arParams["PATH_TO_GROUP_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_edit&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_REQUEST_SEARCH"] = trim($arParams["PATH_TO_GROUP_REQUEST_SEARCH"]);
if ($arParams["PATH_TO_GROUP_REQUEST_SEARCH"] === '')
{
	$arParams["PATH_TO_GROUP_REQUEST_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_request_search&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_REQUESTS"] = trim($arParams["PATH_TO_GROUP_REQUESTS"]);
if ($arParams["PATH_TO_GROUP_REQUESTS"] === '')
{
	$arParams["PATH_TO_GROUP_REQUESTS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_requests&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_BAN"] = trim($arParams["PATH_TO_GROUP_BAN"]);
if ($arParams["PATH_TO_GROUP_BAN"] === '')
{
	$arParams["PATH_TO_GROUP_BAN"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_ban&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_BLOG"] = trim($arParams["PATH_TO_GROUP_BLOG"]);
if( $arParams["PATH_TO_GROUP_BLOG"] === '')
{
	$arParams["PATH_TO_GROUP_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_blog&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_MICROBLOG"] = trim(($arParams["PATH_TO_GROUP_MICROBLOG"] ?? ''));
if ($arParams["PATH_TO_GROUP_MICROBLOG"] === '')
{
	$arParams["PATH_TO_GROUP_MICROBLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_microblog&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_PHOTO"] = trim($arParams["PATH_TO_GROUP_PHOTO"]);
if ($arParams["PATH_TO_GROUP_PHOTO"] === '')
{
	$arParams["PATH_TO_GROUP_PHOTO"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_photo&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_FORUM"] = trim($arParams["PATH_TO_GROUP_FORUM"]);
if ($arParams["PATH_TO_GROUP_FORUM"] === '')
{
	$arParams["PATH_TO_GROUP_FORUM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_forum&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_CALENDAR"] = trim($arParams["PATH_TO_GROUP_CALENDAR"]);
if ($arParams["PATH_TO_GROUP_CALENDAR"] === '')
{
	$arParams["PATH_TO_GROUP_CALENDAR"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_calendar&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_TASKS"] = trim($arParams["PATH_TO_GROUP_TASKS"]);
if ($arParams["PATH_TO_GROUP_TASKS"] === '')
{
	$arParams["PATH_TO_GROUP_TASKS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_tasks&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_FILES"] = trim($arParams["PATH_TO_GROUP_FILES"]);
if ($arParams["PATH_TO_GROUP_FILES"] === '')
{
	$arParams["PATH_TO_GROUP_FILES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_files&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_CONTENT_SEARCH"] = trim($arParams["PATH_TO_GROUP_CONTENT_SEARCH"]);
if ($arParams["PATH_TO_GROUP_CONTENT_SEARCH"] === '')
{
	$arParams["PATH_TO_GROUP_CONTENT_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_content_search&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["USE_MAIN_MENU"] = (isset($arParams["USE_MAIN_MENU"]) ? $arParams["USE_MAIN_MENU"] : false);

if ($arParams["USE_MAIN_MENU"] === "Y" && !array_key_exists("MAIN_MENU_TYPE", $arParams))
{
	$arParams["MAIN_MENU_TYPE"] = "left";
}

$arGroup = CSocNetGroup::GetById($arParams['GROUP_ID'], false, [
	'getSites' => true,
]);

if (
	$arGroup
	&& is_array($arGroup)
	&& $arGroup["ACTIVE"] === "Y"

)
{
	$arGroupSites = $arGroup['SITE_LIST'];

	if (in_array(SITE_ID, $arGroupSites))
	{
		$arResult["Group"] = $arGroup;

		if (
			Loader::includeModule("extranet")
			&& !CExtranet::IsExtranetSite()
			&& in_array(CExtranet::GetExtranetSiteID(), $arGroupSites)
		)
		{
			$arResult["Group"]["IS_EXTRANET"] = "Y";
		}
		else
		{
			$arResult["Group"]["IS_EXTRANET"] = "N";
		}

		if (
			$arResult["Group"]["CLOSED"] === "Y"
			&& COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") !== "Y"
		)
		{
			$arResult["HideArchiveLinks"] = true;
		}
		else
		{
			$arResult["HideArchiveLinks"] = false;
		}

		$arResult['CurrentUserPerms'] = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
			'groupId' => $arGroup['ID'],
		]);

		if (
			$arResult["CurrentUserPerms"] != false
			&& $arResult["CurrentUserPerms"]["UserCanViewGroup"]
		)
		{
			$arResult["bSubscribed"] = (
				in_array($arResult["CurrentUserPerms"]["UserRole"], \Bitrix\Socialnetwork\UserToGroupTable::getRolesMember(), true)
					? CSocNetSubscription::IsUserSubscribed($USER->GetID(), "SG".$arParams["GROUP_ID"])
					: false
			);

			$arResult["Urls"]["Edit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["View"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["UserRequestGroup"] = CComponentEngine::MakePathFromTemplate(($arParams["PATH_TO_USER_REQUEST_GROUP"] ?? ''), array("group_id" => $arResult["Group"]["ID"], "user_id" => $USER->GetID()));
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
			$arResult["Urls"]["TasksTask"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_TASKS_TASK"], array("group_id" => $arResult["Group"]["ID"]));

			$arResult["Urls"]["ScrumTeamSpeed"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SCRUM_TEAM_SPEED"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["ScrumBurnDown"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SCRUM_BURN_DOWN"], array("group_id" => $arResult["Group"]["ID"]));

			$arResult["Urls"]["Files"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_FILES"], array(
				"group_id" => $arResult["Group"]["ID"], "path" => ""));

			$arResult["Urls"]["content_search"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_CONTENT_SEARCH"], array("group_id" => $arResult["Group"]["ID"]));

			$arResult["ActiveFeatures"] = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_GROUP, $arResult["Group"]["ID"]);

			$arResult["CanView"]["files"] = array_key_exists("files", $arResult["ActiveFeatures"]);
			if($arResult["CanView"]["files"])
			{
				$diskEnabled = (Loader::includeModule('disk') && \Bitrix\Disk\Driver::isSuccessfullyConverted());
				if($diskEnabled)
				{
					$arResult["Urls"]["Files"] = CComponentEngine::makePathFromTemplate($arParams["PATH_TO_GROUP_DISK"], array(
						"group_id" => $arResult["Group"]["ID"],
						"PATH" => ""
					));
				}
			}

			$settings = new Settings();
			$arResult["CanView"]["tasks"] =
				array_key_exists("tasks", $arResult["ActiveFeatures"])
				&& CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "tasks", "view", CSocNetUser::IsCurrentUserModuleAdmin())
				&& $settings->isToolAvailable(Settings::TASKS_TOOLS['base_tasks'])
			;
			$arResult["CanView"]["calendar"] =
				array_key_exists("calendar", $arResult["ActiveFeatures"])
				&& CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "calendar", "view", CSocNetUser::IsCurrentUserModuleAdmin())
				&& $settings->isToolAvailable(Settings::CALENDAR_TOOLS['calendar'])
			;
			$arResult["CanView"]["forum"] = (array_key_exists("forum", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "forum", "view", CSocNetUser::IsCurrentUserModuleAdmin()));
			$arResult["CanView"]["microblog"] = (array_key_exists("microblog", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "blog", "view_post", CSocNetUser::IsCurrentUserModuleAdmin()));
			$arResult["CanView"]["blog"] = (array_key_exists("blog", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "blog", "view_post", CSocNetUser::IsCurrentUserModuleAdmin()));
			$arResult["CanView"]["photo"] = (array_key_exists("photo", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "photo", "view", CSocNetUser::IsCurrentUserModuleAdmin()));
			$arResult["CanView"]["content_search"] = (array_key_exists("search", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["Group"]["ID"], "search", "view", CSocNetUser::IsCurrentUserModuleAdmin()));
			$arResult['CanView']['chat'] = (
				array_key_exists('chat', $arResult['ActiveFeatures'])
				&& in_array($arResult['CurrentUserPerms']['UserRole'], UserToGroupTable::getRolesMember())
			);

			$arResult["Title"]["blog"] = Loc::getMessage("SONET_UM_BLOG");
			$arResult["Title"]["microblog"] = Loc::getMessage("SONET_UM_MICROBLOG");
			$arResult["Title"]["photo"] = Loc::getMessage("SONET_UM_PHOTO");
			$arResult["Title"]["forum"] = Loc::getMessage("SONET_UM_FORUM");
			$arResult["Title"]["calendar"] = Loc::getMessage("SONET_UM_CALENDAR");
			$arResult["Title"]["tasks"] = Loc::getMessage("SONET_UM_TASKS");
			$arResult["Title"]["files"] = Loc::getMessage("SONET_UM_FILES");
			$arResult["Title"]["content_search"] = Loc::getMessage("SONET_UM_SEARCH");

			if($arResult["CanView"]["chat"])
			{
				$arResult["Urls"]["chat"] = "javascript:BX.Socialnetwork.UI.Common.openMessenger('".$arResult["Group"]["ID"]."');";
			}

			$a = array_keys($arResult["Urls"]);
			foreach ($a as $v)
			{
				$arResult["Urls"][mb_strtolower($v)] = $arResult["Urls"][$v];
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

			foreach($arResult["ActiveFeatures"] as $key => $value)
			{
				if ($value <> '')
				{
					$arResult["Title"][($key === 'search' ? 'content_search' : $key)] = $value;
				}
			}

			if (Loader::includeModule('rest'))
			{
				$arResult["CanView"]['marketplace'] = array_key_exists('marketplace', $arResult["ActiveFeatures"]);
				$arResult["Title"]['marketplace'] = (
					array_key_exists('marketplace', $arResult["ActiveFeatures"])
					&& $arResult["ActiveFeatures"]['marketplace'] <> ''
						? $arResult["ActiveFeatures"]['marketplace']
						: Loc::getMessage('SONET_UM_MARKETPLACE_2')
				);
				$arResult["Urls"]['marketplace'] = $arResult["Urls"]["view"]."marketplace/";

				$placementHandlerList = \Bitrix\Rest\PlacementTable::getHandlersList('SONET_GROUP_DETAIL_TAB');

				if (is_array($placementHandlerList))
				{
					foreach($placementHandlerList as $placementHandler)
					{
						$tabId = 'placement_'.$placementHandler['ID'];

						$arResult["CanView"][$tabId] = array_key_exists($tabId, $arResult["ActiveFeatures"]);
						$arResult["Title"][$tabId] = (
							array_key_exists($tabId, $arResult["ActiveFeatures"])
							&& $arResult["ActiveFeatures"][$tabId] <> ''
									? $arResult["ActiveFeatures"][$tabId]
									: (
										$placementHandler['TITLE'] <> ''
											? $placementHandler['TITLE']
											: $placementHandler['APP_NAME']
									)
						);
						$arResult["Urls"][$tabId] = $arResult["Urls"]["view"]."app/".$placementHandler['ID']."/";
					}
				}
			}

			if (Loader::includeModule('tasks'))
			{
				$groupId = $arParams["GROUP_ID"];
				$counter = \Bitrix\Tasks\Internals\Counter::getInstance($USER->getId());
				$arResult['Tasks']['Counters'] = [
					\Bitrix\Tasks\Internals\Counter\Role::ALL => $counter->get(\Bitrix\Tasks\Internals\Counter\CounterDictionary::COUNTER_MEMBER_TOTAL, $groupId),
					\Bitrix\Tasks\Internals\Counter\Role::RESPONSIBLE => $counter->get(\Bitrix\Tasks\Internals\Counter\CounterDictionary::COUNTER_MY, $groupId),
					\Bitrix\Tasks\Internals\Counter\Role::ACCOMPLICE => $counter->get(\Bitrix\Tasks\Internals\Counter\CounterDictionary::COUNTER_ACCOMPLICES, $groupId),
					\Bitrix\Tasks\Internals\Counter\Role::ORIGINATOR => $counter->get(\Bitrix\Tasks\Internals\Counter\CounterDictionary::COUNTER_ORIGINATOR, $groupId),
					\Bitrix\Tasks\Internals\Counter\Role::AUDITOR => $counter->get(\Bitrix\Tasks\Internals\Counter\CounterDictionary::COUNTER_AUDITOR, $groupId),
				];

				$filter = \Bitrix\Tasks\Helper\Filter::getInstance($USER->getId(), $groupId);
				$arResult['Tasks']['DefaultRoleId'] = $filter->getDefaultRoleId();
			}

			$group = \Bitrix\Socialnetwork\Item\Workgroup::getById($arResult['Group']['ID']);
			$arResult['isScrumProject'] = $group && $group->isScrumProject();

			$this->IncludeComponentTemplate();
		}
	}
}

$arReturn = [
	"GROUP" => $arResult["Group"] ?? null,
	"CURRENT_USER_PERMS" => $arResult["CurrentUserPerms"] ?? null,
	"IS_SUBSCRIBED" => $arResult["bSubscribed"] ?? null,
];

return $arReturn;
