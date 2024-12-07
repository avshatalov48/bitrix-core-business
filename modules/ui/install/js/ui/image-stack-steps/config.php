<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/image-stack-steps.bundle.css',
	'js' => 'dist/image-stack-steps.bundle.js',
	'rel' => [
		'ui.vue3',
		'main.core.events',
		'ui.tooltip',
		'ui.icon-set.api.vue',
		'main.core',
		'main.date',
		'ui.design-tokens',
	],
	'skip_core' => false,
];
