<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/registry.bundle.js',
	],
	'rel' => [
		'main.core.events',
		'im.v2.lib.user',
		'im.v2.lib.user-status',
		'im.v2.lib.logger',
		'im.v2.lib.channel',
		'im.v2.lib.utils',
		'im.v2.const',
		'im.v2.application.core',
		'ui.vue3.vuex',
		'main.core',
		'im.v2.model',
	],
	'skip_core' => false,
];