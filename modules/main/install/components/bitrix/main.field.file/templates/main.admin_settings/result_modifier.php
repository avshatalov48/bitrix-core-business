<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Text\HtmlFilter;

if($arResult['additionalParameters']['bVarsFromForm'])
{
	$arResult['values']['size'] =
		(int)$GLOBALS[$arResult['additionalParameters']['NAME']]['SIZE'];
	$arResult['values']['width'] =
		(int)$GLOBALS[$arResult['additionalParameters']['NAME']]['LIST_WIDTH'];
	$arResult['values']['height'] =
		(int)$GLOBALS[$arResult['additionalParameters']['NAME']]['LIST_HEIGHT'];
	$arResult['values']['max_show_size'] =
		(int)$GLOBALS[$arResult['additionalParameters']['NAME']]['MAX_SHOW_SIZE'];
	$arResult['values']['max_allowed_size'] =
		(int)$GLOBALS[$arResult['additionalParameters']['NAME']]['MAX_ALLOWED_SIZE'];
	$arResult['values']['extensions'] = HtmlFilter::encode(
		$GLOBALS[$arResult['additionalParameters']['NAME']]['EXTENSIONS']
	);
	$arResult['values']['targetBlank'] = trim(
		$GLOBALS[$arResult['additionalParameters']['NAME']]['TARGET_BLANK']
	);
}
elseif(is_array($arResult['userField']))
{
	$arResult['values']['size'] =
		(int)$arResult['userField']['SETTINGS']['SIZE'];
	$arResult['values']['width'] =
		(int)$arResult['userField']['SETTINGS']['LIST_WIDTH'];
	$arResult['values']['height'] =
		(int)$arResult['userField']['SETTINGS']['LIST_HEIGHT'];
	$arResult['values']['max_show_size'] =
		(int)$arResult['userField']['SETTINGS']['MAX_SHOW_SIZE'];
	$arResult['values']['max_allowed_size'] =
		(int)$arResult['userField']['SETTINGS']['MAX_ALLOWED_SIZE'];

	$extensions = [];
	if(is_array($arResult['userField']['SETTINGS']['EXTENSIONS']))
	{
		foreach($arResult['userField']['SETTINGS']['EXTENSIONS'] as $ext => $flag)
		{
			$extensions[] = HtmlFilter::encode($ext);
		}
	}

	$arResult['values']['extensions'] =  implode(', ', $extensions);

	$arResult['values']['targetBlank'] = trim(
		$arResult['userField']['SETTINGS']['TARGET_BLANK']
	);
}
else
{
	$arResult['values']['size'] = 20;
	$arResult['values']['width'] = 200;
	$arResult['values']['height'] = 200;
	$arResult['values']['max_show_size'] = 0;
	$arResult['values']['max_allowed_size'] = 0;
	$arResult['values']['extensions'] = '';
	$arResult['values']['targetBlank'] = 'Y';
}