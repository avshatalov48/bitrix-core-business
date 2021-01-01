<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/simpleline.bundle.css',
	'js' => 'dist/simpleline.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.loc',
	],
	'skip_core' => true,
];