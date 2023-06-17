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
		'main.core',
		'ui.vue3.vuex',
		'im.old-chat-embedding.application.core',
		'im.old-chat-embedding.const',
		'im.old-chat-embedding.lib.utils',
	],
	'skip_core' => false,
];