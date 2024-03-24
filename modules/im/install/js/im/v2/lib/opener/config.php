<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/opener.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'im.v2.const',
		'im.v2.lib.call',
		'im.v2.lib.desktop-api',
		'im.v2.lib.layout',
		'im.v2.lib.logger',
		'im.v2.lib.phone',
		'im.v2.lib.utils',
		'im.v2.lib.slider',
		'im.v2.provider.service',
	],
	'skip_core' => true,
];