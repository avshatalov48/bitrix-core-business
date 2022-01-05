<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/short.view.bundle.css',
	'js' => 'dist/short.view.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
];