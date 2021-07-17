<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/designpreview.bundle.css',
	'js' => 'dist/designpreview.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];