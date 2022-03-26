<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Helper\Path;

/** @var SocialnetworkGroupUsersEx $this */
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

$arResult["IS_IFRAME"] = ($_REQUEST["IFRAME"] === "Y");

$arParams["GROUP_ID"] = (int)$arParams["GROUP_ID"];

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] === "N" ? "N" : "Y");
$arParams["USE_AUTO_MEMBERS"] = ($arParams["USE_AUTO_MEMBERS"] === "Y" ? "Y" : "N");

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
if ($arParams["PATH_TO_USER"] == '')
{
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
}
$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if ($arParams["PATH_TO_GROUP"] == '')
{
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");
}
$arParams["PATH_TO_GROUP_EDIT"] = trim($arParams["PATH_TO_GROUP_EDIT"]);
if ($arParams["PATH_TO_GROUP_EDIT"] == '')
{
	$arParams["PATH_TO_GROUP_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_edit&".$arParams["GROUP_VAR"]."=#group_id#");
}

$arParams["PATH_TO_GROUP_INVITE"] = trim($arParams["PATH_TO_GROUP_INVITE"]);
if (empty($arParams["PATH_TO_GROUP_INVITE"]))
{
	$parent = $this->getParent();
	if (is_object($parent) && $parent->__name <> '')
	{
		$arParams["PATH_TO_GROUP_INVITE"] = $parent->arResult["PATH_TO_GROUP_INVITE"];
	}
}

$arParams["PATH_TO_CONPANY_DEPARTMENT"] = trim($arParams["PATH_TO_CONPANY_DEPARTMENT"]);
if ($arParams["PATH_TO_CONPANY_DEPARTMENT"] === '')
{
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = Path::get('department_path_template');
}

$arParams["ITEMS_COUNT"] = (int)$arParams["ITEMS_COUNT"];
if ($arParams["ITEMS_COUNT"] <= 0)
{
	$arParams["ITEMS_COUNT"] = 20;
}

$arParams["THUMBNAIL_LIST_SIZE"] = (int)$arParams["THUMBNAIL_LIST_SIZE"];
if ($arParams["THUMBNAIL_LIST_SIZE"] <= 0)
{
	$arParams["THUMBNAIL_LIST_SIZE"] = 42;
}

$arParams['NAME_TEMPLATE'] = $arParams['NAME_TEMPLATE'] ?: GetMessage("SONET_GUE_NAME_TEMPLATE_DEFAULT");
$arParams["NAME_TEMPLATE_WO_NOBR"] = str_replace(
	array("#NOBR#", "#/NOBR#"),
	array("", ""),
	$arParams["NAME_TEMPLATE"]
);

$arResult["bIntranetInstalled"] = ModuleManager::isModuleInstalled('intranet');
$arResult['isExtranetInstalled'] = ModuleManager::isModuleInstalled('extranet');
$arResult["bIntranetIncluded"] = ($arResult["bIntranetInstalled"] && Loader::includeModule('intranet'));
$arResult['isExtranetIncluded'] = ($arResult['isExtranetInstalled'] && Loader::includeModule('extranet'));

$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);

if ($arGroup["CLOSED"] === "Y" && COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") !== "Y")
{
	$arResult["HideArchiveLinks"] = true;
}

$arParams["GROUP_USE_BAN"] =
	$arParams["GROUP_USE_BAN"] !== "N"
	&& (
		!$arResult['isExtranetIncluded']
		|| (!CExtranet::IsExtranetSite() && !$arResult["HideArchiveLinks"])
	)
		? "Y"
		: "N";

$arResult['isScrumProject'] = false;

if (
	!$arGroup
	|| !is_array($arGroup)
	|| $arGroup["ACTIVE"] !== "Y"
)
{
	$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP").". ";
}
else
{
	CSocNetTools::InitGlobalExtranetArrays();

	$arGroup['IS_EXTRANET_GROUP'] = (
		$arResult['isExtranetIncluded']
		&& CExtranet::isExtranetSocNetGroup($arGroup['ID'])
			? 'Y'
			: 'N'
	);

	$arGroupSites = array();
	$rsGroupSite = CSocNetGroup::GetSite($arGroup["ID"]);
	while ($arGroupSite = $rsGroupSite->Fetch())
	{
		$arGroupSites[] = $arGroupSite["LID"];
	}

	if (!in_array(SITE_ID, $arGroupSites, true))
	{
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_GROUP");
	}
	else
	{
		$arResult["Group"] = $arGroup;

		$group = Bitrix\Socialnetwork\Item\Workgroup::getById($arResult['Group']['ID']);
		$arResult['isScrumProject'] = $group && $group->isScrumProject();

		$arResult["CurrentUserPerms"] = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
			'groupId' => $arGroup['ID'],
		]);

		if (!$arResult["CurrentUserPerms"] || !$arResult["CurrentUserPerms"]["UserCanViewGroup"])
		{
			$arResult["FatalError"] = GetMessage($arResult["Group"]["PROJECT"] === 'Y' ? "SONET_GUE_NO_PERMS_PROJECT" : "SONET_GUE_NO_PERMS").". ";
		}
		else
		{
			$arNavParams = array("nPageSize" => $arParams["ITEMS_COUNT"], "bDescPageNumbering" => false, "bShowAll"=>false);

			$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["GroupInvite"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_INVITE"], array("group_id" => $arResult["Group"]["ID"]));

			$pageTitle = Loc::getMessage('SONET_GUE_PAGE_TITLE');
			if ($arResult['isScrumProject'])
			{
				$pageTitle = Loc::getMessage('SONET_GUE_PAGE_TITLE_SCRUM');
			}
			elseif ($arResult["Group"]["PROJECT"] === 'Y')
			{
				$pageTitle = Loc::getMessage('SONET_GUE_PAGE_TITLE_PROJECT');
			}

			$strTitleFormatted = $arResult["Group"]["NAME"];

			if ($arParams["SET_TITLE"] === "Y")
			{
				if ($arResult['IS_IFRAME'])
				{
					$APPLICATION->SetTitle($pageTitle);
					$APPLICATION->SetPageProperty("PageSubtitle", $strTitleFormatted);
				}
				else
				{
					$APPLICATION->SetTitle($strTitleFormatted.": ".$pageTitle);
				}
			}

			if ($arParams["SET_NAV_CHAIN"] !== "N")
			{
				$APPLICATION->AddChainItem($strTitleFormatted, $arResult["Urls"]["Group"]);
				$APPLICATION->AddChainItem($pageTitle);
			}

			$arResult["Departments"] = array();
			if (
				!empty($arResult["Group"]["UF_SG_DEPT"])
				&& is_array($arResult["Group"]["UF_SG_DEPT"])
				&& $arResult["bIntranetIncluded"]
			)
			{
				$arDepartments = CIntranetUtils::GetDepartmentsData($arResult["Group"]["UF_SG_DEPT"]);
				if (!empty($arDepartments))
				{
					$arResult["Departments"]["List"] = array();
					foreach($arDepartments as $departmentId => $departmentName)
					{
						$arResult["Departments"]["List"][] = array(
							"ID" => $departmentId,
							"NAME" => $departmentName,
							"URL" => str_replace('#ID#', $departmentId, $arParams["PATH_TO_CONPANY_DEPARTMENT"])
						);
					}
				}
			}

			$arResult["Users"] = $this->getUserList("Users", $arParams, $arResult, $arNavParams);
			$arResult["UsersAuto"] = $this->getUserList("UsersAuto", $arParams, $arResult, $arNavParams);
			$arResult["Moderators"] = $this->getUserList("Moderators", $arParams, $arResult, $arNavParams);
			$arResult["Ban"] = $this->getUserList("Ban", $arParams, $arResult, $arNavParams);
		}
	}
}

$this->IncludeComponentTemplate();
