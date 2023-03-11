<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \CAllMain $APPLICATION */
/** @var \CBitrixComponentTemplate $this */
/** @var array $arResult */
/** @var array $arParams */

use Bitrix\Main\Localization\Loc;

global $APPLICATION;
$APPLICATION->SetTitle($arResult['TITLE']);

$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
$bodyClasses = 'no-hidden no-background no-all-paddings';
$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, $bodyClasses)));
if($arResult['ERROR'])
{
	ShowError($arResult['ERROR']);
	return false;
}
\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"ui.tilegrid",
	"ui.buttons",
]);
?>
<?php
$APPLICATION->IncludeComponent(
	'bitrix:rest.configuration.section',
	'',
	array(
		'PATH_TO_SECTION' => $arResult['PATH_TO_SECTION']
	)
);
?>
<?
$APPLICATION->IncludeComponent(
	'bitrix:rest.marketplace.category',
	'banner',
	array(
		'TAG' => $arResult['TAG_BANNER'],
		'FILTER_ID' => '_configuration_banner',
		'BLOCK_COUNT' => 3,
		'SET_TITLE' => 'N',
		'SECTION_TITLE' => Loc::getMessage('REST_CONFIGURATION_APP_SECTION_TITLE_2'),
		'HOLD_BANNER_ITEMS' => 'Y',
		'DETAIL_URL_TPL' => $arResult['MP_DETAIL_URL_TPL'],
		'MP_TAG_PATH' => $arResult['MP_TAG_PATH'],
		'FROM' => $arResult['FROM'],
	)
);
?>
<?
$haveItems = $APPLICATION->IncludeComponent(
	'bitrix:rest.marketplace.category',
	'list',
	array(
		'TAG' => $arResult['TAG'],
		'FILTER_ID' => '_configuration_list',
		'SHOW_LAST_BLOCK' => 'Y',
		'BLOCK_COUNT' => 8,
		'SET_TITLE' => 'N',
		'DETAIL_URL_TPL' => $arResult['MP_DETAIL_URL_TPL'],
		'INDEX_URL_PATH' => $arResult['MP_INDEX_PATH'],
		'SECTION_URL_PATH' => $arResult['MP_TAG_PATH'],
		'SECTION_TITLE' => Loc::getMessage("REST_CONFIGURATION_TITLE_NEW_APP"),
		'SECTION_SHOW_ALL_BTN_NAME' => Loc::getMessage("REST_CONFIGURATION_BTN_SHOW_ALL"),
		'FROM' => $arResult['FROM'],
		)
);
?>
