<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/barcode.bundle.css',
	'js' => 'dist/barcode.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];