<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/recent-list.bundle.css',
	'js' => 'dist/recent-list.bundle.js',
	'rel' => [
		'im.v2.provider.service.recent',
		'im.v2.lib.menu',
		'im.v2.lib.draft',
		'im.v2.component.elements.list-loading-state',
		'im.v2.component.list.items.elements.input-action-indicator',
		'im.v2.lib.date-formatter',
		'im.v2.lib.channel',
		'main.date',
		'im.v2.lib.parser',
		'call.application.conference',
		'im.v2.component.elements.chat-title',
		'im.v2.lib.call',
		'call.lib.analytics',
		'ui.icon-set.api.vue',
		'im.public',
		'im.v2.lib.create-chat',
		'im.v2.component.elements.avatar',
		'im.v2.component.elements.button',
		'im.v2.lib.feature',
		'im.v2.lib.invite',
		'im.v2.component.list.items.elements.empty-state',
		'main.core',
		'im.v2.lib.utils',
		'main.core.events',
		'im.v2.application.core',
		'im.v2.const',
	],
	'skip_core' => false,
];