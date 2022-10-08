<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => './dist/osm.bundle.js',
	'css' => './dist/osm.bundle.css',
	'rel' => [
		'main.core',
		'location.core',
	],
	'skip_core' => false,
];