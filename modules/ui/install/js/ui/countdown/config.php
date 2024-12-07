<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/countdown.bundle.css',
	'js' => 'dist/countdown.bundle.js',
	'rel' => [
		'main.core',
		'ui.icon-set.main',
	],
	'skip_core' => false,
];
