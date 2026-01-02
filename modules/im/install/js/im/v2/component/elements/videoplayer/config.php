<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/videoplayer.bundle.css',
	'js' => 'dist/videoplayer.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.fonts.opensans',
		'main.polyfill.intersectionobserver',
		'ui.vue3.directives.lazyload',
		'im.v2.component.animation',
		'im.v2.lib.utils',
	],
	'skip_core' => true,
];
