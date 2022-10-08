<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/popupcomponentsmaker.bundle.css',
	'js' => 'dist/popupcomponentsmaker.bundle.js',
	'rel' => [
		'main.popup',
		'main.core.events',
		'main.core',
		'main.loader',
		'ui.fonts.opensans',
		'ui.design-tokens',
	],
	'skip_core' => false,
	' ' => false,
];