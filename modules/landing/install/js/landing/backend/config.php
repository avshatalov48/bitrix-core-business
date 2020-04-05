<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/backend.bundle.css',
	'js' => 'dist/backend.bundle.js',
	'rel' => [
		'main.core',
		'landing.env',
	],
	'skip_core' => false,
];