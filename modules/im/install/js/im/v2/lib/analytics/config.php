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
		'ui.analytics',
		'main.core',
		'im.v2.application.core',
	],
	'skip_core' => false,
];