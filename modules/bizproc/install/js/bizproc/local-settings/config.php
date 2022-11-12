<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/local-settings.bundle.css',
	'js' => 'dist/local-settings.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];