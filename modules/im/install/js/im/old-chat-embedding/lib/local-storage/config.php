<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/local-storage.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'im.old-chat-embedding.application.core',
	],
	'skip_core' => true,
];