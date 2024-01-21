<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Lists\Api\Service\ServiceFactory\ServiceFactory;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;

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

$arResult = [];

$currentUserId = (int)$USER->GetID();
$iBlockTypeId = (string)$arParams['~IBLOCK_TYPE_ID'];
$socNetGroupId =
	isset($arParams['SOCNET_GROUP_ID']) && is_numeric($arParams['SOCNET_GROUP_ID'])
		? (int)$arParams['SOCNET_GROUP_ID']
		: 0
;

$title =
	$arParams['IBLOCK_TYPE_ID'] === Option::get('lists', 'livefeed_iblock_type_id')
		? Loc::getMessage('CC_BLL_TITLE_TEXT_CLAIM')
		: Loc::getMessage('CC_BLL_TITLE_TEXT_LISTS')
;

$APPLICATION->SetTitle($title);

if (
	$socNetGroupId > 0
	&& Loader::includeModule('socialnetwork')
	&& method_exists(Bitrix\Socialnetwork\ComponentHelper::class, 'getWorkgroupPageTitle')
)
{
	$APPLICATION->SetPageProperty('title', \Bitrix\Socialnetwork\ComponentHelper::getWorkgroupPageTitle([
		'WORKGROUP_ID' => $socNetGroupId,
		'TITLE' => $title
	]));
}

if(!Loader::includeModule('lists'))
{
	ShowError(Loc::getMessage('CC_BLL_MODULE_NOT_INSTALLED'));

	return;
}

$service = ServiceFactory::getServiceByIBlockTypeId($iBlockTypeId, $currentUserId, $socNetGroupId);
if (!$service)
{
	ShowError(Loc::getMessage('CC_BLL_WRONG_IBLOCK_TYPE'));

	return;
}

$checkPermissionResult = $service->checkIBlockTypePermission();
$lists_perm = $checkPermissionResult->getPermission();

if (!$checkPermissionResult->isSuccess())
{
	ShowError($checkPermissionResult->getErrorMessages()[0]);

	return;
}

if ($lists_perm <= CListPermissions::ACCESS_DENIED)
{
	ShowError(Loc::getMessage('CC_BLL_ACCESS_DENIED'));

	return;
}

$arParams['CAN_EDIT'] = $lists_perm >= CListPermissions::IS_ADMIN;
$arParams['SOCNET_GROUP_ID'] = $socNetGroupId > 0 ? $socNetGroupId : '';

$arResult['~LISTS_URL'] = str_replace(
	['#list_id#', '#group_id#'],
	['0', $arParams['SOCNET_GROUP_ID']],
	$arParams['~LISTS_URL']
);
$arResult['LISTS_URL'] = htmlspecialcharsbx($arResult['~LISTS_URL']);

$arResult['~LIST_EDIT_URL'] = str_replace(
	['#list_id#', '#group_id#'],
	['0', $arParams['SOCNET_GROUP_ID']],
	$arParams['~LIST_EDIT_URL']
);
$arResult['LIST_EDIT_URL'] = htmlspecialcharsbx($arResult['~LIST_EDIT_URL']);

global $CACHE_MANAGER;
if($this->StartResultCache(0/*disable cache because it's individual for each user*/, $USER->GetUserGroupArray()))
{
	$CACHE_MANAGER->StartTagCache($this->GetCachePath());
	$CACHE_MANAGER->RegisterTag('lists_list_any');

	$arResult['ITEMS'] = [];
	$getCatalogResult = $service->getCatalog();
	if ($getCatalogResult->isSuccess())
	{
		$rsLists = $getCatalogResult->getCatalog();
		foreach($rsLists as $ar)
		{
			$ar['~LIST_URL'] =
				(new Uri(
					str_replace(
						['#list_id#', '#section_id#', '#group_id#'],
						[$ar['ID'], '0', $arParams['SOCNET_GROUP_ID']],
						$arParams['~LIST_URL']
					)
				))
					->addParams(['list_section_id' => ''])
					->getUri()
			;
			$ar['LIST_URL'] = htmlspecialcharsbx($ar['~LIST_URL']);

			$ar['~LIST_EDIT_URL'] = str_replace(
				['#list_id#', '#group_id#'],
				[$ar['ID'], $arParams['SOCNET_GROUP_ID']],
				$arParams['~LIST_EDIT_URL']
			);
			$ar['LIST_EDIT_URL'] = htmlspecialcharsbx($ar['~LIST_EDIT_URL']);

			$arResult['ITEMS'][] = $ar;
		}
	}

	$CACHE_MANAGER->EndTagCache();
	$this->IncludeComponentTemplate();
}
