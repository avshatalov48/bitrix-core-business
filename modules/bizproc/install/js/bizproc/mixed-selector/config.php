<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/mixed-selector.bundle.css',
	'js' => 'dist/mixed-selector.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'main.popup',
	],
	'skip_core' => false,
];