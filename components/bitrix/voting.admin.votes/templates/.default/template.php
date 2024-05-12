<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
die();
}

use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\UI\Toolbar\Facade\Toolbar;
use \Bitrix\UI;
/**
* @global  CMain $APPLICATION
* @var array $arParams
* @var array $arResult
*/

Toolbar::addFilter([
	'FILTER_ID' => $arParams['FILTER_ID'],
	'GRID_ID' => $arParams['GRID_ID'],
	'ENABLE_LABEL' => true,
	'FILTER' => $arResult['FILTERS'],
	'VALUE_REQUIRED_MODE' => true,
]);
$currentChannelId = $arResult['FILTER']['=CHANNEL_ID'] ?? 0;
if ($currentChannelId > 0 && array_key_exists($currentChannelId, $arResult['ADMIN_CHANNELS']))
{
	Toolbar::addButton([
		'link' => 'vote_edit.php?lang=' . LANG . '&CHANNEL_ID=' . $arResult['FILTER']['=CHANNEL_ID'],
		'color' => UI\Buttons\Color::PRIMARY,
		'icon' => UI\Buttons\Icon::ADD,
		'text' => Loc::getMessage('VOTE_ADD_LIST'),
	]);
}

$APPLICATION->IncludeComponent('bitrix:ui.toolbar', 'admin', []);

$messages = [];
/** @var Main\Error $error */
foreach ($arResult['ERRORS'] as $error)
{
	$messages[] = array(
		'TYPE' => Main\Grid\MessageType::ERROR,
		'TEXT' => $error->getMessage(),
		'TITLE' => Loc::getMessage('VOTE_VOTE_ERROR_TITLE'),
	);
}

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID' => $arParams['GRID_ID'],
		'COLUMNS' => $arResult['COLUMNS'],
		'ROWS' => $arResult['ROWS'],
		'NAV_OBJECT' => $arResult['NAV_OBJECT'],
		'~NAV_PARAMS' => ['SHOW_ALWAYS' => false],
		'ACTION_PANEL' => $arResult['ACTION_PANEL'],

		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_ROW_CHECKBOXES' => false,
		'SHOW_ACTION_PANEL' => false,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => false,

		'MESSAGES' => $messages,

		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,

		'AJAX_MODE' => 'Y',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_STYLE' => 'N',
		'AJAX_OPTION_HISTORY' => 'N',
	]
);
