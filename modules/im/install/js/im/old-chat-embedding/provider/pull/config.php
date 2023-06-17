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
		'pull.client',
		'main.core',
		'im.old-chat-embedding.application.core',
		'im.old-chat-embedding.lib.logger',
		'im.old-chat-embedding.lib.user',
		'im.old-chat-embedding.const',
	],
	'skip_core' => false,
];