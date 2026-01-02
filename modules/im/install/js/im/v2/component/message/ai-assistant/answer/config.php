<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/ai-assistant-answer.bundle.css',
	'js' => 'dist/ai-assistant-answer.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.message.base',
		'ui.icon-set.api.vue',
		'ui.vue3.components.rich-loc',
		'im.v2.lib.utils',
		'im.v2.lib.helpdesk',
		'im.v2.lib.notifier',
		'im.v2.const',
		'im.v2.component.message.elements',
	],
	'skip_core' => true,
];
