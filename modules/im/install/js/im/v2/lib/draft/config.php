<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/draft.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'im.v2.application.core',
		'im.v2.lib.local-storage',
		'im.v2.lib.logger',
		'im.v2.const',
	],
	'skip_core' => true,
];