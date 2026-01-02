<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'call.component.call-button',
		'im.v2.component.elements.loader',
		'im.v2.component.animation',
		'im.v2.component.elements.chat-title',
		'im.v2.component.elements.avatar',
		'im.v2.lib.utils',
		'ui.vue3',
		'im.v2.component.dialog.chat',
		'im.v2.component.textarea',
		'im.v2.lib.theme',
		'im.v2.lib.textarea',
		'im.v2.component.sidebar',
		'im.v2.lib.bulk-actions',
		'ui.uploader.core',
		'main.core',
		'im.v2.provider.service.uploading',
		'im.v2.provider.service.chat',
		'main.core.events',
		'ui.vue3.directives.hint',
		'im.v2.application.core',
		'im.v2.lib.analytics',
		'im.v2.const',
		'im.v2.lib.permission',
		'im.v2.component.elements.button',
		'im.v2.component.entity-selector',
		'im.v2.lib.confirm',
		'im.v2.provider.service.message',
	],
	'skip_core' => false,
];
