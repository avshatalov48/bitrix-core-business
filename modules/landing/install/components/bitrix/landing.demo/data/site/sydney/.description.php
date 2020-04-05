<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

$buttons = \Bitrix\Landing\Hook\Page\B24button::getButtons();
$buttons = array_keys($buttons);

return array(
	'active' => false,	// dbg: not actual, remove if all OK
	'code' => 'sydney',
	'version' => 2,
	'name' => Loc::getMessage('LANDING_DEMO_B24SYD_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_B24SYD_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#000000',
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'B24BUTTON_CODE' => $buttons[0],
			'UP_SHOW' => 'Y',
		),
	),
	'sort' => 2,
	'layout' => array(),
	'folders' => array(),
	'syspages' => array(),
	'items' => array(
		'sydney',
	),
);