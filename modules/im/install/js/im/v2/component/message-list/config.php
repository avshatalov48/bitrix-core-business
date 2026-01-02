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
		'im.v2.lib.utils',
		'im.v2.lib.quote',
		'im.v2.component.animation',
		'im.v2.lib.message-component',
		'ui.lottie',
		'im.v2.lib.input-action',
		'im.v2.component.elements.user-list-popup',
		'im.v2.lib.user',
		'im.v2.lib.logger',
		'ui.vue3.components.rich-loc',
		'im.v2.lib.menu',
		'main.polyfill.intersectionobserver',
		'im.v2.component.elements.avatar',
		'im.v2.lib.copilot',
		'im.v2.lib.permission',
		'im.v2.lib.notifier',
		'main.core',
		'main.core.events',
		'im.v2.lib.analytics',
		'im.v2.lib.feature',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.date-formatter',
		'im.v2.component.message.file',
		'im.v2.component.message.default',
		'im.v2.component.message.error',
		'im.v2.component.message.call-invite',
		'im.v2.component.message.deleted',
		'im.v2.component.message.unsupported',
		'im.v2.component.message.smile',
		'im.v2.component.message.system',
		'im.v2.component.message.creation.chat',
		'im.v2.component.message.copilot.creation',
		'im.v2.component.message.copilot.answer',
		'im.v2.component.message.copilot.added-users',
		'im.v2.component.message.support.vote',
		'im.v2.component.message.support.session-number',
		'im.v2.component.message.support.chat-creation',
		'im.v2.component.message.creation.conference',
		'im.v2.component.message.supervisor.update-feature',
		'im.v2.component.message.supervisor.enable-feature',
		'im.v2.component.message.sign',
		'im.v2.component.message.check-in',
		'im.v2.component.message.creation.own-chat',
		'im.v2.component.message.zoom-invite',
		'im.v2.component.message.creation.general-chat',
		'im.v2.component.message.creation.general-channel',
		'im.v2.component.message.creation.channel',
		'imopenlines.v2.component.message.start-dialog',
		'imopenlines.v2.component.message.hidden',
		'imopenlines.v2.component.message.feedback-form',
		'im.v2.component.message.call',
		'im.v2.component.message.vote',
		'im.v2.component.message.creation.task-chat',
		'im.v2.component.message.collab.convert',
		'im.v2.component.message.ai-assistant.answer',
	],
	'skip_core' => false,
	'lang_additional' => \Bitrix\Im\V2\Chat\InputAction\StatusMessageProvider::get(),
	'settings' => [
		'multipleActionMessageLimit' => $messageLimit,
	],
];
