<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/sidebar.bundle.js',
	],
	'rel' => [
		'im.v2.lib.feature',
		'im.v2.lib.market',
		'im.v2.application.core',
		'main.core',
		'im.v2.lib.channel',
		'im.v2.const',
	],
	'skip_core' => false,
];
