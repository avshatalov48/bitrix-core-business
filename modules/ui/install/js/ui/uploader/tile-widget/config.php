<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/ui.uploader.tile-widget.bundle.js',
	'css' => 'dist/ui.uploader.tile-widget.bundle.css',
	'rel' => [
		'ui.uploader.vue',
		'ui.uploader.core',
		'ui.uploader.tile-widget',
		'ui.icons.generator',
		'main.popup',
		'ui.icon-set.outline',
		'ui.progressround',
		'main.core',
		'ui.icon-set.api.vue',
		'ui.icon-set.api.core',
		'ui.icon-set.actions',
	],
	'skip_core' => false,
];
