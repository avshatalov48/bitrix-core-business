<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('socialnetwork');

$langAdditional = [
	'SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_Y' => Loc::getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_Y'),
	'SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_N' => Loc::getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_N'),
];

return [
	'js' => './dist/livefeed.bundle.js',
//	'css' => '/bitrix/js/socialnetwork/livefeed/livefeed.css',
	'lang_additional' => $langAdditional,
	'rel' => [
		'main.core',
		'main.core.events',
		'main.popup',
	],
	'skip_core' => false,
];