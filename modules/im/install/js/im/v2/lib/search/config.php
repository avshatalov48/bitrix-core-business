<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/search.bundle.js',
	],
	'rel' => [
		'main.core',
		'im.v2.lib.user',
		'ui.vue3.vuex',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.utils',
	],
	'skip_core' => false,
];