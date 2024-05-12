<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/public-event.bundle.css',
	'js' => 'dist/public-event.bundle.js',
	'rel' => [
		'main.core',
		'calendar.sharing.public-v2',
		'calendar.util',
	],
	'skip_core' => false,
];