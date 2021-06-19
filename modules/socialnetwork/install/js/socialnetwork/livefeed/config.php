<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
/*use \Bitrix\Main\UI;*/

Loader::includeModule('socialnetwork');

$langAdditional = [
	'SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_Y' => Loc::getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_Y'),
	'SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_N' => Loc::getMessage('SONET_EXT_LIVEFEED_MENU_TITLE_FAVORITES_N'),
	'SONET_EXT_LIVEFEED_COLLAPSED_PINNED_PANEL_ITEMS_LIMIT' => \Bitrix\Socialnetwork\Component\LogList\Util::getCollapsedPinnedPanelItemsLimit()
];

return [
	'css' => './dist/livefeed.bundle.css',
	'js' => './dist/livefeed.bundle.js',
	'lang_additional' => $langAdditional,
	'rel' => [
		'main.popup',
		'ui.buttons',
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
];
