<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/manual.bundle.css',
	'js' => 'dist/manual.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];