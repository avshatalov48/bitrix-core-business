<?php

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
	'SONET_EXT_LIVEFEED_COLLAPSED_PINNED_PANEL_ITEMS_LIMIT' => \Bitrix\Socialnetwork\Component\LogList\Util::getCollapsedPinnedPanelItemsLimit(),
	'SONET_EXT_LIVEFEED_CREATE_TASK_PATH' => \Bitrix\Main\Config\Option::get('socialnetwork', 'user_page', SITE_DIR . 'company/personal/') . 'user/#user_id#/tasks/task/view/#task_id#/',
	'SONET_EXT_LIVEFEED_SITE_TEMPLATE_ID' => (defined('SITE_TEMPLATE_ID') ? CUtil::JSEscape(SITE_TEMPLATE_ID) : ''),
	'SONET_EXT_LIVEFEED_INTRANET_INSTALLED' => (\Bitrix\Main\ModuleManager::isModuleInstalled('intranet') ? 'Y' : 'N'),
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
		'socialnetwork.commentaux',
		'intranet.control-button',
		'tasks.result',
	],
	'skip_core' => false,
];
