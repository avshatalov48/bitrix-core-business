<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/navigation.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.info-helper',
		'im.v2.const',
		'im.v2.lib.analytics',
		'im.v2.lib.feature',
		'im.v2.lib.layout',
		'im.v2.lib.phone',
		'im.v2.lib.utils',
		'im.v2.lib.market',
	],
	'skip_core' => true,
];
