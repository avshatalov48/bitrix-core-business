<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/recent-list.bundle.css',
	'js' => 'dist/recent-list.bundle.js',
	'rel' => [
		'im.v2.provider.service',
		'im.v2.lib.menu',
		'main.date',
		'ui.vue3',
		'ui.vue3.vuex',
		'main.popup',
		'im.v2.component.elements',
		'im.v2.lib.logger',
		'main.core',
		'im.v2.lib.utils',
		'main.core.events',
		'im.v2.const',
	],
	'skip_core' => false,
];