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
	'name' => Loc::getMessage('LANDING_DEMO_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'travel',
			'THEME_CODE_TYPO' => 'travel',
			'B24BUTTON_CODE' => $buttons[0],
			'UP_SHOW' => 'Y',
		)
	),
	'items' => array (
	)
);