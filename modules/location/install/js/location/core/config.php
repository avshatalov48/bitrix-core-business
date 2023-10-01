<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'rel' => [
		'main.core',
		'main.md5',
		'location.core',
		'main.core.events',
	],
	'skip_core' => false,
	'js' => './dist/core.bundle.js'
];
