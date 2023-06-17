<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sound-notification-manager.bundle.css',
	'js' => 'dist/sound-notification-manager.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue3.vuex',
		'im.v2.application.core',
		'im.v2.lib.desktop',
		'im.v2.lib.call',
		'main.core.events',
		'im.v2.const',
	],
	'skip_core' => true,
];