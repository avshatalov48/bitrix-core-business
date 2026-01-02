<?php

use Bitrix\ImConnector\Connectors\Network;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'im.v2.component.elements.loader',
		'im.v2.lib.logger',
		'im.public',
		'im.v2.lib.menu',
		'im.v2.lib.call',
		'im.v2.lib.permission',
		'im.v2.lib.text-highlighter',
		'im.v2.lib.date-formatter',
		'im.v2.component.elements.chat-title',
		'im.v2.component.elements.avatar',
		'im.v2.component.elements.search-input',
		'ui.design-tokens',
		'ui.fonts.opensans',
		'main.core',
		'main.core.events',
		'im.v2.application.core',
		'im.v2.lib.utils',
		'im.v2.const',
		'im.v2.lib.analytics',
		'im.v2.lib.search',
		'im.v2.component.elements.scroll-with-gradient',
	],
	'settings' => [
		'minTokenSize' => \Bitrix\Main\ORM\Query\Filter\Helper::getMinTokenSize(),
	],
	'skip_core' => false,
];
