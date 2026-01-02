<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/attach.bundle.css',
	'js' => 'dist/attach.bundle.js',
	'rel' => [
		'ui.icons.disk',
		'main.core',
		'im.v2.lib.parser',
		'im.v2.lib.utils',
		'rest.client',
		'im.v2.application.core',
		'im.v2.const',
		'ui.vue3.directives.lazyload',
	],
	'skip_core' => false,
];
