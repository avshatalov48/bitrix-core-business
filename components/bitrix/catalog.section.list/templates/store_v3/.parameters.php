<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * @var array $arCurrentValues
 */

use Bitrix\Main\Localization\Loc;

$offsetModeList = [
	'N' => Loc::getMessage('CPT_BCSL_OFFSET_MODE_NO'),
	'F' => Loc::getMessage('CPT_BCSL_OFFSET_MODE_FIXED'),
	'D' => Loc::getMessage('CPT_BCSL_OFFSET_MODE_DYNAMIC')
];

$arTemplateParameters = [];
$arTemplateParameters['OFFSET_MODE'] = [
	'NAME' => Loc::getMessage('CPT_BCSL_OFFSET_MODE'),
	'PARENT' => 'VISUAL',
	'TYPE' => 'LIST',
	'VALUES' => $offsetModeList,
	'MULTIPLE' => 'N',
	'DEFAULT' => 'N',
	'REFRESH' => 'Y'
];

if (isset($arCurrentValues['OFFSET_MODE']))
{
	$arTemplateParameters['OFFSET_VALUE'] = [
		'NAME' => Loc::getMessage('CPT_BCSL_OFFSET_VALUE'),
		'PARENT' => 'VISUAL',
		'TYPE' => 'STRING',
		'DEFAULT' => '',
		'HIDDEN' => ($arCurrentValues['OFFSET_MODE'] === 'F' ? 'N' : 'Y')
	];
	$arTemplateParameters['OFFSET_VARIABLE'] = [
		'NAME' => Loc::getMessage('CPT_BCSL_OFFSET_VARIABLE'),
		'PARENT' => 'VISUAL',
		'TYPE' => 'STRING',
		'DEFAULT' => '',
		'HIDDEN' => ($arCurrentValues['OFFSET_MODE'] === 'D' ? 'N' : 'Y')
	];
}