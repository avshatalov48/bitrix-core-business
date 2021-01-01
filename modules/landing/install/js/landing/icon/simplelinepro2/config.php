<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/simplelinepro2.bundle.css',
	'js' => 'dist/simplelinepro2.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.loc',
	],
	'skip_core' => true,
];