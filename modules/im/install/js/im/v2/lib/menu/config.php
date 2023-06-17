<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'ui.dialogs.messagebox',
		'im.v2.lib.call',
		'im.v2.provider.service',
		'im.v2.lib.utils',
		'im.public',
		'main.popup',
		'main.core.events',
		'ui.vue3.vuex',
		'rest.client',
		'im.v2.application.core',
		'im.v2.const',
		'main.core',
	],
	'skip_core' => false,
];