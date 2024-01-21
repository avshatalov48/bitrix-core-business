<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/component.bundle.css',
	'js' => 'dist/component.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.node.base',
	],
	'skip_core' => true,
];