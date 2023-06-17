<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/notification-content.bundle.css',
	'js' => 'dist/notification-content.bundle.js',
	'rel' => [
		'main.core.events',
		'main.polyfill.intersectionobserver',
		'ui.dialogs.messagebox',
		'im.v2.provider.service',
		'im.v2.lib.utils',
		'im.v2.lib.parser',
		'im.public',
		'im.v2.component.elements',
		'im.v2.lib.date-formatter',
		'ui.forms',
		'ui.vue3.vuex',
		'im.v2.lib.user',
		'main.core',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.logger',
	],
	'skip_core' => false,
];