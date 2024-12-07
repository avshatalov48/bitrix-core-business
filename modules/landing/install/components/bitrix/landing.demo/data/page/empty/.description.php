<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

return array(
	'name' => Loc::getMessage('LANDING_DEMO_EMPTY_TITLE_PAGE'),
	'description' => Loc::getMessage('LANDING_DEMO_EMPTY_DESCRIPTION'),
	'active' => true,
	'type' => ['page', 'knowledge', 'group', 'mainpage'],
	'fields' => array(
		'ADDITIONAL_FIELDS' => array(
			'THEME_COLOR' => '#4fd2c2',
			'THEME_USE' => 'N',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/empty/preview.jpg',
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_EMPTY_TITLE_PAGE'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_EMPTY_DESCRIPTION'),
			'METAMAIN_TITLE' => Loc::getMessage('LANDING_DEMO_EMPTY_TITLE_PAGE'),
			'METAMAIN_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_EMPTY_DESCRIPTION'),

			'THEMEFONTS_SIZE' => '1',
			'THEMEFONTS_LINE_HEIGHT' => '1.6',
		),
	),
	'items' => array (),
);