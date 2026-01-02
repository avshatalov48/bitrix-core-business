<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/chip.bundle.css',
	'js' => 'dist/chip.bundle.js',
	'rel' => [
		'ui.icon-set.api.vue',
		'ui.icon-set.outline',
		'main.core',
		'ui.icon-set.api.core',
	],
	'skip_core' => false,
];
