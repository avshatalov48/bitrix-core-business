<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/directorymenu.bundle.css',
	'js' => 'dist/directorymenu.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
	],
	'skip_core' => false,
];