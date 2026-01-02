<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/recent-container.bundle.css',
	'js' => 'dist/recent-container.bundle.js',
	'rel' => [
		'main.core.events',
		'im.v2.component.list.items.recent',
		'im.v2.component.search',
		'im.v2.lib.logger',
		'im.v2.provider.service.chat',
		'ui.info-helper',
		'im.public',
		'im.v2.component.elements.menu',
		'im.v2.component.elements.copilot-roles-dialog',
		'im.v2.component.list.container.elements.create-chat-promo',
		'im.v2.lib.permission',
		'im.v2.lib.create-chat',
		'im.v2.lib.feature',
		'im.v2.provider.service.copilot',
		'im.v2.lib.helpdesk',
		'main.core',
		'im.v2.lib.analytics',
		'im.v2.lib.invite',
		'im.v2.lib.promo',
		'im.v2.const',
	],
	'skip_core' => false,
];
