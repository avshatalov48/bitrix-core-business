<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

/** @var \CBitrixComponentTemplate $this  */
/** @var \CatalogCatalogControllerComponent $component */
/** @global \CMain $APPLICATION */
/** @var array $arResult */

$arResult['PAGE_DESCRIPTION']['SEF_FOLDER'] = $this->GetFolder().'/';
$arResult['PAGE_DESCRIPTION']['PAGE_PATH'] = 'include/section_detail.php';

$APPLICATION->IncludeComponent(
	"bitrix:crm.admin.page.include",
	"",
	$arResult['PAGE_DESCRIPTION'],
	$component,
	['HIDE_ICONS' => 'Y']
);
