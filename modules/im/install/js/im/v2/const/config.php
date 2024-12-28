<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/registry.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'imopenlines.v2.const',
		'im.v2.const',
	],
	'skip_core' => true,
];