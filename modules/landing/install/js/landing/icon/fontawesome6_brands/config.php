<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/fontawesome6_brands.bundle.css',
	'js' => 'dist/fontawesome6_brands.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'landing.loc',
	],
	'skip_core' => true,
];