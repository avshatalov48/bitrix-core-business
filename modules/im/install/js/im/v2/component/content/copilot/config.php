<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot-content.bundle.css',
	'js' => 'dist/copilot-content.bundle.js',
	'rel' => [
		'im.v2.lib.logger',
		'im.v2.lib.theme',
		'im.v2.lib.textarea',
		'im.v2.component.sidebar',
		'ui.notification',
		'im.v2.component.entity-selector',
		'im.public',
		'im.v2.const',
		'im.v2.provider.service',
		'im.v2.lib.analytics',
		'im.v2.lib.draft',
		'im.v2.component.textarea',
		'main.core',
		'main.core.events',
		'im.v2.lib.desktop-api',
		'im.v2.component.dialog.chat',
		'im.v2.component.message-list',
		'im.v2.component.elements',
		'ui.vue3',
	],
	'skip_core' => false,
	'settings' => [
		'isAddToChatAvailable' => \Bitrix\Main\Config\Option::get('im', 'im_add_users_to_copilot_chat', 'N'),
	]
];