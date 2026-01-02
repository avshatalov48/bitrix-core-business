<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/health-check.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];
