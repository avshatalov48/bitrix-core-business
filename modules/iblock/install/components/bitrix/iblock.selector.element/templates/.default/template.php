<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Iblock;

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

$settings = $arResult['SETTINGS'];
if (!empty($arResult['FILTER']))
{
	$pageTitleFilter = ($settings['FILTER']['PAGETITLE'] === 'Y');
	if ($pageTitleFilter)
	{
		$this->SetViewTarget('inside_pagetitle');
	}
	$APPLICATION->includeComponent(
		'bitrix:main.ui.filter',
		'',
		$arResult['FILTER'],
		$component,
		['HIDE_ICONS' => true]
	);
	if ($pageTitleFilter)
	{
		$this->EndViewTarget();
	}
	unset($pageTitleFilter);
}

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID'],
	$component,
	['HIDE_ICONS' => true]
);
unset($settings);