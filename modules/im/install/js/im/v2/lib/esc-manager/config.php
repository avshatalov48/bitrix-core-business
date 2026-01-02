<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/esc-manager.bundle.js',
	],
	'rel' => [
		'main.core',
		'main.core.events',
		'main.popup',
		'main.sidepanel',
		'im.v2.lib.utils',
		'im.v2.lib.call',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.layout',
		'im.v2.lib.slider',
		'im.v2.lib.desktop',
		'im.v2.lib.desktop-api',
	],
	'skip_core' => false,
];
