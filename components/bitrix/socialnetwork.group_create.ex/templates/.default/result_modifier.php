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

Loc::loadMessages(__FILE__);

$arResult['TypesProject'] = [];
$arResult['TypesNonProject'] = [];
$arResult['TypesAll'] = [];

$arResult['ClientConfig'] = [
	'refresh' => (empty($_REQUEST['refresh']) || $_REQUEST['refresh'] !== 'N' ? 'Y' : 'N')
];

if (
	is_array($arResult['Types'])
	&& !empty($arResult['Types'])
)
{
	foreach($arResult['Types'] as $code => $type)
	{
		$arResult['TypesAll'][$code] = $type;

		if ($type['PROJECT'] === 'Y')
		{
			$arResult['TypesProject'][$code] = $type;
		}
		else
		{
			$arResult['TypesNonProject'][$code] = $type;
		}
	}
}

$arResult['openAdditional'] = false;

$arResult["GROUP_PROPERTIES_MANDATORY"] = $arResult["GROUP_PROPERTIES_NON_MANDATORY"] = [];
if (
	is_array($arResult['GROUP_PROPERTIES'])
	&& !empty($arResult['GROUP_PROPERTIES'])
)
{
	foreach($arResult["GROUP_PROPERTIES"] as $key => $userField)
	{
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

$arResult['TypeRowNameList'] = [
	'TypesProject' => Loc::getMessage('SONET_GCE_T_TYPE_SUBTITLE_PROJECT'),
	'TypesNonProject' => Loc::getMessage('SONET_GCE_T_TYPE_SUBTITLE_GROUP'),
	'TypesAll' => '',
];

//$arResult['TypeRowList'] = [ 'TypesProject', 'TypesNonProject' ];
$arResult['TypeRowList'] = [ 'TypesAll' ];

$arResult['AVATAR_UPLOADER_CID'] = 'GROUP_IMAGE_ID';

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

if ($arParams["GROUP_ID"] > 0)
{
	$arResult['typeCode'] = \Bitrix\Socialnetwork\Item\Workgroup::getTypeCodeByParams([
		'typesList' => $arResult['Types'],
		'fields' => [
			'VISIBLE' => (isset($arResult['POST']['VISIBLE']) && $arResult['POST']['VISIBLE'] === 'Y' ? 'Y' : 'N'),
			'OPENED' => (isset($arResult['POST']['OPENED']) && $arResult['POST']['OPENED'] === 'Y' ? 'Y' : 'N'),
			'PROJECT' => (isset($arResult['POST']['PROJECT']) && $arResult['POST']['PROJECT'] === 'Y' ? 'Y' : 'N'),
			'EXTERNAL' => (isset($arResult['POST']['IS_EXTRANET_GROUP']) && $arResult['POST']['IS_EXTRANET_GROUP'] === 'Y' ? 'Y' : 'N')
		]
	]);
}
