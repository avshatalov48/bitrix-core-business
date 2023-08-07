<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => 'dist/counterpanel.bundle.css',
	'js' => 'dist/counterpanel.bundle.js',
	'rel' => [
		'main.popup',
		'main.core',
		'ui.cnt',
		'main.core.events',
		'ui.design-tokens',
	],
	'skip_core' => false,
];
