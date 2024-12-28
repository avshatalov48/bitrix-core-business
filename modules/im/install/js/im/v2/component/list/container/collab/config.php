<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/collab-container.bundle.css',
	'js' => 'dist/collab-container.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.list.items.collab',
		'im.v2.component.elements',
		'im.v2.const',
		'im.v2.lib.analytics',
		'im.v2.lib.feature',
		'im.v2.lib.logger',
		'im.v2.lib.create-chat',
		'im.v2.lib.permission',
	],
	'skip_core' => true,
];
