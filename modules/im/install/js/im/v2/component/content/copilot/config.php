<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot-content.bundle.css',
	'js' => 'dist/copilot-content.bundle.js',
	'rel' => [
		'im.v2.lib.logger',
		'im.v2.lib.theme',
		'im.v2.lib.textarea',
		'ui.notification',
		'im.public',
		'im.v2.component.elements',
		'im.v2.const',
		'im.v2.provider.service',
		'im.v2.lib.draft',
		'im.v2.component.textarea',
		'main.core',
		'main.core.events',
		'im.v2.lib.desktop-api',
		'im.v2.component.dialog.chat',
		'ui.vue3',
		'im.v2.component.message-list',
	],
	'skip_core' => false,
];