<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/attached-result.bundle.css',
	'js' => 'dist/attached-result.bundle.js',
	'rel' => [
		'main.core',
		'ui.vue3.components.popup',
		'ui.icon-set.api.vue',
		'ui.icon-set.main',
		'ui.icon-set.animated',
		'ui.vue3',
	],
	'skip_core' => false,
];
