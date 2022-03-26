<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @var \CMain $APPLICATION */
/** @var \CBitrixComponent $component */
?>

<?$APPLICATION->IncludeComponent(
	'bitrix:landing.folder_edit',
	'.default',
	array(
		'TYPE' => $arParams['TYPE'],
		'FOLDER_ID' => $arResult['VARS']['folder_edit'],
		'ACTION_FOLDER' => $arParams['ACTION_FOLDER'],
		'PAGE_URL_LANDING_EDIT' => $arParams['PAGE_URL_LANDING_EDIT'],
		'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW']
	),
	$component
);?>
