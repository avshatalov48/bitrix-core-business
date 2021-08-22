<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);



return array(
	'name' => Loc::getMessage('LANDING_DEMO_AGENCY_TITLE'),
	'description' => Loc::getMessage('LANDING_DEMO_AGENCY_DESCRIPTION'),
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_CODE' => 'agency',
			'THEMEFONTS_CODE' => 'Roboto',
			'THEMEFONTS_CODE_H' => 'Roboto',
			'THEMEFONTS_SIZE' => '1',
			'THEMEFONTS_USE' => 'Y',
			'UP_SHOW' => 'Y',
		)
	),
	'items' => array (
	)
);