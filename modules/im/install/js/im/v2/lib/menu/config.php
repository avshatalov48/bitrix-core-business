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
		'im.v2.lib.channel',
		'call.lib.analytics',
		'main.popup',
		'ui.vue3.vuex',
		'rest.client',
		'main.core',
		'main.core.events',
		'im.public',
		'im.v2.lib.utils',
		'im.v2.application.core',
		'im.v2.provider.service',
		'im.v2.lib.confirm',
		'im.v2.lib.permission',
		'im.v2.const',
	],
	'skip_core' => false,
];