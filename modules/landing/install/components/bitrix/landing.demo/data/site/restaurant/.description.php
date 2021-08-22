<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);



return array(
	'name' => Loc::getMessage('LANDING_DEMO_RESTAURANT_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_RESTAURANT_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'restaurant',
			'THEMEFONTS_CODE' => 'Montserrat',
			'THEMEFONTS_CODE_H' => 'Montserrat',
			'THEMEFONTS_SIZE' => '0.92857',
			'THEMEFONTS_USE' => 'Y',
			'UP_SHOW' => 'Y',
		)
	),
	'items' => array (
	)
);