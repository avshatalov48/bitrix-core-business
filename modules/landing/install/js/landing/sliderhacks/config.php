<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sliderhacks.bundle.css',
	'js' => 'dist/sliderhacks.bundle.js',
	'rel' => [
		'main.core',
		'main.loader',
	],
	'skip_core' => false,
];