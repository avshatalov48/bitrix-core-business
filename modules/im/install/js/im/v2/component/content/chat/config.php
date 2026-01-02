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
		'ui.notification',
		'im.v2.lib.layout',
		'im.v2.lib.utils',
		'im.v2.lib.channel',
		'im.v2.lib.notifier',
		'im.v2.component.elements.loader',
		'im.v2.component.animation',
		'ui.dialogs.tooltip',
		'im.v2.component.elements.chat-title',
		'im.v2.lib.copilot',
		'im.v2.component.entity-selector',
		'main.popup',
		'im.v2.component.elements.popup',
		'ui.icon-set.api.vue',
		'im.v2.lib.health-check',
		'im.v2.provider.service.recent',
		'im.v2.lib.promo',
		'im.v2.lib.invite',
		'im.v2.component.content.chat-forms.forms',
		'im.v2.lib.feature',
		'im.public',
		'im.v2.component.elements.copilot-roles-dialog',
		'im.v2.lib.theme',
		'im.v2.provider.service.copilot',
		'main.core',
		'im.v2.application.core',
		'im.v2.lib.analytics',
		'main.core.events',
		'im.v2.component.elements.avatar',
		'im.v2.lib.permission',
		'im.v2.component.content.elements',
		'im.v2.component.elements.toggle',
		'im.v2.provider.service.comments',
		'im.v2.lib.logger',
		'im.v2.model',
		'im.v2.component.dialog.chat',
		'im.v2.component.message-list',
		'im.v2.lib.message-component',
		'im.v2.const',
		'im.v2.component.textarea',
		'im.v2.component.elements.button',
		'im.v2.provider.service.chat',
	],
	'skip_core' => false,
];
