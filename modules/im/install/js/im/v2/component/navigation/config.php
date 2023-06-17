<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/navigation.bundle.css',
	'js' => 'dist/navigation.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue3.directives.hint',
		'ui.dialogs.messagebox',
		'im.v2.lib.slider',
		'im.v2.lib.call',
		'im.v2.lib.utils',
		'im.v2.component.elements',
		'im.v2.lib.theme',
		'im.v2.lib.rest',
		'im.v2.lib.logger',
		'ui.buttons',
		'ui.feedback.form',
		'ui.fontawesome4',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.market',
	],
	'skip_core' => true,
];