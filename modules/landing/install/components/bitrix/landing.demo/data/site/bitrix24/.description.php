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
	'name' => Loc::getMessage('LANDING_DEMO_B24_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_B24_DESCRIPTION'),
	'sort' => 3,
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => '3corporate',
			'B24BUTTON_CODE' => $buttons[0],
			'UP_SHOW' => 'Y',
		)
	),
	'items' => array (),
);