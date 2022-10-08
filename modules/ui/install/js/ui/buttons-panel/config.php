<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => 'dist/buttons-panel.bundle.css',
	'js' => 'dist/buttons-panel.bundle.js',
	'rel' => [
		'main.core',
		'ui.buttons',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];
