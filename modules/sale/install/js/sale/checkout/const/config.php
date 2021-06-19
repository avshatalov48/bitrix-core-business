<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/const.bundle.css',
	'js' => 'dist/const.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];