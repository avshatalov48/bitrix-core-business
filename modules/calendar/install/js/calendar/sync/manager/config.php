<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/manager.bundle.css',
	'js' => 'dist/manager.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.tour',
		'main.popup',
		'main.core.events',
		'calendar.util',
		'main.core',
	],
	'skip_core' => false,
];