<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/base.bundle.css',
	'js' => 'dist/base.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'landing.utils',
	],
	'skip_core' => false,
];