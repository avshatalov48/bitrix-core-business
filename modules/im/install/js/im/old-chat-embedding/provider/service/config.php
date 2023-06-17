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
		'main.core.events',
		'rest.client',
		'im.old-chat-embedding.application.core',
		'im.old-chat-embedding.const',
		'im.old-chat-embedding.lib.logger',
	],
	'skip_core' => true,
];