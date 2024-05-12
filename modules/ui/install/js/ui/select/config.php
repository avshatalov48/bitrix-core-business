<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/select.bundle.css',
	'js' => 'dist/select.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'main.popup',
	],
	'skip_core' => false,
];