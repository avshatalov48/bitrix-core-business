<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */

$c = \Bitrix\Main\Text\Converter::getHtmlConverter();

$appId = 0;
$appTitle = '';

foreach($arResult['APPLICATION_LIST'] as $app)
{
	if($app['ID'] == $arResult['APPLICATION_CURRENT'])
	{
		$appId = $app['APP_ID'];
		break;
	}
}

if($appId > 0)
{
	$APPLICATION->IncludeComponent(
		'bitrix:app.layout',
		'',
		array(
			'ID' => $appId,
			'PLACEMENT' => $arResult['PLACEMENT'],
			'PLACEMENT_ID' => $arResult['APPLICATION_CURRENT'],
			"PLACEMENT_OPTIONS" => $arResult['PLACEMENT_OPTIONS'],
			'PARAM' => $arParams['PARAM']
		),
		null,
		array('HIDE_ICONS' => 'Y')
	);
}

