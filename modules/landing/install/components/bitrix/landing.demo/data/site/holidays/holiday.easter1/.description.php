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
	'name' => Loc::getMessage('LANDING_DEMO_EASTER1_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_EASTER1_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'accounting',
			'THEME_CODE_TYPO' => 'accounting',
			'B24BUTTON_CODE' => $buttons[0],
			'UP_SHOW' => 'Y',
		)
	),
	'items' => array (
	),
	'sort' => \LandingSiteDemoComponent::checkActivePeriod(3,13,4,25) ? 71 : -141,
	'available' => true,
	'active' => \LandingSiteDemoComponent::checkActive(array(
		'ONLY_IN' => array(),
		'EXCEPT' => array('kz', 'ua', 'cn', 'tr')
	))
);