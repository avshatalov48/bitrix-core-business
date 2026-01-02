<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var \IblockElement $component
 * @var \CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

use Bitrix\Main\Loader;
use Bitrix\UI\Toolbar\Facade\Toolbar;

Loader::includeModule('ui');

$settings = $arResult['SETTINGS'];
if (!empty($arResult['FILTER']))
{
	if ($settings['FILTER']['PAGETITLE'] === 'Y')
	{
		Toolbar::addFilter($arResult['FILTER']);
	}
	else
	{
		$APPLICATION->includeComponent(
			'bitrix:main.ui.filter',
			'',
			$arResult['FILTER'],
			$component,
			['HIDE_ICONS' => true]
		);
	}
}

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID'],
	$component,
	['HIDE_ICONS' => true]
);
unset($settings);
