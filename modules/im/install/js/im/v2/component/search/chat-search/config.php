<?php

use Bitrix\ImConnector\Connectors\Network;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/chat-search.bundle.css',
	'js' => 'dist/chat-search.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.fonts.opensans',
		'im.v2.lib.logger',
		'im.v2.lib.search',
		'main.core.events',
		'im.public',
		'im.v2.lib.menu',
		'im.v2.lib.call',
		'im.v2.lib.permission',
		'main.core',
		'im.v2.const',
		'im.v2.lib.utils',
		'im.v2.lib.text-highlighter',
		'im.v2.lib.date-formatter',
		'im.v2.application.core',
		'im.v2.component.elements',
	],
	'settings' => [
		'minTokenSize' => \Bitrix\Main\ORM\Query\Filter\Helper::getMinTokenSize(),
	],
	'skip_core' => false,
];