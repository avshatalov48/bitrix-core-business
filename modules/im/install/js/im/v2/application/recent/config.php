<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/recent.bundle.js',
	],
	'css' => [
		'./dist/recent.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'im.v2.application.core',
		'im.v2.component.recent-list',
		'im.v2.provider.pull',
	],
	'skip_core' => true,
];