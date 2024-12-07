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

use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(Loc::getMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

CPageOption::SetOptionString("main", "nav_page_in_session", "N");
CSocNetTools::InitGlobalExtranetArrays();

if ($arParams["USE_KEYWORDS"] !== "N")
{
	$arParams["USE_KEYWORDS"] = "Y";
}

$arResult["NAV_ID"] = "sonet_user_groups";
$arResult['USE_PROJECTS'] = (
	isset($arParams['USE_PROJECTS'])
	&& $arParams['USE_PROJECTS'] === 'Y'
		? 'Y'
		: 'N'
);

$arParams["USER_ID"] = (int)$arParams["USER_ID"];
$currentUser = false;
if ($arParams["USER_ID"] <= 0)
{
	$arParams["USER_ID"] = (int)$USER->GetID();
	$currentUser = true;
}

$arResult["AJAX_CALL"] = (
	isset($_REQUEST["refreshAjax"])
	&& $_REQUEST["refreshAjax"] === "Y"
);

if ($currentUser)
{
	$hasGroups = false;

	$currentCache = \Bitrix\Main\Data\Cache::createInstance();
	$cacheTtl = 60*60*24*365;
	$cacheId = 'user_has_groups_'.SITE_ID.'_'.$arParams["USER_ID"];
	$cacheDir = '/sonet/user_group_member/'.SITE_ID.'/'.$arParams["USER_ID"];

	if ($currentCache->startDataCache($cacheTtl, $cacheId, $cacheDir))
	{
		$res = UserToGroupTable::getList([
			'filter' => [
				'USER_ID' => $arParams["USER_ID"],
				'@ROLE' => UserToGroupTable::getRolesMember(),
			],
			'select' => [ 'ID' ],
		]);
		if ($group = $res->fetch())
		{
			$hasGroups = true;
		}

		if (defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->startTagCache($cacheDir);
			$CACHE_MANAGER->registerTag("sonet_user2group_U".$arParams["USER_ID"]);
			$CACHE_MANAGER->endTagCache();
		}
		$currentCache->endDataCache(array('HAS_GROUPS' => $hasGroups));
	}
	else
	{
		$cacheResult = $currentCache->getVars();
		$hasGroups = $cacheResult['HAS_GROUPS'];
	}
}

$arResult['intanetInstalled'] = ModuleManager::isModuleInstalled('intranet');
$arParams["PAGE"] = Trim($arParams["PAGE"]);

if (!in_array($arParams["PAGE"], array("group_request_group_search", "user_groups", "user_projects", "groups_list", "groups_subject")))
{
	$arParams["PAGE"] = ($arResult["USE_PROJECTS"] === 'Y' ? "user_projects" : "user_groups");
}

$arResult["USER_GROUPS_EMPTY_MODE"] = false;


if (
	$arParams["PAGE"] === 'user_groups'
	&& $currentUser
	&& !$hasGroups
)
{
	$arParams["PAGE"] = "groups_list";
	$arResult["USER_GROUPS_EMPTY_MODE"] = true;
}

$arResult["ORDER_KEY"] = $_REQUEST['order'];
if (empty($arResult["ORDER_KEY"]))
{
	$arResult["ORDER_KEY"] = ($arResult["USER_GROUPS_EMPTY_MODE"] ? 'members_count' : 'alpha');
}

if ((int)$arParams["THUMBNAIL_SIZE"] <= 0)
{
	$arParams["THUMBNAIL_SIZE"] = 48;
}
if ((int)$arParams["THUMBNAIL_SIZE_COMMON"] <= 0)
{
	$arParams["THUMBNAIL_SIZE_COMMON"] = 100;
}

$user4Groups = $arParams["USER_ID"];
$user2Request = 0;
if ($arParams["PAGE"] === "group_request_group_search")
{
	$user4Groups = (int)$USER->GetID();
	$user2Request = $arParams["USER_ID"];
}

$filtered = false;

if (array_key_exists("filter_name", $_REQUEST) && $_REQUEST["filter_name"] <> '')
{
	$filtered = true;
	$arResult["filter_name"] = $_REQUEST["filter_name"];
}

if (empty($arParams["FILTER_ID"]))
{
	$arParams["FILTER_ID"] = "SONET_GROUP_LIST";
}

$arGroupFilter = array(
	"=ACTIVE" => "Y"
);

$arResult["USE_UI_FILTER"] = (isset($arParams["USE_UI_FILTER"]) && $arParams["USE_UI_FILTER"] === 'Y');

if ($arResult["USE_UI_FILTER"])
{
	$extranetSiteId = Option::get("extranet", "extranet_site");
	$extranetSiteId = ($extranetSiteId && ModuleManager::isModuleInstalled('extranet') ?  $extranetSiteId : false);

	$filterOption = new \Bitrix\Main\UI\Filter\Options(
		$arParams["FILTER_ID"],
		Bitrix\Socialnetwork\Integration\Main\UIFilter\Workgroup::getFilterPresetList(array(
			'currentUserId' => ($USER->isAuthorized() ? $USER->getId() : false),
			'extranetSiteId' => $extranetSiteId
		))
	);
	$filterData = $filterOption->getFilter();

	if (
		isset($filterData['FILTER_APPLIED'])
		&& $filterData['FILTER_APPLIED']
	)
	{
		if (
			isset($filterData['FIND'])
			&& !empty($filterData['FIND'])
		)
		{
			$filtered = true;
			$arResult["filter_name"] = $filterData['FIND'];
		}

		if (
			isset($filterData['NAME'])
			&& !empty($filterData['NAME'])
		)
		{
			$filtered = true;
			$arResult["filter_name_only"] = $filterData['NAME'];
		}

		if (
			isset($filterData['MEMBER'])
			&& !empty($filterData['MEMBER'])
			&& preg_match('/^U(\d+)$/i', $filterData['MEMBER'], $matches)
			&& !empty($matches[1])
			&& (int)$matches[1] > 0
		)
		{
			$filtered = true;
			$arResult["filter_member"] = (int)$matches[1];

			if (SITE_TEMPLATE_ID !== 'bitrix24')
			{
				\Bitrix\Main\FinderDestTable::merge(array(
					"CONTEXT" => "SONET_GROUP_LIST_FILTER_MEMBER",
					"CODE" => $filterData['MEMBER']
				));
			}
		}

		if (
			isset($filterData['OWNER'])
			&& !empty($filterData['OWNER'])
			&& preg_match('/^U(\d+)$/i', $filterData['OWNER'], $matches)
			&& !empty($matches[1])
			&& (int)$matches[1] > 0
		)
		{
			$filtered = true;
			$arResult["filter_owner"] = (int)$matches[1];

			if (SITE_TEMPLATE_ID !== 'bitrix24')
			{
				\Bitrix\Main\FinderDestTable::merge(array(
					"CONTEXT" => "SONET_GROUP_LIST_FILTER_OWNER",
					"CODE" => $filterData['OWNER']
				));
			}
		}

		if (isset($filterData['EXTRANET']))
		{
			$arResult["filter_extranet"] = $filterData['EXTRANET'];
		}

		if (
			isset($filterData['FAVORITES'])
			&& $filterData['FAVORITES'] === 'Y'
		)
		{
			$arResult["filter_favorites"] = 'Y';
		}

		if (
			isset($filterData['LANDING'])
			&& $filterData['LANDING'] === 'Y'
		)
		{
			$arResult["filter_landing"] = 'Y';
		}

		if (!empty($filterData['TAG']))
		{
			$arResult["~tags"] = $filterData['TAG'];
			$arResult["tags"] = htmlspecialcharsex($filterData['TAG']);
		}

		if (isset($filterData['CLOSED']))
		{
			$arResult["filter_archive"] = $filterData['CLOSED'];
		}
		else // set for later isset() check
		{
			$arResult["filter_archive"] = "";
		}

		if (isset($filterData['PROJECT']))
		{
			$arResult["filter_project"] = $filterData['PROJECT'];
		}

		if (!empty($filterData["PROJECT_DATE_START_from"]))
		{
			$filtered = true;
			$arGroupFilter[">=PROJECT_DATE_START"] = $filterData["PROJECT_DATE_START_from"];
		}

		if (!empty($filterData["PROJECT_DATE_START_to"]))
		{
			$filtered = true;
			$arGroupFilter["<=PROJECT_DATE_START"] = ConvertTimeStamp(MakeTimeStamp($filterData["PROJECT_DATE_START_to"], CSite::getDateFormat("SHORT")) + 86399, "FULL");
		}

		if (!empty($filterData["PROJECT_DATE_FINISH_from"]))
		{
			$filtered = true;
			$arGroupFilter[">=PROJECT_DATE_FINISH"] = $filterData["PROJECT_DATE_FINISH_from"];
		}

		if (!empty($filterData["PROJECT_DATE_FINISH_to"]))
		{
			$filtered = true;
			$arGroupFilter["<=PROJECT_DATE_FINISH"] = ConvertTimeStamp(MakeTimeStamp($filterData["PROJECT_DATE_FINISH_to"], CSite::getDateFormat("SHORT")) + 86399, "FULL");
		}

		$groupPropertiesList = $USER_FIELD_MANAGER->GetUserFields("SONET_GROUP", 0, LANGUAGE_ID);
		$availableUFTypes = ['date', 'datetime', 'string', 'double', 'boolean', 'crm'];

		foreach ($groupPropertiesList as $field => $arUserField)
		{
			if (
				empty($arUserField['SHOW_FILTER'])
				|| $arUserField['SHOW_FILTER'] === 'N'
			)
			{
				unset($groupPropertiesList[$field]);
				continue;
			}

			$type = $arUserField['USER_TYPE_ID'];

			if (!in_array($type, $availableUFTypes))
			{
				$type = 'string';
			}
			if ($type === 'datetime')
			{
				$type = 'date';
			}

			if ($type === 'double')
			{
				$type = 'number';
			}

			if ($type === 'date')
			{
				if (!empty($filterData[$field."_from"]))
				{
					$filtered = true;
					$arGroupFilter[">=".$field] = $filterData[$field."_from"];
				}

				if (!empty($filterData[$field."_to"]))
				{
					$filtered = true;
					$arGroupFilter["<=".$field] = $filterData[$field."_to"];
				}
			}
			elseif (
				isset($filterData[$field])
				&& in_array($type, array('number', 'string', 'boolean'))
			)
			{
				$filtered = true;
				$arGroupFilter["=".$field] = $filterData[$field];
			}
		}
	}
	else // main.ui.filter without CLOSED
	{
		$arResult["filter_archive"] = "";
	}
}

if ($filtered)
{
	$arParams["CACHE_TIME"] = 0;
}

if ($arParams["PAGE"] === "groups_list")
{
	if (
		array_key_exists("filter_my", $_REQUEST)
		&& $_REQUEST["filter_my"] === "Y"
	)
	{
		$arResult["filter_my"] = $_REQUEST["filter_my"];
	}

	if (array_key_exists("filter_subject_id", $_REQUEST) && (int)$_REQUEST["filter_subject_id"] > 0)
	{
		$arResult["filter_subject_id"] = $_REQUEST["filter_subject_id"];
	}

	if (array_key_exists("filter_archive", $_REQUEST) && $_REQUEST["filter_archive"] === "Y")
	{
		$arResult["filter_archive"] = $_REQUEST["filter_archive"];
	}

	if ((int)$arParams["SUBJECT_ID"] == -1)
	{
		$arResult["filter_archive"] = "Y";
	}

	if (array_key_exists("filter_extranet", $_REQUEST) && $_REQUEST["filter_extranet"] <> '')
	{
		$arResult["filter_extranet"] = $_REQUEST["filter_extranet"];
	}

	if (
		!isset($arResult["filter_project"])
		&& !empty($_REQUEST["filter_project"])
		&& in_array($_REQUEST["filter_project"], array('Y', 'N'))
	)
	{
		$arResult["filter_project"] = $_REQUEST["filter_project"];
	}

	if (
		array_key_exists("filter_favorites", $_REQUEST)
		&& $_REQUEST["filter_favorites"] <> ''
		&& $USER->IsAuthorized()
	)
	{
		$arResult["filter_favorites"] = $_REQUEST["filter_favorites"];
	}

	if (
		!isset($arResult["filter_tags"])
		&& array_key_exists("filter_tags", $_REQUEST)
		&& $_REQUEST["filter_tags"] <> ''
	)
	{
		$arResult["filter_tags"] = $_REQUEST["filter_tags"];
	}

	if (
		array_key_exists("tags", $_REQUEST)
		&& $_REQUEST["tags"] <> ''
	)
	{
		$arResult["~tags"] = $_REQUEST["tags"];
		$arResult["tags"] = htmlspecialcharsbx($arResult["~tags"]);
	}
}

if (
	$arParams["PAGE"] === "groups_subject"
	&& (int)$arParams["SUBJECT_ID"] > 0
)
{
	$arResult["filter_subject_id"] = (int)$arParams["SUBJECT_ID"];
}

$arResult["WORKGROUPS_PATH"] = Option::get("socialnetwork", "workgroups_list_page", false, SITE_ID);
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] === "N" ? "N" : "Y");

if ($arParams["USER_VAR"] == '')
{
	$arParams["USER_VAR"] = "user_id";
}
if ($arParams["GROUP_VAR"] == '')
{
	$arParams["GROUP_VAR"] = "group_id";
}
if ($arParams["PAGE_VAR"] == '')
{
	$arParams["PAGE_VAR"] = "page";
}

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] === '')
{
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
}

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if ($arParams["PATH_TO_GROUP"] === '')
{
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_EDIT"] = trim($arParams["PATH_TO_GROUP_EDIT"]);
if ($arParams["PATH_TO_GROUP_EDIT"] === '')
{
	$arParams["PATH_TO_GROUP_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_edit&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_CREATE"] = trim($arParams["PATH_TO_GROUP_CREATE"]);
if ($arParams["PATH_TO_GROUP_CREATE"] === '')
{
	$arParams["PATH_TO_GROUP_CREATE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_create&".$arParams["USER_VAR"]."=#user_id#");
}

$arParams["PATH_TO_GROUP_SEARCH"] = trim($arParams["PATH_TO_GROUP_SEARCH"]);
if ($arParams["PATH_TO_GROUP_SEARCH"] === '')
{
	$arParams["PATH_TO_GROUP_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_search");
}

$arParams["PATH_TO_GROUP_REQUEST_USER"] = trim($arParams["PATH_TO_GROUP_REQUEST_USER"]);
if ($arParams["PATH_TO_GROUP_REQUEST_USER"] === '')
{
	$arParams["PATH_TO_GROUP_REQUEST_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_request_user&".$arParams["USER_VAR"]."=#user_id#&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_LOG"] = trim($arParams["PATH_TO_LOG"]);
if ($arParams["PATH_TO_LOG"] == '')
{
	$arParams["PATH_TO_LOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=log");
}

$arParams["ITEMS_COUNT"] = (int)$arParams["ITEMS_COUNT"];
if ($arParams["ITEMS_COUNT"] <= 0)
{
	$arParams["ITEMS_COUNT"] = 30;
}

/* obsolete parameter for default template */
$arParams["COLUMNS_COUNT"] = (int)$arParams["COLUMNS_COUNT"];
if ($arParams["COLUMNS_COUNT"] <= 0)
{
	$arParams["COLUMNS_COUNT"] = 3;
}

$arParams["DATE_TIME_FORMAT"] = Trim($arParams["DATE_TIME_FORMAT"]);
$arParams["DATE_TIME_FORMAT"] = (($arParams["DATE_TIME_FORMAT"] == '') ? CDatabase::DateFormatToPHP(CSite::GetDateFormat()) : $arParams["DATE_TIME_FORMAT"]);

/***************** CACHE ****************************************/
if (!isset($arParams["CACHE_TIME"]))
{
	$arParams["CACHE_TIME"] = 3600;
}

if (
	$arParams["CACHE_TYPE"] === "Y"
	|| (
		$arParams["CACHE_TYPE"] === "A"
		&& Option::get("main", "component_cache_on", "Y") === "Y"
	)
)
{
	$arParams["CACHE_TIME"] = (int)$arParams["CACHE_TIME"];
}
else
{
	$arParams["CACHE_TIME"] = 0;
}

$groupCache = new CPHPCache;
$cachePath = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName);
/********************************************************************/

$arResult["FatalError"] = "";

if (
	$user4Groups <= 0
	&& (
		in_array($arParams["PAGE"], array("group_request_group_search", "user_groups"))
		|| $arResult["filter_my"] === "Y"
	)
)
{
	$arResult["FatalError"] = Loc::getMessage("SONET_C36_NO_USER_ID").". ";
}

if (
	($arResult["FatalError"] == '')
	&& $arParams["PAGE"] === "group_request_group_search"
)
{
	if ($user2Request <= 0)
	{
		$arResult["FatalError"] = Loc::getMessage("SONET_C36_NO_USER_ID").". ";
	}
	elseif ($user2Request == $user4Groups)
	{
		$arResult["FatalError"] = Loc::getMessage("SONET_C36_SELF").". ";
	}
}

if ($arResult["FatalError"] == '')
{
	if ($arParams["PAGE"] === "groups_list")
	{
		$arResult["Subjects"] = array();
		$dbSubjects = CSocNetGroupSubject::GetList(
			array("SORT" => "ASC", "NAME" => "ASC"),
			array("SITE_ID" => SITE_ID),
			false,
			false,
			array("ID", "NAME")
		);
		while ($arSubject = $dbSubjects->GetNext())
		{
			$arResult["Subjects"][$arSubject["ID"]] = $arSubject["NAME"];
		}
	}
	elseif ($arParams["PAGE"] === "groups_subject" && (int)$arResult["filter_subject_id"] > 0)
	{
		$arResult["Subjects"] = array();
		$dbSubjects = CSocNetGroupSubject::GetList(
			array("SORT" => "ASC", "NAME" => "ASC"),
			array("SITE_ID" => SITE_ID, "ID" => (int)$arResult["filter_subject_id"]),
			false,
			false,
			array("ID", "NAME")
		);
		if ($arSubject = $dbSubjects->GetNext())
		{
			$arResult["Subject"] = $arSubject;
		}
	}
}

if (
	$arResult["FatalError"] == ''
	&& $user4Groups > 0
)
{
	$dbUser = CUser::GetByID($user4Groups);
	$arResult["User"] = $dbUser->GetNext();

	if (!is_array($arResult["User"]))
	{
		$arResult["FatalError"] = Loc::getMessage("SONET_P_USER_NO_USER").". ";
	}
	if (CModule::IncludeModule('extranet') && !CExtranet::IsProfileViewable($arResult["User"]))
	{
		return false;
	}
}

if ($arResult["FatalError"] == '')
{
	$arResult["UserRequest"] = false;
	if ($user2Request > 0)
	{
		$dbUser = CUser::GetByID($user2Request);
		$arResult["UserRequest"] = $dbUser->GetNext();

		if (!is_array($arResult["UserRequest"]))
		{
			$arResult["FatalError"] = Loc::getMessage("SONET_P_USER_NO_USER").". ";
		}
		if (CModule::IncludeModule('extranet') && !CExtranet::IsProfileViewable($arResult["UserRequest"]))
		{
			return false;
		}
	}
}

if ($arResult["FatalError"] == '')
{
	$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($USER->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());
	$arResult["ALLOW_CREATE_GROUP"] = \Bitrix\Socialnetwork\Helper\Workgroup\Access::canCreate();

	$arCacheKeys = array();
	$arNavigation = array();

	foreach ($arResult as $key => $value)
	{
		if (mb_substr($key, 0, 7) === "filter_")
		{
			$arCacheKeys[] = $key."_".$value;
		}
	}

	$nav = new \Bitrix\Main\UI\PageNavigation($arResult["NAV_ID"]);
	$nav->allowAllRecords(false)->setPageSize($arParams["ITEMS_COUNT"])->initFromUri();

	$arNavigation = array(
		'ITEMS_COUNT' => $arParams["ITEMS_COUNT"],
		'CURRENT_PAGE' => $nav->getCurrentPage(),
		'ALL_RECORDS_SHOWN' => $nav->allRecordsShown()
	);

	$arCacheKeys = array_merge($arCacheKeys, $arNavigation);

	if (array_key_exists("tags", $arResult) && $arResult["tags"] <> '')
	{
		$arCacheKeys[] = $arResult["tags"];
	}

	$arCacheKeys[] = $arResult["ORDER_KEY"];
	$arCacheResult = array();

	$cacheId = "socnet_user_groups_".SITE_ID.LANGUAGE_ID.'_'.$arParams["PAGE"]."_".$USER->GetID()."_"."_".$arResult["User"]["ID"]."_".md5(serialize($arCacheKeys))."_" . (int)CSocNetUser::IsCurrentUserModuleAdmin();
	if (
		$arParams["CACHE_TIME"] > 0
		&& $groupCache->InitCache($arParams["CACHE_TIME"], $cacheId, $cachePath)
	)
	{
		$vars = $groupCache->GetVars();
		$arCacheResult = $vars["arCacheResult"];

		if (!empty($arCacheResult["ASSETS"]))
		{
			if (!empty($arCacheResult["ASSETS"]["CSS"]))
			{
				foreach ($arCacheResult["ASSETS"]["CSS"] as $cssFile)
				{
					\Bitrix\Main\Page\Asset::getInstance()->addCss($cssFile);
				}
			}

			if (!empty ($arCacheResult["ASSETS"]["JS"]))
			{
				foreach ($arCacheResult["ASSETS"]["JS"] as $jsFile)
				{
					\Bitrix\Main\Page\Asset::getInstance()->addJs($jsFile);
				}
			}
		}
	}
	else
	{
		if (
			$arParams["CACHE_TIME"] > 0
			&& defined("BX_COMP_MANAGED_CACHE")
		)
		{
			$CACHE_MANAGER->startTagCache($cachePath);

			if (
				isset($arResult["ORDER_KEY"])
				&& $arResult["ORDER_KEY"] === 'date_view'
				&& $arParams["USER_ID"] == $USER->getId()
			)
			{
				$CACHE_MANAGER->registerTag("sonet_group_view_U".$arParams["USER_ID"]);
			}

			$CACHE_MANAGER->registerTag("sonet_user2group_U".$arParams["USER_ID"]);
			$CACHE_MANAGER->registerTag("sonet_group");

			if (
				isset($arResult["ORDER_KEY"])
				&& $arResult["ORDER_KEY"] === 'date_activity'
			)
			{
				$CACHE_MANAGER->registerTag("sonet_group_activity");
			}

			if (
				$USER->isAuthorized()
				&& $arParams["USER_ID"] == $USER->getId()
			)
			{
				$CACHE_MANAGER->registerTag("sonet_group_favorites_U".$arParams["USER_ID"]);
			}
		}

		$arGroupID = Array();

		if (
			(
				$arResult["CurrentUserPerms"]
				&& $arResult["CurrentUserPerms"]["Operations"]["viewgroups"]
			)
			|| in_array($arParams["PAGE"], array("groups_list", "groups_subject"))
		)
		{
			$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false);

			$arCacheResult["Urls"]["GroupsAdd"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_CREATE"], array("user_id" => $arResult["User"]["ID"]));
			$arCacheResult["Urls"]["LogGroups"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG"], array());
			$arCacheResult["Urls"]["LogGroups"] .= ((mb_strpos($arCacheResult["Urls"]["LogGroups"], "?") !== false) ? "&" : "?")."flt_entity_type=".SONET_ENTITY_GROUP;
			$arCacheResult["CanViewLog"] = ($arResult["User"]["ID"] == $USER->GetID());

			$arCacheResult["Groups"] = false;

			if (!empty($arResult["filter_name"]))
			{
				if ($arResult["USE_UI_FILTER"])
				{
					$operation = \Bitrix\Socialnetwork\WorkgroupTable::getEntity()->fullTextIndexEnabled("SEARCH_INDEX") ? '*' : '*%';
					$arGroupFilter[$operation."SEARCH_INDEX"] = \Bitrix\Socialnetwork\Item\Workgroup::prepareToken($arResult["filter_name"]);
				}
				else
				{
					$arGroupFilter[] = array(
						'LOGIC' => 'OR',
						'%NAME' => $arResult["filter_name"],
						'%DESCRIPTION' => $arResult["filter_name"]
					);
				}
			}

			if (!empty($arResult["filter_name_only"])) // USE_UI_FILTER == Y
			{
				$arGroupFilter['%NAME'] = $arResult["filter_name_only"];
			}

			$arGroupFilter["=WorkgroupSite:GROUP.SITE_ID"] = SITE_ID;

			if (!CSocNetUser::IsCurrentUserModuleAdmin())
			{
				$arAvailableGroupID = array();
				$dbGroups = CSocnetGroup::GetList(
					array("ID" => "ASC"),
					array(
						"SITE_ID" => SITE_ID,
						"CHECK_PERMISSIONS" => $USER->GetID()
					),
					false,
					false,
					array("ID")
				);

				while ($arGroups = $dbGroups->GetNext())
				{
					if (!in_array($arGroups["ID"], $arAvailableGroupID))
					{
						$arAvailableGroupID[] = $arGroups["ID"];
					}
				}

				if (empty($arAvailableGroupID))
				{
					$bNoMyGroups = true;
				}
				else
				{
					$arGroupFilter["ID"] = $arAvailableGroupID;
				}
			}

			if (
				Option::get("socialnetwork", "work_with_closed_groups", "N") !== "Y"
				&& $arResult["filter_tags"] !== "Y"
			)
			{
				if (isset($arResult["filter_archive"])) // bitrix24 ui.filter
				{
					if (!empty($arResult["filter_archive"]))
					{
						$arGroupFilter["=CLOSED"] = ($arResult["filter_archive"] === "Y" ? "Y" : "N");
					}
				}
				else // not bitrix24
				{
					$arGroupFilter["=CLOSED"] = "N";
				}
			}

			if ((int)$arResult["filter_subject_id"] > 0)
			{
				$arGroupFilter["WORKGROUP_SUBJECT.ID"] = (int)$arResult["filter_subject_id"];
			}

			// get my groups for extranet
			if (
				CModule::IncludeModule("extranet")
				&& CExtranet::IsExtranetSite()
			)
			{
				$arCacheResult["bExtranet"] = true;
				if (!$USER->IsAdmin() && !CSocNetUser::IsCurrentUserModuleAdmin())
				{
					$arGroupFilterMy = array(
						"USER_ID" => $USER->GetID(),
						"<=ROLE" => SONET_ROLES_USER,
						"GROUP_SITE_ID" => SITE_ID,
						"GROUP_ACTIVE" => "Y"
					);

					$dbGroups = CSocNetUserToGroup::GetList(
						array("GROUP_NAME" => "ASC"),
						$arGroupFilterMy,
						false,
						false,
						array("GROUP_ID")
					);

					$arMyGroups = array();
					while ($arGroups = $dbGroups->GetNext())
					{
						$arMyGroups[] = $arGroups["GROUP_ID"];
					}

					if (count($arMyGroups) <= 0)
					{
						$bNoMyGroups = true;
					}
					else
					{
						$arGroupFilter["ID"] = (!empty($arGroupFilter["ID"]) ? array_intersect($arGroupFilter["ID"], $arMyGroups) : $arMyGroups);
					}
				}
			}
			else
			{
				// not extranet
				if (
					$arResult["filter_my"] === "Y"
					|| in_array($arParams["PAGE"], array("user_groups", "user_projects"))
				)
				{
					$arUserGroupFilter["USER_ID"] = $arResult["User"]["ID"];
					$arUserGroupFilter["<=ROLE"] = SONET_ROLES_USER;
				}

				if (!empty($arResult["filter_project"]))
				{
					$arGroupFilter["=PROJECT"] = $arResult["filter_project"];
				}
				elseif ($arParams["PAGE"] === "user_projects")
				{
					$arGroupFilter["=PROJECT"] = 'Y';
				}

				if (!empty($arResult["filter_landing"]))
				{
					$arGroupFilter["=LANDING"] = $arResult["filter_landing"];
				}

				if (
					!empty($arResult["filter_extranet"])
					&& CModule::IncludeModule("extranet")
					&& !CExtranet::IsExtranetSite()

				)
				{
					if ($arResult["filter_extranet"] === 'Y')
					{
						$arUserGroupFilter["=GROUP_SITE_ID"] = CExtranet::GetExtranetSiteID();
						if (!CSocNetUser::isCurrentUserModuleAdmin())
						{
							$arUserGroupFilter["USER_ID"] = $arResult["User"]["ID"];
							$arUserGroupFilter["<=ROLE"] = SONET_ROLES_USER;
						}
					}
					else
					{
						$arUserGroupFilter["!=GROUP_SITE_ID"] = CExtranet::GetExtranetSiteID();
					}
				}
			}

			if (
				isset($arResult["filter_member"])
				&& $arResult["filter_member"] > 0
			)
			{
				if (!empty($arUserGroupFilter["USER_ID"]))
				{
					$arUserGroupFilter2 = array(
						"USER_ID" => $arResult["filter_member"],
						"<=ROLE" => UserToGroupTable::ROLE_USER
					);
				}
				else
				{
					$arUserGroupFilter["USER_ID"] = $arResult["filter_member"];
					$arUserGroupFilter["<=ROLE"] = UserToGroupTable::ROLE_USER;
				}
			}

			if (
				isset($arResult["filter_owner"])
				&& $arResult["filter_owner"] > 0
			)
			{
				if (!empty($arUserGroupFilter["USER_ID"]))
				{
					$arUserGroupFilter2 = array(
						"USER_ID" => $arResult["filter_owner"],
						"<=ROLE" => UserToGroupTable::ROLE_OWNER
					);
				}
				else
				{
					$arUserGroupFilter["USER_ID"] = $arResult["filter_owner"];
					$arUserGroupFilter["<=ROLE"] = UserToGroupTable::ROLE_OWNER;
				}
			}

			if (
				$arParams["USE_KEYWORDS"] === "Y"
				&& $arResult["~tags"] <> ''
			)
			{
				$arGroupFilter['Bitrix\Socialnetwork\WorkgroupTag:GROUP.NAME'] = mb_strtolower($arResult["~tags"]);
			}

			if (
				!$bNoMyGroups
				&& !empty($arUserGroupFilter)
			)
			{
				$arUserGroupsList = array();
				$dbUserGroups = CSocNetUserToGroup::getList(
					array("GROUP_NAME" => "ASC"),
					$arUserGroupFilter,
					false,
					false,
					array("GROUP_ID")
				);
				if ($dbUserGroups)
				{
					while ($arUserGroups = $dbUserGroups->GetNext())
					{
						$arUserGroupsList[] = $arUserGroups["GROUP_ID"];
					}
					$arUserGroupsList = array_unique($arUserGroupsList);
				}

				if (
					!empty($arUserGroupsList)
					&& !empty($arUserGroupFilter2)
				)
				{
					$dbUserGroups = UserToGroupTable::getList(array(
						'filter' => array_merge(array('@GROUP_ID' => $arUserGroupsList), $arUserGroupFilter2),
						'select' => array('GROUP_ID')
					));

					$arUserGroupsList = array();

					while ($arUserGroups = $dbUserGroups->fetch())
					{
						if (!in_array($arUserGroups["GROUP_ID"], $arUserGroupsList))
						{
							$arUserGroupsList[] = $arUserGroups["GROUP_ID"];
						}
					}
				}

				$arGroupFilter["ID"] = (!empty($arGroupFilter["ID"]) ? array_intersect($arGroupFilter["ID"], $arUserGroupsList) : $arUserGroupsList);

				if (empty($arGroupFilter["ID"]))
				{
					$bNoMyGroups = true;
				}
			}
		}

		if (
			!$bNoMyGroups
			&& (
				$arResult["filter_my"] === "Y"
				|| (
					$arParams["PAGE"] === "user_groups"
					&& !$USER->IsAdmin()
					&& !CSocNetUser::IsCurrentUserModuleAdmin()
				)
				|| (
					$arParams["PAGE"] === "user_groups"
					&& (
						$USER->IsAdmin()
						|| CSocNetUser::IsCurrentUserModuleAdmin()
					)
					&& ((int)$USER->GetID() != $arParams["USER_ID"])
				)
				|| $arResult["filter_extranet"] === "Y"
			)
			&& (
				!array_key_exists("ID", $arGroupFilter)
				|| !is_array($arGroupFilter["ID"])
				|| count($arGroupFilter["ID"]) <= 0
			)
		)
		{
			$bNoMyGroups = true;
		}

		if (!$bNoMyGroups)
		{
			$arCacheResult["Groups"] = array();
			$arCacheResult["Groups"]["List"] = false;

			$arFilterTmp = array();
			$dbGroupTmp = \Bitrix\Socialnetwork\WorkgroupTable::getList(array(
				'order' => array(
					'ID' => 'ASC'
				),
				'filter' => $arGroupFilter,
				'group' => array("ID"),
				'select' => array("ID"),
				'data_doubling' => false
			));

			while ($arGroupTmp = $dbGroupTmp->Fetch())
			{
				$arFilterTmp[] = $arGroupTmp["ID"];
			}

			if (!empty($arFilterTmp))
			{
				$nav = new \Bitrix\Main\UI\PageNavigation($arResult["NAV_ID"]);
				$nav->allowAllRecords(false)->setPageSize($arParams["ITEMS_COUNT"])->initFromUri();

				$query = new \Bitrix\Main\Entity\Query(\Bitrix\Socialnetwork\WorkgroupTable::getEntity());

				switch($arResult["ORDER_KEY"])
				{
					case 'alpha':
						$query->addOrder('NAME', 'ASC');
						break;
					case 'date_activity':
						$query->addOrder('DATE_ACTIVITY', 'DESC');
						break;
					case 'date_create':
						$query->addOrder('DATE_CREATE', 'DESC');
						break;
					case 'date_request':
						if ($USER->isAuthorized())
						{
							$query->registerRuntimeField(
								'',
								new \Bitrix\Main\Entity\ReferenceField('UG',
									\Bitrix\Socialnetwork\UserToGroupTable::getEntity(),
									array(
										'=ref.GROUP_ID' => 'this.ID',
										'=ref.USER_ID' =>  new \Bitrix\Main\DB\SqlExpression($USER->getId())
									),
									array('join_type' => 'LEFT')
								)
							);
							$query->addOrder('UG.DATE_UPDATE', 'DESC');
						}
						break;
					case 'date_view':
						if ($USER->isAuthorized())
						{
							$query->registerRuntimeField(
								'',
								new \Bitrix\Main\Entity\ReferenceField('GV',
									\Bitrix\Socialnetwork\WorkgroupViewTable::getEntity(),
									array(
										'=ref.GROUP_ID' => 'this.ID',
										'=ref.USER_ID' =>  new \Bitrix\Main\DB\SqlExpression($USER->getId())
									),
									array('join_type' => 'LEFT')
								)
							);
							$query->addOrder('GV.DATE_VIEW', 'DESC');
						}
						break;
					case 'members_count':
						$query->addOrder('NUMBER_OF_MEMBERS', 'DESC');
						break;
					default:
						$query->addOrder('NAME', 'ASC');
				}

				if (
					$arResult["filter_favorites"] === "Y"
					&& $USER->isAuthorized()
				)
				{
					$query->registerRuntimeField(
						'',
						new \Bitrix\Main\Entity\ReferenceField('GF',
							\Bitrix\Socialnetwork\WorkgroupFavoritesTable::getEntity(),
							array(
								'=ref.GROUP_ID' => 'this.ID'
							),
							array('join_type' => 'INNER')
						)
					);
					$query->addFilter('=GF.USER_ID', $USER->getId());
				}

				$query->addFilter('@ID', $arFilterTmp);

				$query->addSelect('ID');
				$query->addSelect('NAME');
				$query->addSelect('DESCRIPTION');
				$query->addSelect('IMAGE_ID');
				$query->addSelect('VISIBLE');
				$query->addSelect('OWNER_ID');
				$query->addSelect('INITIATE_PERMS');
				$query->addSelect('OPENED');
				$query->addSelect('CLOSED');
				$query->addSelect('NUMBER_OF_MEMBERS');
				$query->addSelect('AVATAR_TYPE');

				$query->countTotal(true);
				$query->setOffset($nav->getOffset());
				$query->setLimit($nav->getLimit());

				$dbGroup = $query->exec();

				$nav->setRecordCount($dbGroup->getCount());
			}

			if ($dbGroup)
			{
				while ($arGroup = $dbGroup->fetch())
				{
					$arGroup["NAME"] = htmlspecialcharsEx($arGroup["NAME"]);
					$arGroup["DESCRIPTION"] = htmlspecialcharsEx($arGroup["DESCRIPTION"]);

					if ($arCacheResult["Groups"]["List"] == false)
					{
						$arCacheResult["Groups"]["List"] = array();
					}

					$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroup["ID"]));

					if ((int)$arGroup["IMAGE_ID"] <= 0)
					{
						$arGroup["IMAGE_ID"] = Option::get("socialnetwork", "default_group_picture", false, SITE_ID);
					}

					$arImageResized = $arImageResizedCommon = false;
					$imageFile = CFile::GetFileArray($arGroup["IMAGE_ID"]);
					if ($imageFile !== false)
					{
						$arImageResized = CFile::ResizeImageGet(
							$imageFile,
							array("width" => $arParams["THUMBNAIL_SIZE"], "height" => $arParams["THUMBNAIL_SIZE"]),
							BX_RESIZE_IMAGE_EXACT
						);
						$arImageResizedCommon = CFile::ResizeImageGet(
							$imageFile,
							array("width" => $arParams["THUMBNAIL_SIZE_COMMON"], "height" => $arParams["THUMBNAIL_SIZE_COMMON"]),
							BX_RESIZE_IMAGE_EXACT
						);
					}

					$arImage = CSocNetTools::InitImage($arGroup["IMAGE_ID"], 150, "/bitrix/images/socialnetwork/nopic_group_150.gif", 150, $pu, true);

					if ($arParams["PAGE"] === "group_request_group_search")
					{
						$arCurrentUserPerms4Group = CSocNetUserToGroup::InitUserPerms($arResult["User"]["ID"], array("ID" => $arGroup["ID"], "OWNER_ID" => $arGroup["OWNER_ID"], "INITIATE_PERMS" => $arGroup["INITIATE_PERMS"], "VISIBLE" => $arGroup["VISIBLE"], "OPENED" => $arGroup["OPENED"]), CSocNetUser::IsCurrentUserModuleAdmin());
					}

					$arCacheResult["Groups"]["List"][] = array(
						"GROUP_ID" => $arGroup["ID"],
						"GROUP_NAME" => $arGroup["NAME"],
						"GROUP_CLOSED" => $arGroup["CLOSED"],
						"GROUP_DESCRIPTION" => (mb_strlen($arGroup["DESCRIPTION"]) > 50 ? mb_substr($arGroup["DESCRIPTION"], 0, 50)."..." : $arGroup["DESCRIPTION"]),
						"GROUP_DESCRIPTION_FULL" => $arGroup["DESCRIPTION"],
						"GROUP_PHOTO" => $arGroup["IMAGE_ID"],
						"GROUP_PHOTO_FILE" => $arImage["FILE"],
						"GROUP_PHOTO_IMG" => $arImage["IMG"],
						"GROUP_PHOTO_RESIZED" => $arImageResized,
						"GROUP_PHOTO_RESIZED_COMMON" => $arImageResizedCommon,
						"GROUP_AVATAR_TYPE" => $arGroup['AVATAR_TYPE'],
						"GROUP_URL" => $pu,
						"GROUP_REQUEST_USER_URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_REQUEST_USER"], array("group_id" => $arGroup["ID"], "user_id" => $arResult["UserRequest"]["ID"])),
						"CAN_INVITE2GROUP" => (($arParams["PAGE"] !== "user_groups") ? $arCurrentUserPerms4Group && $arCurrentUserPerms4Group["UserCanInitiate"] : false),
						"FULL" => array(
							"DATE_CREATE_FORMATTED" => date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arGroup["DATE_CREATE"], CSite::GetDateFormat("FULL"))),
							"DATE_UPDATE_FORMATTED" => date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arGroup["DATE_UPDATE"], CSite::GetDateFormat("FULL"))),
							"DATE_ACTIVITY_FORMATTED" => date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arGroup["DATE_ACTIVITY"], CSite::GetDateFormat("FULL")))
						),
						"NUMBER_OF_MEMBERS" => $arGroup["NUMBER_OF_MEMBERS"],
					);
					$CACHE_MANAGER->registerTag("sonet_user2group_G".$arGroup["ID"]);

					$arGroupID[] = $arGroup["ID"];
				}

				if (
					!empty($arGroupID)
					&& CModule::IncludeModule("extranet")
					&& !CExtranet::IsExtranetSite()
				)
				{
					$arExtranetGroupID = array();
					$dbGroupTmp = CSocNetGroup::GetList(
						array(),
						array(
							"ID" => $arGroupID,
							"SITE_ID" => CExtranet::GetExtranetSiteID()
						),
						false,
						false,
						array("ID")
					);
					while ($arGroupTmp = $dbGroupTmp->Fetch())
					{
						$arExtranetGroupID[] = $arGroupTmp["ID"];
					}

					if (
						count($arExtranetGroupID) > 0
						&& is_array($arCacheResult["Groups"]["List"])
					)
					{
						foreach ($arCacheResult["Groups"]["List"] as $key => $arGroupTmp)
						{
							$arCacheResult["Groups"]["List"][$key]["IS_EXTRANET"] = (in_array($arGroupTmp["GROUP_ID"], $arExtranetGroupID) ? "Y" : "N");
						}
					}
				}

				$arAssets = array();

				$arCss = $APPLICATION->sPath2css;
				$arJs = $APPLICATION->arHeadScripts;

				ob_start();

				$APPLICATION->IncludeComponent(
					"bitrix:main.pagenavigation",
					"",
					array(
						"NAV_OBJECT" => $nav
					),
					false
				);

				$arCacheResult["NAV_STRING"] = ob_get_clean();

				$arAssets["CSS"] = array_diff($APPLICATION->sPath2css, $arCss);
				$arAssets["JS"] = array_diff($APPLICATION->arHeadScripts, $arJs);

				$arCacheResult["ASSETS"] = $arAssets;
			}
		}

		if ($arParams["CACHE_TIME"] > 0)
		{
			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->EndTagCache();
			}

			$groupCache->StartDataCache($arParams["CACHE_TIME"], $cacheId, $cachePath);
			$groupCache->EndDataCache(array("arCacheResult" => $arCacheResult));
		}
	}

	if ($arParams["PAGE"] === "user_projects")
	{
		$arResult["filter_project"] = "Y";
	}

	if (
		$USER->isAuthorized()
		&& isset($arCacheResult['Groups'])
		&& !empty($arCacheResult['Groups']['List'])
	)
	{
		$groupIdList = array();
		foreach ($arCacheResult['Groups']['List'] as $group)
		{
			$groupIdList[] = $group['GROUP_ID'];
		}

		if (!empty($groupIdList))
		{
			$favoritesGroupIdList = array();
			$res = \Bitrix\Socialnetwork\WorkgroupFavoritesTable::getList(array(
				'filter' => array(
					'@GROUP_ID' => $groupIdList,
					'USER_ID' => $USER->getId()
				),
				'select' => array('GROUP_ID')
			));
			while ($groupFavorites = $res->fetch())
			{
				$favoritesGroupIdList[] = $groupFavorites['GROUP_ID'];
			}

			if (!empty($favoritesGroupIdList))
			{
				foreach ($arCacheResult['Groups']['List'] as $key => $group)
				{
					$arCacheResult['Groups']['List'][$key]['IN_FAVORITES'] = (in_array($group['GROUP_ID'], $favoritesGroupIdList) ? 'Y' : 'N');
				}
			}
		}
	}

	$arResult = array_merge($arResult, $arCacheResult);

	if (
		$currentUser
		&& $arParams["PAGE"] === "groups_list"
		&& !empty($arResult["Groups"]["List"])
	)
	{
		$arGroupID = $arRelations = array();
		foreach ($arResult["Groups"]["List"] as $arGroup)
		{
			$arGroupID[] = $arGroup["GROUP_ID"];
		}

		if (!empty($arGroupID))
		{
			$res = UserToGroupTable::getList(array(
				'filter' => array(
					'USER_ID' => $arParams["USER_ID"],
					'@GROUP_ID' => $arGroupID
				),
				'select' => array('GROUP_ID', 'ROLE')
			));
			while ($relation = $res->fetch())
			{
				$arRelations[$relation['GROUP_ID']] = $relation;
			}
		}
		foreach ($arResult["Groups"]["List"] as $key => $arGroup)
		{
			$arResult["Groups"]["List"][$key]['ROLE'] = (isset($arRelations[$arGroup['GROUP_ID']]) ? $arRelations[$arGroup['GROUP_ID']]['ROLE'] : false);
		}
	}

	if ($arParams["SET_TITLE"] === "Y" || $arParams["SET_NAV_CHAIN"] !== "N")
	{
		if ($arParams["NAME_TEMPLATE"] == '')
		{
			$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
		}

		$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
			array("#NOBR#", "#/NOBR#"),
			array("", ""),
			$arParams["NAME_TEMPLATE"]
		);
		$bUseLogin = $arParams['SHOW_LOGIN'] !== "N" ? true : false;

		if ($arParams["PAGE"] === "group_request_group_search")
		{
			$arTmpUser = array(
				"NAME" => $arResult["UserRequest"]["~NAME"],
				"LAST_NAME" => $arResult["UserRequest"]["~LAST_NAME"],
				"SECOND_NAME" => $arResult["UserRequest"]["~SECOND_NAME"],
				"LOGIN" => $arResult["UserRequest"]["~LOGIN"],
			);
			$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
		}
		elseif ($arParams["PAGE"] === "user_groups")
		{
			$arTmpUser = array(
				"NAME" => $arResult["User"]["~NAME"],
				"LAST_NAME" => $arResult["User"]["~LAST_NAME"],
				"SECOND_NAME" => $arResult["User"]["~SECOND_NAME"],
				"LOGIN" => $arResult["User"]["~LOGIN"],
			);
			$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
		}

		if ($arParams["SET_TITLE"] === "Y")
		{
			if ($arResult["USE_UI_FILTER"])
			{
				$APPLICATION->SetTitle(Loc::getMessage("SONET_C36_PAGE_TITLE_COMMON"));
			}
			elseif (
				$arParams["PAGE"] === "user_groups"
				&& !empty($_REQUEST['IFRAME'])
				&& $_REQUEST['IFRAME'] === 'Y'
			)
			{
				$APPLICATION->SetTitle($strTitleFormatted.": ".Loc::getMessage("SONET_C36_PAGE_TITLE1"));
			}
			elseif ($arParams["PAGE"] === "group_request_group_search")
			{
				$APPLICATION->SetTitle($strTitleFormatted.": ".Loc::getMessage("SONET_C36_PAGE_TITLE"));
			}
			elseif ($arParams["PAGE"] === "user_groups")
			{
				if ($currentUser)
				{
					$APPLICATION->SetTitle(Loc::getMessage("SONET_C36_PAGE_TITLE2_1"));
				}
				else
				{
					$APPLICATION->SetTitle($strTitleFormatted.": ".Loc::getMessage("SONET_C36_PAGE_TITLE1"));
				}
			}
			elseif ($arParams["PAGE"] === "user_projects")
			{
				if ($currentUser)
				{
					$APPLICATION->SetTitle(Loc::getMessage("SONET_C36_PAGE_TITLE2_1_PROJECT"));
				}
				else
				{
					$APPLICATION->SetTitle(Loc::getMessage("SONET_C36_PAGE_TITLE1_PROJECT"));
				}
			}
			elseif (
				$arParams["PAGE"] === "groups_subject" &&
				is_array($arResult["Subject"])
			)
			{
				$APPLICATION->SetTitle($arResult["Subject"]["NAME"]);
			}
			elseif (isset($arResult["filter_my"]) && $arResult["filter_my"] === "Y")
			{
				$APPLICATION->SetTitle(Loc::getMessage($arResult["filter_project"] === "Y" ? "SONET_C36_PAGE_TITLE2_1_PROJECT" : "SONET_C36_PAGE_TITLE2_1"));
			}
			elseif (isset($arResult["filter_archive"]) && $arResult["filter_archive"] === "Y")
			{
				$APPLICATION->SetTitle(Loc::getMessage("SONET_C36_PAGE_TITLE2_2"));
			}
			elseif (isset($arResult["filter_extranet"]) && $arResult["filter_extranet"] === "Y")
			{
				$APPLICATION->SetTitle(Loc::getMessage("SONET_C36_PAGE_TITLE2_3"));
			}
			elseif (isset($arResult["filter_favorites"]) && $arResult["filter_favorites"] === "Y")
			{
				$APPLICATION->SetTitle(Loc::getMessage("SONET_C36_PAGE_TITLE2_4"));
			}
			else
			{
				$APPLICATION->SetTitle(Loc::getMessage($arResult["filter_project"] === "Y" ? "SONET_C36_PAGE_TITLE2_PROJECT" : "SONET_C36_PAGE_TITLE2"));
			}
		}

		if ($arParams["SET_NAV_CHAIN"] !== "N")
		{
			if ($arParams["PAGE"] === "group_request_group_search")
			{
				$APPLICATION->AddChainItem($strTitleFormatted, CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["UserRequest"]["ID"])));
				$APPLICATION->AddChainItem(Loc::getMessage("SONET_C36_PAGE_TITLE"));
			}
			elseif ($arParams["PAGE"] === "user_groups")
			{
				$APPLICATION->AddChainItem($strTitleFormatted, CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"])));
				$APPLICATION->AddChainItem(Loc::getMessage("SONET_C36_PAGE_TITLE1"));
			}
			else
			{
				if ($arResult["filter_my"] === "Y")
				{
					$APPLICATION->AddChainItem(Loc::getMessage("SONET_C36_PAGE_TITLE2_1"));
				}
				elseif ($arResult["filter_archive"] === "Y")
				{
					$APPLICATION->AddChainItem(Loc::getMessage("SONET_C36_PAGE_TITLE2_2"));
				}
				elseif ($arResult["filter_extranet"] === "Y")
				{
					$APPLICATION->AddChainItem(Loc::getMessage("SONET_C36_PAGE_TITLE2_3"));
				}
				else
				{
					$APPLICATION->AddChainItem(Loc::getMessage("SONET_C36_PAGE_TITLE2"));
				}
			}
		}
	}

	if (
		!$arResult["USE_UI_FILTER"]
		&& CSocNetUser::IsCurrentUserModuleAdmin()
		&& CModule::IncludeModule('intranet')
	)
	{
		global $INTRANET_TOOLBAR;

		$INTRANET_TOOLBAR->AddButton(array(
			'HREF' => "/bitrix/admin/socnet_subject.php?lang=".LANGUAGE_ID,
			"TEXT" => Loc::getMessage('SONET_C36_EDIT_ENTRIES'),
			'ICON' => 'settings',
			"SORT" => 1000,
		));
	}

}

$this->IncludeComponentTemplate();
