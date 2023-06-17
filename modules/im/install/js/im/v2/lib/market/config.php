<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/market.bundle.js',
	],
	'rel' => [
		'marketplace',
		'ui.vue3.vuex',
		'main.core',
		'im.v2.application.core',
		'im.v2.const',
	],
	'skip_core' => false,
];