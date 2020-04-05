<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/imageeditor.bundle.css',
	'js' => 'dist/imageeditor.bundle.js',
	'rel' => [
		'main.popup',
		'main.loader',
		'main.core',
	],
	'skip_core' => false,
];