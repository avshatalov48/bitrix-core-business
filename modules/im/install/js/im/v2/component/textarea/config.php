<?php

use \Bitrix\Main\Config\Option;

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
		'ui.icon-set.outline',
		'im.v2.lib.local-storage',
		'im.v2.provider.service.message',
		'im.v2.lib.sound-notification',
		'im.v2.lib.input-action',
		'ui.uploader.core',
		'im.v2.lib.esc-manager',
		'im.v2.lib.desktop-api',
		'im.v2.component.elements.tabs',
		'im.v2.lib.smile-manager',
		'im.v2.provider.service.sending',
		'im.v2.lib.helpdesk',
		'im.v2.lib.rest',
		'calendar.sharing.interface',
		'vote.application',
		'im.v2.component.elements.menu',
		'im.v2.lib.entity-creator',
		'im.v2.lib.analytics',
		'im.v2.lib.feature',
		'im.v2.lib.permission',
		'im.v2.lib.notifier',
		'file_dialog',
		'im.v2.model',
		'main.popup',
		'im.v2.lib.draft',
		'im.v2.lib.hotkey',
		'im.v2.lib.textarea',
		'im.v2.provider.service.uploading',
		'im.v2.component.elements.media-gallery',
		'im.v2.component.elements.send-button',
		'ui.icons',
		'im.v2.lib.copilot',
		'main.core.events',
		'im.v2.component.elements.scroll-with-gradient',
		'im.v2.lib.text-highlighter',
		'im.v2.component.elements.chat-title',
		'im.v2.component.elements.avatar',
		'im.v2.lib.user',
		'im.v2.lib.logger',
		'im.v2.lib.search',
		'im.v2.lib.utils',
		'im.v2.application.core',
		'im.v2.lib.parser',
		'main.core',
		'im.v2.component.elements.loader',
		'im.v2.lib.market',
		'im.v2.component.elements.popup',
		'ui.icon-set.api.vue',
		'im.v2.component.elements.auto-delete',
		'im.v2.provider.service.chat',
		'im.v2.lib.auto-delete',
		'im.v2.const',
	],
	'skip_core' => false,
	'settings' => [
		'maxLength' => \CIMMessenger::MESSAGE_LIMIT,
		'minSearchTokenSize' => \Bitrix\Main\ORM\Query\Filter\Helper::getMinTokenSize(),
	]
];
