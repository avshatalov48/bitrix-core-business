<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die;
}

return [
	'css' => 'dist/item-list-selector.bundle.css',
	'js' => 'dist/item-list-selector.bundle.js',
	'rel' => [
		'main.popup',
		'ui.buttons',
		'ui.vue3',
		'ui.vue3.directives.hint',
		'ui.draganddrop.draggable',
		'ui.icon-set.actions',
		'ui.icon-set.api.vue',
		'ui.icon-set.outline',
		'ui.forms',
		'main.core',
		'ui.system.chip.vue',
		'ui.icon-set',
	],
	'skip_core' => false,
];
