<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/notifications.bundle.css',
	'js' => 'dist/notifications.bundle.js',
	'rel' => [
		'im.lib.animation',
		'ui.forms',
		'ui.design-tokens',
		'im.view.element.attach',
		'im.view.element.keyboard',
		'ui.vue',
		'main.core',
		'ui.vue.vuex',
		'im.lib.logger',
		'ui.vue.portal',
		'im.view.popup',
		'main.popup',
		'im.lib.utils',
		'im.const',
		'im.lib.timer',
		'main.core.events',
	],
	'skip_core' => false,
];