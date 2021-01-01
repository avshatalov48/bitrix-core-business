<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/hsicons.bundle.css',
	'js' => 'dist/hsicons.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.loc',
	],
	'skip_core' => true,
];