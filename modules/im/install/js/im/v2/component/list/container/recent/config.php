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
		'im.public',
		'im.v2.lib.utils',
		'im.v2.component.list.items.recent',
		'im.v2.component.search.chat-search-input',
		'im.v2.component.search.chat-search',
		'im.v2.lib.logger',
		'im.v2.provider.service',
		'im.v2.component.elements',
		'im.v2.const',
		'im.v2.lib.analytics',
		'im.v2.lib.permission',
		'im.v2.lib.promo',
		'im.v2.lib.create-chat',
		'im.v2.lib.feature',
		'im.v2.lib.helpdesk',
		'main.core',
	],
	'skip_core' => false,
];
