<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/baseform.bundle.css',
	'js' => 'dist/baseform.bundle.js',
	'rel' => [
		'main.core',
		'landing.env',
	],
	'skip_core' => false,
];