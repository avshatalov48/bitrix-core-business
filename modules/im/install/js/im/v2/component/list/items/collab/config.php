<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/collab-list.bundle.css',
	'js' => 'dist/collab-list.bundle.js',
	'rel' => [
		'im.v2.lib.utils',
		'im.v2.component.elements',
		'im.v2.component.list.items.recent',
		'im.v2.application.core',
		'im.v2.lib.rest',
		'im.v2.lib.logger',
		'im.v2.lib.user',
		'main.core',
		'im.v2.const',
		'im.v2.lib.layout',
		'im.v2.lib.menu',
	],
	'skip_core' => false,
];