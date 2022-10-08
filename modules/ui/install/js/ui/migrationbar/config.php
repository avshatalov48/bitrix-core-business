<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => 'dist/migrationbar.bundle.css',
	'js' => 'dist/migrationbar.bundle.js',
	'rel' => [
		'main.core',
		'ui.buttons',
		'main.popup',
		'ui.design-tokens',
	],
	'skip_core' => false,
];
