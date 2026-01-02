<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/task-container.bundle.css',
	'js' => 'dist/task-container.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.list.items.task',
		'im.v2.const',
		'im.v2.lib.logger',
	],
	'skip_core' => true,
];
