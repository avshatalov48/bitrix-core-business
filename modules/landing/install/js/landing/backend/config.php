<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/backend.bundle.js',
	'rel' => [
		'main.core',
		'landing.env',
	],
	'skip_core' => false,
];