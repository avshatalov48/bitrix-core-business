<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'ui.notification',
		'im.v2.component.entity-selector',
		'im.v2.lib.local-storage',
		'im.v2.lib.menu',
		'im.v2.lib.rest',
		'im.v2.lib.feature',
		'im.v2.lib.analytics',
		'im.public',
		'im.v2.lib.call',
		'ui.vue3.directives.hint',
		'im.v2.component.animation',
		'im.v2.lib.utils',
		'ui.vue3',
		'im.v2.component.dialog.chat',
		'im.v2.component.textarea',
		'im.v2.lib.theme',
		'im.v2.lib.permission',
		'im.v2.lib.textarea',
		'im.v2.component.sidebar',
		'ui.uploader.core',
		'main.core',
		'main.core.events',
		'im.v2.lib.channel',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.component.elements',
		'im.v2.provider.service',
	],
	'skip_core' => false,
];
