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
	'name' => Loc::getMessage('LANDING_DEMO_8MARCH1_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_8MARCH1_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'B24BUTTON_CODE' => $buttons[0],
			'UP_SHOW' => 'Y',
			'THEME_CODE' => 'wedding',
			'THEME_CODE_TYPO' => 'wedding',
		)
	),
	'items' => array (
	),
	'sort' => \LandingSiteDemoComponent::checkActivePeriod(2,15,3,8) ? 91 : -121,
	'available' => true,
	'active' => \LandingSiteDemoComponent::checkActive(array(
		'ONLY_IN' => array('ru', 'kz', 'by', 'ua'),
		'EXCEPT' => array()
	))
);