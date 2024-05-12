<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/section.bundle.css',
	'js' => 'dist/section.bundle.js',
	'rel' => [
		'main.popup',
		'main.core',
	],
	'skip_core' => false,
];