<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!\Bitrix\Main\Loader::includeModule('im'))
{
	return [];
}

return [
	'css' => 'dist/chat-content.bundle.css',
	'js' => 'dist/chat-content.bundle.js',
	'rel' => [
		'ui.uploader.core',
		'im.v2.component.dialog.chat',
		'im.v2.component.textarea',
		'im.v2.lib.theme',
		'im.v2.lib.textarea',
		'im.v2.lib.layout',
		'im.v2.lib.logger',
		'im.v2.component.entity-selector',
		'main.core.events',
		'im.v2.lib.local-storage',
		'im.v2.lib.permission',
		'im.v2.lib.menu',
		'im.public',
		'im.v2.lib.call',
		'main.core',
		'im.v2.lib.utils',
		'im.v2.component.sidebar',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.component.elements',
		'im.v2.provider.service',
		'ui.notification',
	],
	'skip_core' => false,
	'settings' => [
		'isCallBetaAvailable' => \Bitrix\Im\Settings::isCallBetaAvailable()
	]
];