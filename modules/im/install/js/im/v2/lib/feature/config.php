<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/feature.bundle.js',
	],
	'rel' => [
		'main.core',
		'ui.info-helper',
		'im.v2.const',
		'im.v2.application.core',
	],
	'skip_core' => false,
];