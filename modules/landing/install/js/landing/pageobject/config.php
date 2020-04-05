<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/pageobject.bundle.css',
	'js' => 'dist/pageobject.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];