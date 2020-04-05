<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/public.bundle.css',
	'js' => 'dist/public.bundle.js',
	'rel' => [
		'main.core',
		'landing.sliderhacks',
	],
	'skip_core' => false,
];