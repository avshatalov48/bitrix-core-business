<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'mobile.css',
	'js' => 'mobile.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];