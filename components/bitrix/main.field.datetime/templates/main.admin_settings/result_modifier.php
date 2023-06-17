<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\DateType;

/**
 * @var $component DateTimeUfComponent
 */

$component = $this->getComponent();

if (isset($arResult['additionalParameters']['bVarsFromForm']) && $arResult['additionalParameters']['bVarsFromForm'])
{
	$type = $GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE']['TYPE'] ?? '';
	$defaultDateTime = $GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE']['VALUE'] ?? '';
	$useSeconds = (
		isset($GLOBALS[$arResult['additionalParameters']['NAME']]['USE_SECOND'])
		&& $GLOBALS[$arResult['additionalParameters']['NAME']]['USE_SECOND'] === 'N'
			? 'N'
			: 'Y'
	);

	$useTimezone = (
		isset($GLOBALS[$arResult['additionalParameters']['NAME']]['USE_TIMEZONE'])
		&& $GLOBALS[$arResult['additionalParameters']['NAME']]['USE_TIMEZONE'] === 'N'
			? 'N'
			: 'Y'
	);
}
elseif (
	isset($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
	&& is_array($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
)
{
	$type = $arResult['userField']['SETTINGS']['DEFAULT_VALUE']['TYPE'] ?? '';
	$defaultDateTime = str_replace(
		' 00:00:00',
		'',
		CDatabase::FormatDate(
			$arResult['userField']['SETTINGS']['DEFAULT_VALUE']['VALUE'] ?? '',
			'YYYY-MM-DD HH:MI:SS',
			CLang::GetDateFormat(DateType::FORMAT_TYPE_FULL)
		)
	);
	$useSeconds = (
		isset($arResult['userField']['SETTINGS']['USE_SECOND'])
		&& $arResult['userField']['SETTINGS']['USE_SECOND'] === 'N'
			? 'N'
			: 'Y'
	);

	$useTimezone = (
		isset($arResult['userField']['SETTINGS']['USE_TIMEZONE'])
		&& $arResult['userField']['SETTINGS']['USE_TIMEZONE'] === 'N'
			? 'N'
			: 'Y'
	);
}
else
{
	$type = DateType::TYPE_NONE;
	$defaultDateTime = '';
	$useSeconds = 'Y';
	$useTimezone = 'N';
}

$arResult['type'] = $type;
$arResult['defaultDateTime'] = $defaultDateTime;
$arResult['useSeconds'] = $useSeconds;
$arResult['useTimezone'] = $useTimezone;