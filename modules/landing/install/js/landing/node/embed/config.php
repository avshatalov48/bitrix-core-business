<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/embed.bundle.css',
	'js' => 'dist/embed.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.node.base',
	],
	'skip_core' => true,
];