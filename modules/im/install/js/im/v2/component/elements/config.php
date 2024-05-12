<?php
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
		'ui.loader',
		'im.v2.model',
		'main.core.events',
		'ui.notification',
		'im.public',
		'im.v2.provider.service',
		'im.v2.lib.phone',
		'main.popup',
		'ui.forms',
		'ui.vue3.components.audioplayer',
		'ui.vue3',
		'im.v2.lib.text-highlighter',
		'im.v2.lib.utils',
		'im.v2.lib.permission',
		'main.core',
		'im.v2.lib.date-formatter',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.user',
		'im.v2.lib.logger',
	],
	'skip_core' => false,
];