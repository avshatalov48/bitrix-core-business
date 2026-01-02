<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/channel-list.bundle.css',
	'js' => 'dist/channel-list.bundle.js',
	'rel' => [
		'im.v2.component.elements.list-loading-state',
		'main.date',
		'im.v2.component.elements.chat-title',
		'im.v2.component.elements.avatar',
		'im.v2.lib.utils',
		'im.v2.lib.parser',
		'im.v2.lib.date-formatter',
		'im.v2.provider.service.recent',
		'im.v2.application.core',
		'im.v2.lib.rest',
		'main.core',
		'im.v2.const',
		'im.v2.lib.layout',
		'im.v2.lib.menu',
	],
	'skip_core' => false,
];