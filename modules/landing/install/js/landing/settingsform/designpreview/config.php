<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/designpreview.bundle.css',
	'js' => 'dist/designpreview.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.design-tokens',
		'main.polyfill.intersectionobserver',
	],
	'skip_core' => false,
];