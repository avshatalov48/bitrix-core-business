<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/filter.bundle.css',
	'js' => 'dist/filter.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
	],
	'skip_core' => true,
];
