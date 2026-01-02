<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/media-gallery.bundle.css',
	'js' => 'dist/media-gallery.bundle.js',
	'rel' => [
		'main.core',
		'ui.icon-set.api.core',
		'ui.vue3.directives.lazyload',
		'im.v2.const',
		'im.v2.lib.utils',
		'im.v2.component.elements.progressbar',
	],
	'skip_core' => false,
];
