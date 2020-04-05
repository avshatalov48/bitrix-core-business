<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/fixfontsize.bundle.css',
	'js' => 'dist/fixfontsize.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];