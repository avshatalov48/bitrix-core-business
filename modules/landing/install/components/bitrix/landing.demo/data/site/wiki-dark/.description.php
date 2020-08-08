<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Hook\Page\B24button;
use \Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

$buttons = B24button::getButtons();
$buttons = array_keys($buttons);

return [
	'name' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'Y',
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'ADDITIONAL_FIELDS' => [
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'mobile',
			'ROBOTS_USE' => 'N',
			'THEME_CODE' => '3corporate',
			'THEMEFONTS_CODE' => 'g-font-roboto',
			'THEMEFONTS_CODE_H' => 'g-font-montserrat',
			'THEMEFONTS_SIZE' => '1.14286',
			'THEMEFONTS_USE' => 'Y',
			'COPYRIGHT_SHOW' => 'Y',
			'B24BUTTON_CODE' => $buttons[0],
			'B24BUTTON_COLOR' => 'site',
			'UP_SHOW' => 'Y',
			'GMAP_USE' => 'N',
			'PIXELFB_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'BACKGROUND_USE' => 'Y',
			'BACKGROUND_COLOR' => '#2C2C2C',
			'BACKGROUND_POSITION' => 'center_repeat_y',
			'BACKGROUND_PICTURE' => 'https://cdn.bitrix24.site/bitrix/images/landing/bg/wiki-dark.png',
			'METAYANDEXVERIFICATION_USE' => 'N',
			'YACOUNTER_USE' => 'N',
			'GTM_USE' => 'N',
			'PIXELVK_USE' => 'N',
			'METAGOOGLEVERIFICATION_USE' => 'N',
			'HEADBLOCK_USE' => 'N',
			'CSSBLOCK_USE' => 'N',
		],
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_DARK_TITLE"),
		'LANDING_ID_INDEX' => 'wiki-dark/main',
		'LANDING_ID_404' => '0',
	],
	'layout' => [
		'code' => 'header_footer',
		'ref' => array(
			1 => 'wiki-dark/header',
			2 => 'wiki-dark/footer',
		),
	],
	'folders' => [],
	'syspages' => [],
	'items' => [
			0 => 'wiki-dark/main',
			1 => 'wiki-dark/header',
			2 => 'wiki-dark/footer',
			3 => 'wiki-dark/detail',
	],
];