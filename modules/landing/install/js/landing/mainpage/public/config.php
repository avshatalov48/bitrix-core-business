<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/public.bundle.css',
	'js' => 'dist/public.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
	],
	'skip_core' => true,
];