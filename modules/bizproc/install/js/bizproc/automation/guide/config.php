<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/guide.bundle.css',
	'js' => 'dist/guide.bundle.js',
	'rel' => [
		'ui.tour',
		'main.core',
		'main.core.events',
		'bizproc.local-settings',
	],
	'skip_core' => false,
];