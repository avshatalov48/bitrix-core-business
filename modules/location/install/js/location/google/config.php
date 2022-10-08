<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => './dist/google.bundle.js',
	'rel' => [
		'main.core',
		'location.core',
	],
	'skip_core' => false,
];