<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/ui.uploader.stack-widget.bundle.js',
	'css' => 'dist/ui.uploader.stack-widget.bundle.css',
	'rel' => [
		'main.core',
		'ui.design-tokens',
		'ui.fonts.opensans',
		'main.popup',
		'ui.uploader.vue',
		'ui.uploader.core',
		'ui.uploader.tile-widget',
		'ui.buttons',
		'ui.progressround',
	],
	'skip_core' => false,
];
