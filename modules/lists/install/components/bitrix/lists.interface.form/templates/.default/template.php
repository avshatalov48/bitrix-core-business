<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @global CMain $APPLICATION */
/** @var CBitrixComponent $component */

$APPLICATION->includeComponent(
	'bitrix:main.interface.form',
	'lists',
	array(
		'FORM_ID' => $arParams['~FORM_ID'],
		'TABS' => $arParams['~TABS'],
		'BUTTONS' => $arParams['~BUTTONS'],
		'DATA' => $arParams['~DATA'],
		'SHOW_SETTINGS'=>'N',
		'THEME_GRID_ID'=>$arParams['~THEME_GRID_ID'],
	),
	$component, array('HIDE_ICONS' => 'Y')
);