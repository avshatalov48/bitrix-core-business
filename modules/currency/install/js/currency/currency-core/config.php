<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/currency-core.bundle.css',
	'js' => 'dist/currency-core.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];