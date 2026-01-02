<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/chat-creation.bundle.css',
	'js' => 'dist/chat-creation.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue3.directives.hint',
		'im.public',
		'im.v2.component.elements.button',
		'im.v2.component.entity-selector',
		'im.v2.component.message.base',
		'im.v2.lib.call',
		'call.lib.analytics',
	],
	'skip_core' => true,
];