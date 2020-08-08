<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/b24integration.bundle.css',
	'js' => 'dist/b24integration.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];