<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Helper\Workgroup;

Loc::loadMessages(__FILE__);

$arResult['ClientConfig'] = [
	'refresh' => (empty($_REQUEST['refresh']) || $_REQUEST['refresh'] !== 'N' ? 'Y' : 'N')
];

$arResult['openAdditional'] = false;

$arResult["GROUP_PROPERTIES_MANDATORY"] = $arResult["GROUP_PROPERTIES_NON_MANDATORY"] = [];
if (
	is_array($arResult['GROUP_PROPERTIES'])
	&& !empty($arResult['GROUP_PROPERTIES'])
)
{
	foreach($arResult["GROUP_PROPERTIES"] as $key => $userField)
	{
		if ($key === 'UF_SG_DEPT')
		{
			continue;
		}

		if ($userField['MANDATORY'] === 'Y')
		{
			$arResult["GROUP_PROPERTIES_MANDATORY"][$key] = $userField;
		}
		else
		{
			$arResult["GROUP_PROPERTIES_NON_MANDATORY"][$key] = $userField;
		}
	}
}

if (
	$arParams["GROUP_ID"] <= 0
	&& $arResult["intranetInstalled"]
)
{
	$inactiveFeaturesList = [ 'forum', 'photo', 'search', 'group_lists', 'wiki' ];
	foreach($inactiveFeaturesList as $feature)
	{
		if (isset($arResult["POST"]["FEATURES"][$feature]))
		{
			$arResult["POST"]["FEATURES"][$feature]["Active"] = false;
		}
	}
}

if ($arParams['GROUP_ID'] > 0)
{
	$arResult['typeCode'] = Workgroup::getTypeCodeByParams([
		'typesList' => $arResult['Types'],
		'fields' => [
			'VISIBLE' => (isset($arResult['POST']['VISIBLE']) && $arResult['POST']['VISIBLE'] === 'Y' ? 'Y' : 'N'),
			'OPENED' => (isset($arResult['POST']['OPENED']) && $arResult['POST']['OPENED'] === 'Y' ? 'Y' : 'N'),
			'PROJECT' => (isset($arResult['POST']['PROJECT']) && $arResult['POST']['PROJECT'] === 'Y' ? 'Y' : 'N'),
			'EXTERNAL' => (isset($arResult['POST']['IS_EXTRANET_GROUP']) && $arResult['POST']['IS_EXTRANET_GROUP'] === 'Y' ? 'Y' : 'N'),
		]
	]);
}

switch ($arResult['preset'])
{
	case 'project-open':
		$arResult['selectedProjectType'] = 'project';
		$arResult['selectedConfidentialityType'] = 'open';
		break;
	case 'project-closed':
	case 'project-external':
		$arResult['selectedProjectType'] = 'project';
		$arResult['selectedConfidentialityType'] = 'secret';
		break;
	case 'project-scrum':
		$arResult['selectedProjectType'] = 'scrum';
		$arResult['selectedConfidentialityType'] = 'secret';
		break;
	case 'group-landing':
		$arResult['selectedProjectType'] = 'group';
		$arResult['selectedConfidentialityType'] = 'secret';
		break;
	default:
		$arResult['selectedProjectType'] = '';
		$arResult['selectedConfidentialityType'] = '';
}

$arResult['POST']['IMAGE_SRC'] = '';

if ((int) ($arResult['POST']['IMAGE_ID'] ?? 0) > 0)
{
	if ($fileTmp = \CFile::resizeImageGet(
		(int)$arResult['POST']['IMAGE_ID'],
		[
			'width' => 300,
			'height' => 300,
		],
		BX_RESIZE_IMAGE_PROPORTIONAL,
		false,
		false,
		true,
	))
	{
		$arResult['POST']['IMAGE_SRC'] = $fileTmp['src'];
	}
}

$arResult['avatarTypesList'] = Workgroup::getDefaultAvatarTypes();
