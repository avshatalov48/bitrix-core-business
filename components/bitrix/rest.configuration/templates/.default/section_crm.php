<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CMain $APPLICATION */
/** @var CBitrixComponentTemplate $this */
/** @var array $arResult */
/** @var array $arParams */

use Bitrix\Main\Localization\Loc;

global $APPLICATION;
$APPLICATION->SetTitle($arResult['TITLE']);

$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
$bodyClasses = 'no-hidden no-background no-all-paddings';
$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, $bodyClasses)));
if (!empty($arResult['ERROR']))
{
	ShowError($arResult['ERROR']);
	return false;
}

\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"ui.tilegrid",
	"ui.buttons",
]);

$APPLICATION->IncludeComponent(
	'bitrix:rest.configuration.action',
	'crm',
	array(
		'PATH_TO_IMPORT' => $arResult['PATH_TO_IMPORT'],
		'PATH_TO_IMPORT_MANIFEST' => $arResult['PATH_TO_IMPORT_MANIFEST'],
		'PATH_TO_EXPORT' => $arResult['PATH_TO_EXPORT'],
		'MANIFEST_CODE' => $arResult['MANIFEST_CODE'],
		'MP_LOAD_PATH' => '',
		'FROM' => $arResult['FROM'],
	)
);

$APPLICATION->IncludeComponent(
	'bitrix:rest.marketplace.solution',
	'',
	[]
);
