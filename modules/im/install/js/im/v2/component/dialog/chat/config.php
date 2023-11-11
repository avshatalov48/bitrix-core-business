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
		'im.v2.component.message.file',
		'im.v2.component.message.default',
		'im.v2.component.message.call-invite',
		'im.v2.component.message.deleted',
		'im.v2.component.message.unsupported',
		'im.v2.component.message.smile',
		'im.v2.component.message.system',
		'im.v2.component.message.chat-creation',
		'im.v2.component.message.conference-creation',
		'im.v2.lib.call',
		'im.v2.lib.smile-manager',
		'im.v2.lib.animation',
		'im.v2.lib.entity-creator',
		'im.v2.lib.market',
		'ui.notification',
		'im.public',
		'im.v2.lib.menu',
		'im.v2.lib.permission',
		'im.v2.lib.confirm',
		'im.v2.provider.service',
		'main.polyfill.intersectionobserver',
		'main.core.events',
		'im.v2.lib.rest',
		'im.v2.lib.parser',
		'im.v2.lib.date-formatter',
		'im.v2.component.elements',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.user',
		'im.v2.lib.logger',
		'main.core',
		'im.v2.lib.quote',
		'im.v2.lib.utils',
		'im.v2.lib.slider',
	],
	'skip_core' => false,
];