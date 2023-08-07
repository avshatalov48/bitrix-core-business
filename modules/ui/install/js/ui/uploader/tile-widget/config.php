<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/ui.uploader.tile-widget.bundle.js',
	'css' => 'dist/ui.uploader.tile-widget.bundle.css',
	'rel' => [
		'main.core',
		'ui.design-tokens',
		'main.popup',
		'ui.uploader.vue',
		'ui.uploader.core',
		'ui.progressround',
		'ui.icons.generator',
	],
	'skip_core' => false,
];
