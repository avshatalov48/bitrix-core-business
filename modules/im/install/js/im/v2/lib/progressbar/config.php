<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/progressbar.bundle.js',
	],
	'rel' => [
		'main.core.events',
		'main.core',
		'ui.progressbarjs.uploader',
		'im.v2.const',
	],
	'skip_core' => false,
];