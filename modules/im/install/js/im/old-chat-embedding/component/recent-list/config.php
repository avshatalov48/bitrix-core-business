<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/recent-list.bundle.css',
	'js' => 'dist/recent-list.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'im.old-chat-embedding.provider.service',
		'im.old-chat-embedding.lib.menu',
		'main.date',
		'im.v2.lib.parser',
		'ui.vue3.vuex',
		'main.popup',
		'im.old-chat-embedding.component.elements',
		'im.old-chat-embedding.lib.logger',
		'im.old-chat-embedding.lib.utils',
		'main.core',
		'main.core.events',
		'im.old-chat-embedding.const',
	],
	'skip_core' => false,
];