<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/date-formatter.bundle.css',
	'js' => 'dist/date-formatter.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.date',
	],
	'skip_core' => true,
];