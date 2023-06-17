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
		'im.v2.component.message.base',
		'im.v2.component.message.chat-creation',
		'im.v2.lib.call',
		'im.v2.lib.animation',
		'im.v2.lib.entity-creator',
		'im.v2.lib.market',
		'ui.notification',
		'im.public',
		'im.v2.lib.menu',
		'im.v2.lib.utils',
		'im.v2.provider.service',
		'main.polyfill.intersectionobserver',
		'main.core.events',
		'im.v2.lib.parser',
		'main.core',
		'im.v2.lib.date-formatter',
		'im.v2.component.elements',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.user',
		'im.v2.lib.logger',
	],
	'skip_core' => false,
];