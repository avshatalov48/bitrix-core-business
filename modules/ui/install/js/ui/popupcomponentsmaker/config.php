<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/popupcomponentsmaker.bundle.css',
	'js' => 'dist/popupcomponentsmaker.bundle.js',
	'rel' => [
		'main.core.events',
		'main.loader',
		'main.core',
		'main.popup',
	],
	'skip_core' => false,
	' ' => false,
];
