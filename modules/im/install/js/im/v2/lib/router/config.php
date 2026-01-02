<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/router.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.public',
		'im.v2.const',
	],
	'skip_core' => true,
];
