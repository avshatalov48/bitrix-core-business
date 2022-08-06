<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */

global $CACHE_MANAGER, $USER, $APPLICATION;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

\Bitrix\Main\UI\Extension::load('ui.design-tokens');

$arFilterKeys = array("filter_my", "filter_archive", "filter_extranet", "filter_project");

$arResult['menuItems'] = array();
$myTabActive = $myProjectsTabActive = false;

if (
	!$arResult["bExtranet"]
	&& $USER->IsAuthorized()
)
{
	if ($arResult['USE_PROJECTS'] === 'Y')
	{
		$myProjectsTabActive = (
			$arParams["PAGE"] === 'user_projects'
			|| (
				$arParams["PAGE"] === 'groups_list'
				&& $arResult["filter_my"]
				&& isset($arResult["filter_project"])
				&& $arResult["filter_project"] === 'Y'
			)
		);
		$arResult["menuItems"][] = array(
			"TEXT" => Loc::getMessage("SONET_C33_T_F_MY_PROJECT"),
			"URL" => (
				$arResult["WORKGROUPS_PATH"] <> ''
					? $arResult["WORKGROUPS_PATH"]."?filter_my=Y&filter_project=Y"
					: $APPLICATION->GetCurPageParam("filter_my=Y&filter_project=Y", $arFilterKeys, false)
			),
			"ID" => "workgroups_my_projects",
			"IS_ACTIVE" => $myProjectsTabActive
		);
	}

	$myTabActive = (
		$arParams["PAGE"] === 'user_groups'
		|| (
			$arParams["PAGE"] === 'groups_list'
			&& $arResult["filter_my"]
			&& (
				$arResult["USE_PROJECTS"] !== 'Y'
				|| !isset($arResult["filter_project"])
				|| $arResult["filter_project"] !== 'Y'
			)
		)
	);

	$arResult["menuItems"][] = array(
		"TEXT" => Loc::getMessage("SONET_C33_T_F_MY"),
		"URL" => (
			$arResult["WORKGROUPS_PATH"] <> ''
				? $arResult["WORKGROUPS_PATH"]."?filter_my=Y".($arResult['USE_PROJECTS'] == 'Y' ? '&filter_project=N' : '')
				: $APPLICATION->GetCurPageParam("filter_my=Y".($arResult['USE_PROJECTS'] == 'Y' ? '&filter_project=N' : ''), $arFilterKeys, false)
		),
		"ID" => "workgroups_my",
		"IS_ACTIVE" => $myTabActive
	);
}

if ($arResult['USE_PROJECTS'] == 'Y')
{
	$arResult["menuItems"][] = array(
		"TEXT" => Loc::getMessage("SONET_C36_T_F_ALL_PROJECT"),
		"URL" => (
			$arResult["WORKGROUPS_PATH"] <> ''
				? $arResult["WORKGROUPS_PATH"]."?filter_project=Y"
				: $APPLICATION->GetCurPageParam("filter_project=Y", $arFilterKeys, false)
		),
		"ID" => "workgroups_all_projects",
		"IS_ACTIVE" => (
			!$myTabActive
			&& !$myProjectsTabActive
			&& !$arResult["filter_my"]
			&& isset($arResult["filter_project"])
			&& $arResult["filter_project"] === 'Y'
			&& !$arResult["filter_archive"]
			&& !$arResult["filter_extranet"]
			&& !$arResult["filter_tags"]
			&& !$arResult["filter_favorites"]
		)
	);
}

$arResult["menuItems"][] = array(
	"TEXT" => Loc::getMessage("SONET_C36_T_F_ALL"),
	"URL" => (
		$arResult["WORKGROUPS_PATH"] <> ''
			? $arResult["WORKGROUPS_PATH"].($arResult['USE_PROJECTS'] === 'Y' ? '?filter_project=N' : '')
			: $APPLICATION->GetCurPageParam("filter_project=N", $arFilterKeys, false)
	),
	"ID" => "workgroups_all",
	"IS_ACTIVE" => (
		!$myTabActive
		&& !$myProjectsTabActive
		&& !$arResult["filter_my"]
		&& (
			!isset($arResult["filter_project"])
			|| $arResult["filter_project"] !== 'Y'
		)
		&& !$arResult["filter_archive"]
		&& !$arResult["filter_extranet"]
		&& !$arResult["filter_tags"]
		&& !$arResult["filter_favorites"]
	)
);

if ($USER->IsAuthorized())
{
	$arResult["menuItems"][] = array(
		"TEXT" => Loc::getMessage("SONET_C36_T_F_FAVORITES"),
		"URL" => (
			$arResult["WORKGROUPS_PATH"] <> ''
				? $arResult["WORKGROUPS_PATH"]."?filter_favorites=Y"
				: $APPLICATION->GetCurPageParam("filter_favorites=Y", $arFilterKeys, false)
		),
		"ID" => "workgroups_favorites",
		"IS_ACTIVE" => $arResult["filter_favorites"]
	);
}

if (COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y")
{
	$arResult["menuItems"][] = array(
		"TEXT" => Loc::getMessage("SONET_C33_T_F_ARCHIVE"),
		"URL" => (
			$arResult["WORKGROUPS_PATH"] <> ''
				? $arResult["WORKGROUPS_PATH"]."?filter_archive=Y"
				: $APPLICATION->GetCurPageParam("filter_archive=Y", $arFilterKeys, false)
		),
		"ID" => "workgroups_archive",
		"IS_ACTIVE" => $arResult["filter_archive"]
	);
}

if (
	ModuleManager::isModuleInstalled("extranet")
	&& !$arResult["bExtranet"]
)
{
	$arResult["menuItems"][] = array(
		"TEXT" => Loc::getMessage("SONET_C33_T_F_EXTRANET"),
		"URL" => (
			$arResult["WORKGROUPS_PATH"] <> ''
				? $arResult["WORKGROUPS_PATH"]."?filter_extranet=Y"
				: $APPLICATION->GetCurPageParam("filter_extranet=Y", $arFilterKeys, false)
		),
		"ID" => "workgroups_extranet",
		"IS_ACTIVE" => $arResult["filter_extranet"]
	);
}
if (
	$arParams["USE_KEYWORDS"] !== "N"
	&& $arResult["USE_PROJECTS"] !== "Y"
	&& ModuleManager::isModuleInstalled("search")
)
{
	$arResult["menuItems"][] = array(
		"TEXT" => Loc::getMessage("SONET_C33_T_F_TAGS"),
		"URL" => (
			$arResult["WORKGROUPS_PATH"] <> ''
				? $arResult["WORKGROUPS_PATH"]."?filter_tags=Y"
				: $APPLICATION->GetCurPageParam("filter_tags=Y", $arFilterKeys, false)
		),
		"ID" => "workgroups_tags",
		"IS_ACTIVE" => $arResult["filter_tags"]
	);
}

$arResult["menuId"] = "sonetgroups_panel_menu";

$arResult['SIDEBAR_GROUPS'] = [];

if (
	SITE_TEMPLATE_ID === "bitrix24"
	&& $USER->isAuthorized()
	&& $arParams["USER_ID"] == $USER->getId()
	&& (
		!\Bitrix\Main\Loader::includeModule('extranet')
		|| !CExtranet::IsExtranetSite()
	)
)
{
	$count = 10;

	$lastViewCache = \Bitrix\Main\Data\Cache::createInstance();
	$cacheTtl = 60*60*24*365;
	$cacheId = 'user_groups_date_view'.SITE_ID.'_'.$arParams["USER_ID"].$count;
	$cacheDir = '/sonet/user_group_date_view/'.SITE_ID.'/'.$arParams["USER_ID"];

	$lastViewGroupsList = [];

	if($lastViewCache->startDataCache($cacheTtl, $cacheId, $cacheDir))
	{
		$query = new \Bitrix\Main\Entity\Query(\Bitrix\Socialnetwork\WorkgroupTable::getEntity());

		$query->registerRuntimeField(
			'',
			new ReferenceField('UG',
				\Bitrix\Socialnetwork\UserToGroupTable::getEntity(),
				[
					'=ref.GROUP_ID' => 'this.ID',
					'=ref.USER_ID' =>  new SqlExpression($arParams["USER_ID"])
				],
				array('join_type' => 'LEFT')
			)
		);
		$query->registerRuntimeField(
			'',
			new ReferenceField('GV',
				\Bitrix\Socialnetwork\WorkgroupViewTable::getEntity(),
				array(
					'=ref.GROUP_ID' => 'this.ID',
					'=ref.USER_ID' =>  new SqlExpression($arParams["USER_ID"])
				),
				array('join_type' => 'INNER')
			)
		);
		$query->registerRuntimeField(
			'',
			new ReferenceField('GS',
				\Bitrix\Socialnetwork\WorkgroupSiteTable::getEntity(),
				array(
					'=ref.GROUP_ID' => 'this.ID'
				),
				array('join_type' => 'INNER')
			)
		);
		$query->addOrder('GV.DATE_VIEW', 'DESC');

		$query->addFilter('=GS.SITE_ID', SITE_ID);
		$query->addFilter(null, array(
			'LOGIC' => 'OR',
			'=VISIBLE' => 'Y',
			'<=UG.ROLE' => \Bitrix\Socialnetwork\UserToGroupTable::ROLE_USER
		));

		$query->addSelect('ID');
		$query->addSelect('NAME');
		$query->addSelect('DESCRIPTION');
		$query->addSelect('IMAGE_ID');
		$query->addSelect('AVATAR_TYPE');

		$query->countTotal(false);
		$query->setOffset(0);
		$query->setLimit($count);

		$res = $query->exec();

		if ($res)
		{
			$groupIdList = array();
			while ($group = $res->fetch())
			{
				$groupIdList[] = $group['ID'];

				$group["NAME"] = htmlspecialcharsEx($group["NAME"]);
				$group['URL'] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $group["ID"]));
				$group["DESCRIPTION"] = (mb_strlen($group["DESCRIPTION"]) > 47 ? mb_substr($group["DESCRIPTION"], 0, 47)."..." : $group["DESCRIPTION"]);
				$group["DESCRIPTION"] = htmlspecialcharsEx($group["DESCRIPTION"]);

				$imageResized = false;

				if ((int)$group["IMAGE_ID"] > 0)
				{
					$imageFile = \CFile::getFileArray($group["IMAGE_ID"]);
					if ($imageFile !== false)
					{
						$imageResized = \CFile::resizeImageGet(
							$imageFile,
							array("width" => $arParams["THUMBNAIL_SIZE_COMMON"], "height" => $arParams["THUMBNAIL_SIZE_COMMON"]),
							BX_RESIZE_IMAGE_EXACT
						);
					}
				}
				$group['IMAGE_RESIZED'] = $imageResized;

				$lastViewGroupsList[] = $group;
			}

			// get extranet info
			if (
				!empty($groupIdList)
				&& Bitrix\Main\Loader::includeModule('extranet')
				&& ($extranetSiteId = CExtranet::getExtranetSiteID())
			)
			{
				$groupSiteList = array();
				$resSite = \Bitrix\Socialnetwork\WorkgroupSiteTable::getList(array(
					'filter' => array(
						'@GROUP_ID' => $groupIdList
					),
					'select' => array('GROUP_ID', 'SITE_ID')
				));
				while ($groupSite = $resSite->fetch())
				{
					if (!isset($groupSiteList[$groupSite['GROUP_ID']]))
					{
						$groupSiteList[$groupSite['GROUP_ID']] = array();
					}
					$groupSiteList[$groupSite['GROUP_ID']][] = $groupSite['SITE_ID'];
				}

				foreach($lastViewGroupsList as $key => $group)
				{
					$lastViewGroupsList[$key]['IS_EXTRANET'] = (in_array($extranetSiteId, $groupSiteList[$group['ID']]) ? 'Y' : 'N');
				}
			}
		}

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->startTagCache($cacheDir);
			$CACHE_MANAGER->registerTag("sonet_group_view_U".$arParams["USER_ID"]);
			$CACHE_MANAGER->registerTag("sonet_user2group_U".$arParams["USER_ID"]);
			$CACHE_MANAGER->registerTag("sonet_group");
			$CACHE_MANAGER->endTagCache();
		}

		$lastViewCache->endDataCache(array("SIDEBAR_GROUPS" => $lastViewGroupsList));
	}
	else
	{
		$cacheResult = $lastViewCache->getVars();
		$lastViewGroupsList = $cacheResult['SIDEBAR_GROUPS'];
	}

	$arResult['SIDEBAR_GROUPS'] = $lastViewGroupsList;
}
