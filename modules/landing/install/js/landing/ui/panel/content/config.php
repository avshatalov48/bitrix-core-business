<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/content.bundle.css',
	'js' => 'dist/content.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.panel.base',
		'landing.utils',
	],
	'skip_core' => false,
];