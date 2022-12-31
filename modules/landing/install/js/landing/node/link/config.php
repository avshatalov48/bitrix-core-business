<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/link.bundle.css',
	'js' => 'dist/link.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.panel.link',
	],
	'skip_core' => false,
];