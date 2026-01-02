<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/selectable-view-widget.bundle.css',
	'js' => 'dist/selectable-view-widget.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue3',
		'ui.entity-editor',
		'ui.vue3.components.button',
		'ui.uploader.core',
		'ui.uploader.tile-widget',
		'ui.fonts.inter',
		'ui.design-tokens',
	],
	'skip_core' => true,
];
