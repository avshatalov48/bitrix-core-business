<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/ai-assistant-widget.bundle.js',
	'css' => 'dist/ai-assistant-widget.bundle.css',
	'rel' => [
		'main.polyfill.core',
		'im.v2.application.core',
		'im.v2.css.classes',
		'im.v2.lib.logger',
		'im.v2.provider.service.chat',
		'im.v2.component.content.chat',
		'im.v2.lib.message-notifier',
		'ui.icon-set.api.vue',
		'main.core.events',
		'im.v2.component.content.elements',
		'im.v2.const',
	],
	'skip_core' => true,
];
