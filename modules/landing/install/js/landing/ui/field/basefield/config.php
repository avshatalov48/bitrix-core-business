<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/basefield.bundle.css',
	'js' => 'dist/basefield.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];