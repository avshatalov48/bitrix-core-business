<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/fontawesome.bundle.css',
	'js' => 'dist/fontawesome.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.loc',
	],
	'skip_core' => true,
];