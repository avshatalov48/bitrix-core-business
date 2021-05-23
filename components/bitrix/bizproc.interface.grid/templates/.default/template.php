<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
global $APPLICATION;
$APPLICATION->IncludeComponent(
	'bitrix:main.interface.grid',
	'',
	array(
		'GRID_ID' => $arParams['~GRID_ID'],
		'HEADERS' => $arParams['~HEADERS'],
		'SORT' => $arParams['~SORT'],
		'SORT_VARS' => $arParams['~SORT_VARS'],
		'ROWS' => $arParams['~ROWS'],
		'FOOTER' => $arParams['~FOOTER'],
		'EDITABLE' => $arParams['~EDITABLE'],
		'ACTIONS' => $arParams['~ACTIONS'],
		'ACTION_ALL_ROWS' => $arParams['~ACTION_ALL_ROWS'],
		'NAV_OBJECT' => $arParams['~NAV_OBJECT'],
		'FORM_ID' => $arParams['~FORM_ID'],
		'TAB_ID' => $arParams['~TAB_ID'],
		'AJAX_MODE' => $arParams['~AJAX_MODE'],
		'AJAX_ID' => isset($arParams['~AJAX_ID']) ? $arParams['~AJAX_ID'] : '',
		'AJAX_OPTION_JUMP' => isset($arParams['~AJAX_OPTION_JUMP']) ? $arParams['~AJAX_OPTION_JUMP'] : 'N',
		'AJAX_OPTION_HISTORY' => isset($arParams['~AJAX_OPTION_HISTORY']) ? $arParams['~AJAX_OPTION_HISTORY'] : 'N',
		'AJAX_INIT_EVENT' => isset($arParams['~AJAX_INIT_EVENT']) ? $arParams['~AJAX_INIT_EVENT'] : '',
		'FILTER' => $arParams['~FILTER'],
		'FILTER_PRESETS' => $arParams['~FILTER_PRESETS'],
		'RENDER_FILTER_INTO_VIEW' => isset($arParams['~RENDER_FILTER_INTO_VIEW']) ? $arParams['~RENDER_FILTER_INTO_VIEW'] : '',
		'HIDE_FILTER' => isset($arParams['~HIDE_FILTER']) ? $arParams['~HIDE_FILTER'] : false,
		'FILTER_TEMPLATE' => isset($arParams['~FILTER_TEMPLATE']) ? $arParams['~FILTER_TEMPLATE'] : '',
		'MANAGER' => isset($arParams['~MANAGER']) ? $arParams['~MANAGER'] : null,
		'ERROR_MESSAGES' => $arParams['~ERROR_MESSAGES'],
	),
	$component, array('HIDE_ICONS' => 'Y')
);
?>
