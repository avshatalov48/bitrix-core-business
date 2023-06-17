<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/launch.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.old-chat-embedding.lib.logger',
	],
	'skip_core' => true,
];