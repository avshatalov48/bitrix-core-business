<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => 'dist/navigationpanel.bundle.css',
	'js' => 'dist/navigationpanel.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
];
