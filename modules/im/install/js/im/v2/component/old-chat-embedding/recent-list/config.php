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
		'im.v2.provider.service',
		'im.v2.lib.old-chat-embedding.menu',
		'main.date',
		'im.v2.lib.parser',
		'ui.vue3.vuex',
		'main.popup',
		'im.v2.component.old-chat-embedding.elements',
		'im.v2.lib.logger',
		'im.v2.lib.utils',
		'main.core',
		'main.core.events',
		'im.v2.const',
	],
	'skip_core' => false,
];