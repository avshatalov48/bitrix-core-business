<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Loader::includeModule('im');

return [
	'js' => [
		'./dist/core.bundle.js',
	],
	'rel' => [
		'im.old-chat-embedding.application.launch',
		'pull.client',
		'rest.client',
		'main.core',
		'ui.vue3',
		'ui.vue3.vuex',
		'im.old-chat-embedding.model',
		'im.old-chat-embedding.const',
		'im.old-chat-embedding.provider.pull',
		'im.old-chat-embedding.lib.logger',
		'im.old-chat-embedding.lib.utils',
		'im.old-chat-embedding.lib.smile-manager',
	],
	'skip_core' => false,
	'settings' => [
		'v2' => \Bitrix\Im\Settings::isBetaActivated()
	]
];