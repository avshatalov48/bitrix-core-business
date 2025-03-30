<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$messageLimit = null;

if (\Bitrix\Main\Loader::includeModule('im'))
{
	$messageLimit = \Bitrix\Im\V2\Message\MessageService::getMultipleActionMessageLimit();
}

return [
	'css' => 'dist/message-list.bundle.css',
	'js' => 'dist/message-list.bundle.js',
	'rel' => [
		'im.v2.lib.quote',
		'im.v2.component.animation',
		'im.v2.lib.message-component-manager',
		'im.v2.lib.promo',
		'im.v2.lib.channel',
		'im.v2.lib.menu',
		'im.v2.lib.parser',
		'im.v2.lib.entity-creator',
		'im.v2.provider.service',
		'im.v2.lib.market',
		'im.v2.lib.utils',
		'im.v2.lib.confirm',
		'ui.notification',
		'main.polyfill.intersectionobserver',
		'im.v2.component.elements',
		'im.v2.lib.copilot',
		'im.v2.lib.permission',
		'main.core',
		'main.core.events',
		'im.v2.lib.analytics',
		'im.v2.lib.feature',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.date-formatter',
		'im.v2.component.message.file',
		'im.v2.component.message.default',
		'im.v2.component.message.call-invite',
		'im.v2.component.message.deleted',
		'im.v2.component.message.unsupported',
		'im.v2.component.message.smile',
		'im.v2.component.message.system',
		'im.v2.component.message.chat-creation',
		'im.v2.component.message.copilot.creation',
		'im.v2.component.message.copilot.answer',
		'im.v2.component.message.copilot.added-users',
		'im.v2.component.message.support.vote',
		'im.v2.component.message.support.session-number',
		'im.v2.component.message.support.chat-creation',
		'im.v2.component.message.conference-creation',
		'im.v2.component.message.supervisor.update-feature',
		'im.v2.component.message.supervisor.enable-feature',
		'im.v2.component.message.sign',
		'im.v2.component.message.check-in',
		'im.v2.component.message.own-chat-creation',
		'im.v2.component.message.zoom-invite',
		'im.v2.component.message.general-chat-creation',
		'im.v2.component.message.general-channel-creation',
		'im.v2.component.message.channel-creation',
		'imopenlines.v2.component.message.start-dialog',
		'imopenlines.v2.component.message.hidden',
		'imopenlines.v2.component.message.feedback-form',
		'im.v2.component.message.call',
		'im.v2.component.message.vote',
	],
	'skip_core' => false,
	'settings' => [
		'multipleActionMessageLimit' => $messageLimit,
	],
];