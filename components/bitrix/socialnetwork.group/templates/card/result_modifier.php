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
/** @global CUserTypeManager $USER_FIELD_MANAGER */

use Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker;
use Bitrix\Intranet\Internals\ThemeTable;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Integration;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Helper;

global $USER_FIELD_MANAGER;

Loc::loadMessages(__FILE__);

CSocNetLogComponent::processDateTimeFormatParams($arParams);

$arResult["Urls"]["Delete"] = CComponentEngine::MakePathFromTemplate(
	$arParams["PATH_TO_GROUP_DELETE"],
	[
		"group_id" => $arResult["Group"]["ID"],
	]
);

$arResult["FAVORITES"] = false;
if ($USER->IsAuthorized())
{
	$res = \Bitrix\Socialnetwork\WorkgroupFavoritesTable::getList([
		'filter' => [
			'GROUP_ID' => $arResult["Group"]["ID"],
			'USER_ID' => $USER->getId(),
		],
	]);
	$arResult["FAVORITES"] = ($res->fetch());
}

$arResult["Types"] = Helper\Workgroup::getTypes([
	'currentExtranetSite' => $arResult["bExtranet"],
]);

$typeFields = [
	'PROJECT' => (isset($arResult["Group"]['PROJECT']) && $arResult["Group"]['PROJECT'] === 'Y' ? 'Y' : 'N'),
	'EXTERNAL' => (isset($arResult["Group"]["IS_EXTRANET_GROUP"]) && $arResult["Group"]["IS_EXTRANET_GROUP"] === 'Y' ? 'Y' : 'N'),
	'SCRUM_PROJECT' => ($arResult['isScrumProject'] ? 'Y' : 'N'),
];
if (!$arResult['isScrumProject'])
{
	$typeFields['OPENED'] = (isset($arResult["Group"]['OPENED']) && $arResult["Group"]['OPENED'] === 'Y' ? 'Y' : 'N');
	$typeFields['VISIBLE'] = (isset($arResult["Group"]['VISIBLE']) && $arResult["Group"]['VISIBLE'] === 'Y' ? 'Y' : 'N');
}

$arResult["groupTypeCode"] = Helper\Workgroup::getTypeCodeByParams([
	'fields' => $typeFields,
]);

$arResult["Group"]["IS_EXTRANET_GROUP"] = (
	Loader::includeModule("extranet")
	&& CExtranet::isExtranetSocNetGroup($arResult["Group"]["ID"])
		? "Y"
		: "N"
);

$arResult["Group"]["KEYWORDS_LIST"] = [];
if (
	isset($arResult["Group"]["KEYWORDS"])
	&& $arResult["Group"]["KEYWORDS"] <> ''
)
{
	$arResult["Group"]["KEYWORDS_LIST"] = explode(',', $arResult["Group"]["KEYWORDS"]);
	foreach($arResult["Group"]["KEYWORDS_LIST"] as $key => $val)
	{
		$val = trim($val);
		if ($val !== '')
		{
			$arResult["Group"]["KEYWORDS_LIST"][$key] = $val;
		}
		else
		{
			unset($arResult["Group"]["KEYWORDS_LIST"][$key]);
		}
	}
}

$arParams["PATH_TO_GROUPS_LIST"] = ComponentHelper::getWorkgroupSEFUrl();
$arParams["PATH_TO_GROUP_TAG"] = $arParams["PATH_TO_GROUPS_LIST"].(mb_strpos($arParams["PATH_TO_GROUPS_LIST"], '?') !== false ? '&' : '?')."TAG=#tag#&apply_filter=Y";

if (empty($arResult["Urls"]["GroupsList"]))
{
	$arResult["Urls"]["GroupsList"] = CComponentEngine::MakePathFromTemplate(
		$arParams["PATH_TO_GROUPS_LIST"],
		[ "user_id" => $USER->getId() ]
	);
}

$arParams['USER_LIMIT'] = 3;

if (
	!empty($arResult['Group'])
	&& !empty($arResult['Group']['DESCRIPTION'])
)
{
	$arResult['Group']['DESCRIPTION'] = str_replace("\n", "<br />", $arResult['Group']['DESCRIPTION']);
}

$arResult['TASKS_EFFICIENCY'] = null;
if (
	$arResult["Group"]['PROJECT'] === 'Y'
	&& !$arResult['isScrumProject']
	&& Loader::includeModule('tasks')
)
{
	$arResult['TASKS_EFFICIENCY'] = \Bitrix\Tasks\Internals\Effective::getAverageEfficiency(
		null,
		null,
		0,
		(int)$arResult['Group']['ID']
	);
}

$arResult['themePickerData'] = [];
if (Loader::includeModule('intranet'))
{
	$themePicker = new ThemePicker(SITE_TEMPLATE_ID, false, $USER->getId(), ThemePicker::ENTITY_TYPE_SONET_GROUP, $arResult['Group']['ID']);
	$themeId = $themePicker->getCurrentThemeId();
	$themeUserId = false;
	if ($themeId)
	{
		$res = ThemeTable::getList([
			'filter' => [
				'=ENTITY_TYPE' => $themePicker->getEntityType(),
				'ENTITY_ID' => $themePicker->getEntityId(),
				'=CONTEXT' => $themePicker->getContext(),
			],
			'select' => [ 'USER_ID' ],
		]);
		if (
			($themeFields = $res->fetch())
			&& (int)$themeFields['USER_ID'] > 0
		)
		{
			$themeUserId = (int)$themeFields['USER_ID'];
		}
	}

	$arResult['themePickerData'] = $themePicker->getTheme($themeId, $themeUserId);
}

$arResult['GROUP_PROPERTIES'] = (
	!empty($arResult['Group'])
		? $USER_FIELD_MANAGER->getUserFields('SONET_GROUP', $arResult['Group']['ID'], LANGUAGE_ID)
		: []
);

foreach ($arResult['GROUP_PROPERTIES'] as $field => $userFieldFata)
{
	if (
		!empty($userFieldFata['SHOW_IN_LIST'])
		&& $userFieldFata['SHOW_IN_LIST'] === 'N'
		&& (
			empty($userFieldFata['MANDATORY'])
			|| $userFieldFata['MANDATORY'] !== 'Y'
		)
	)
	{
		unset($arResult['GROUP_PROPERTIES'][$field]);
		continue;
	}

	$arResult['GROUP_PROPERTIES'][$field]['LIST_COLUMN_LABEL'] = (
		(string)$userFieldFata['LIST_COLUMN_LABEL'] !== ''
			? $userFieldFata['LIST_COLUMN_LABEL']
			: $userFieldFata['FIELD_NAME']
	);
}

if ($arResult['IS_IFRAME'])
{
	$APPLICATION->setTitle('');
}
