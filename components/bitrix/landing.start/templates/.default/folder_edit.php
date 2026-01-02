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

<?php
$APPLICATION->includeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:landing.folder_edit',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '.default',
		'POPUP_COMPONENT_PARAMS' => [
			'TYPE' => $arParams['TYPE'],
			'FOLDER_ID' => $arResult['VARS']['folder_edit'],
			'ACTION_FOLDER' => $arParams['ACTION_FOLDER'],
			'PAGE_URL_LANDING_EDIT' => $arParams['PAGE_URL_LANDING_EDIT'],
			'PAGE_URL_LANDING_VIEW' => $arParams['PAGE_URL_LANDING_VIEW'],
		],
		'POPUP_COMPONENT_PARENT' => $component,
		'USE_PADDING' => false,
		'USE_UI_TOOLBAR' => 'Y',
	]
);
?>
