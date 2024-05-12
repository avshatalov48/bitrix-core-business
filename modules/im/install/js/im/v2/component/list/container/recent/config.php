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
		'main.core',
		'im.public',
		'im.v2.lib.utils',
		'im.v2.component.list.items.recent',
		'im.v2.component.search.chat-search-input',
		'im.v2.component.search.chat-search',
		'im.v2.lib.logger',
		'im.v2.provider.service',
		'im.v2.lib.promo',
		'im.v2.lib.create-chat',
		'im.v2.lib.layout',
		'im.v2.const',
		'ui.lottie',
		'im.v2.component.elements',
	],
	'skip_core' => false,
];