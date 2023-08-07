<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Loader;

if (!Loader::includeModule('catalog'))
{
	ShowError(GetMessage('BX_CMP_CDI_ERR_MODULE_CATALOG_ABSENT'));
	return;
}

$arSiteList = [];
$strDefSite = '';
$rsSites = CSite::GetList('sort', 'desc');
while ($arSite = $rsSites->GetNext())
{
	if ('Y' == $arSite['DEF'])
	{
		$strDefSite = $arSite['ID'];
	}
	$arSiteList[$arSite['ID']] = '[' . $arSite['ID'] . '] ' . $arSite['NAME'];
}

$arComponentParameters = [
	'GROUPS' => [
	],
	'PARAMETERS' => [
		'SITE_ID' => [
			'PARENT' => 'DATA',
			'NAME' => GetMessage('BX_CMP_CDI_PARAM_TITLE_SITE_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $arSiteList,
			'ADDITIONAL_VALUES' => 'N',
			'REFRESH' => 'N',
			'MULTIPLE' => 'N',
			'DEFAULT' => $strDefSite,
		],
		'USER_ID' => [
			'PARENT' => 'DATA',
			'NAME' => GetMessage('BX_CMP_CDI_PARAM_TITLE_USER_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		],
		'SHOW_NEXT_LEVEL' => [
			'PARENT' => 'ADDITIONAL_SETTINGS',
			'NAME' => GetMessage('BX_CMP_CDI_PARAM_TITLE_SHOW_NEXT_LEVEL'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y'
		],
	],
];
