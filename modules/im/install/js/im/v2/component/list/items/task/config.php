<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/task-list.bundle.css',
	'js' => 'dist/task-list.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.lib.draft',
		'im.v2.lib.utils',
		'im.v2.component.elements.list-loading-state',
		'im.v2.component.list.items.recent',
		'im.v2.component.list.items.elements.empty-state',
		'im.v2.const',
		'im.v2.provider.service.recent',
		'im.v2.lib.menu',
	],
	'skip_core' => true,
];