<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/local-settings.bundle.css',
	'js' => 'dist/local-settings.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];