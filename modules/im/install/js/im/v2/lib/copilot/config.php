<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/copilot.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.v2.const',
		'im.v2.application.core',
	],
	'skip_core' => true,
];