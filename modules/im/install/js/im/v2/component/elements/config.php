<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'ui.fonts.opensans',
		'ui.icons.disk',
		'im.v2.lib.parser',
		'rest.client',
		'ui.vue3.directives.lazyload',
		'im.v2.provider.service',
		'im.v2.application.core',
		'im.v2.lib.user',
		'ui.loader',
		'im.public',
		'im.v2.model',
		'im.v2.const',
		'im.v2.lib.logger',
		'main.popup',
		'ui.forms',
		'main.core',
		'ui.vue3.components.audioplayer',
		'ui.vue3',
		'im.v2.lib.text-highlighter',
		'im.v2.lib.utils',
	],
	'skip_core' => false,
];