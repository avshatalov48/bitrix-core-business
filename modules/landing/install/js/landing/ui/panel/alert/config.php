<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/alert.bundle.css',
	'js' => 'dist/alert.bundle.js',
	'rel' => [
		'main.core',
		'landing.loc',
		'landing.ui.panel.base',
	],
	'skip_core' => false,
];