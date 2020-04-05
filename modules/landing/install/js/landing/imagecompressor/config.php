<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/imagecompressor.bundle.css',
	'js' => 'dist/imagecompressor.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];