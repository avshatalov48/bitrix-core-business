<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/quick-access.bundle.css',
	'js' => 'dist/quick-access.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.list.element-list.recent',
		'im.v2.lib.logger',
		'im.v2.lib.init',
	],
	'skip_core' => true,
];