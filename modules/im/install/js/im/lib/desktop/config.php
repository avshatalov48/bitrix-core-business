<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/desktop.bundle.css',
	'js' => 'dist/desktop.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];