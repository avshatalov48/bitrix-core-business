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
	'css' => 'dist/textarea.bundle.css',
	'js' => 'dist/textarea.bundle.js',
	'rel' => [
		'ui.uploader.core',
		'im.v2.lib.local-storage',
		'im.v2.lib.sound-notification',
		'rest.client',
		'im.v2.application.core',
		'im.v2.lib.smile-manager',
		'im.v2.lib.rest',
		'im.v2.lib.entity-creator',
		'im.v2.lib.hotkey',
		'im.v2.lib.textarea',
		'im.v2.lib.draft',
		'main.core.events',
		'im.v2.lib.logger',
		'im.v2.provider.service',
		'im.v2.lib.text-highlighter',
		'im.v2.lib.date-formatter',
		'im.v2.lib.utils',
		'im.v2.lib.parser',
		'im.v2.const',
		'main.core',
		'im.v2.lib.market',
		'im.v2.component.elements',
	],
	'skip_core' => false,
	'settings' => [
		'maxLength' => \CIMMessenger::MESSAGE_LIMIT,
		'minSearchTokenSize' => \Bitrix\Main\ORM\Query\Filter\Helper::getMinTokenSize(),
	]
];