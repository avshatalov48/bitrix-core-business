<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UserField\Types\DateType;

/**
 * @var $component DateTimeUfComponent
 */

$component = $this->getComponent();

if($arResult['additionalParameters']['bVarsFromForm'])
{
	$type =
		$GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE']['TYPE'];
	$defaultDateTime =
		$GLOBALS[$arResult['additionalParameters']['NAME']]['DEFAULT_VALUE']['VALUE'];
	$useSeconds =
		($GLOBALS[$arResult['additionalParameters']['NAME']]['USE_SECOND'] === 'N' ?
			'N' : 'Y'
		);
	$useTimezone =
		($GLOBALS[$arResult['additionalParameters']['NAME']]['USE_TIMEZONE'] === 'N' ?
			'N' : 'Y'
		);
}
elseif(
	is_array($arResult['userField'])
	&&
	is_array($arResult['userField']['SETTINGS']['DEFAULT_VALUE'])
)
{
	$type = $arResult['userField']['SETTINGS']['DEFAULT_VALUE']['TYPE'];
	$defaultDateTime = str_replace(
		' 00:00:00',
		'',
		CDatabase::FormatDate(
			$arResult['userField']['SETTINGS']['DEFAULT_VALUE']['VALUE'],
			'YYYY-MM-DD HH:MI:SS',
			CLang::GetDateFormat(DateType::FORMAT_TYPE_FULL)
		)
	);
	$useSeconds = ($arResult['userField']['SETTINGS']['USE_SECOND'] === 'N' ?
		'N' : 'Y'
	);
	$useTimezone = ($arResult['userField']['SETTINGS']['USE_TIMEZONE'] === 'N' ?
		'N' : 'Y'
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