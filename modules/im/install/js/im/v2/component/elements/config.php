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
		'im.v2.lib.parser',
		'im.v2.provider.service',
		'im.v2.application.core',
		'im.v2.lib.user',
		'ui.loader',
		'im.public',
		'im.v2.model',
		'im.v2.lib.logger',
		'main.popup',
		'dropdown.css',
		'main.core.events',
		'im.v2.lib.utils',
		'im.v2.lib.progressbar',
		'ui.icons.disk',
		'ui.vue3.directives.lazyload',
		'ui.vue3.components.audioplayer',
		'im.v2.const',
		'ui.vue3',
		'ui.vue3.components.socialvideo',
		'main.core',
	],
	'skip_core' => false,
];