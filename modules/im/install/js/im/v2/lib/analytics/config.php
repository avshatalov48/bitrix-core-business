<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/analytics.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.v2.lib.message-component-manager',
		'ui.analytics',
		'im.v2.application.core',
		'im.v2.const',
	],
	'skip_core' => true,
];
