<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'im.v2.lib.channel',
		'im.v2.component.elements',
		'ui.avatar',
		'im.v2.lib.copilot',
		'ui.icons.disk',
		'im.v2.lib.parser',
		'rest.client',
		'ui.loader',
		'im.v2.model',
		'ui.notification',
		'im.public',
		'im.v2.provider.service',
		'im.v2.lib.phone',
		'main.popup',
		'ui.forms',
		'main.core.events',
		'im.v2.lib.local-storage',
		'ui.vue3',
		'im.v2.lib.text-highlighter',
		'im.v2.lib.utils',
		'im.v2.lib.permission',
		'im.v2.lib.date-formatter',
		'im.v2.application.core',
		'im.v2.lib.user',
		'im.v2.lib.logger',
		'im.v2.const',
		'ui.lottie',
		'ai.roles-dialog',
		'ui.vue3.components.hint',
		'ui.fonts.opensans',
		'main.polyfill.intersectionobserver',
		'ui.vue3.directives.lazyload',
		'im.v2.component.animation',
		'main.core',
	],
	'skip_core' => false,
];
