<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/document.bundle.css',
	'js' => 'dist/document.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];