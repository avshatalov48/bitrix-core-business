<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/chat-dialog.bundle.css',
	'js' => 'dist/chat-dialog.bundle.js',
	'rel' => [
		'main.popup',
		'pull.vue3.status',
		'im.v2.lib.analytics',
		'im.v2.component.message-list',
		'im.v2.component.entity-selector',
		'im.v2.lib.call',
		'im.v2.lib.layout',
		'im.v2.lib.access',
		'im.v2.lib.feature',
		'im.v2.provider.service',
		'main.core.events',
		'im.v2.lib.logger',
		'im.v2.lib.animation',
		'im.v2.application.core',
		'im.v2.lib.rest',
		'im.v2.lib.channel',
		'im.v2.const',
		'im.v2.lib.permission',
		'im.v2.lib.parser',
		'main.core',
		'im.v2.lib.quote',
		'im.v2.lib.utils',
		'im.v2.lib.slider',
	],
	'skip_core' => false,
];