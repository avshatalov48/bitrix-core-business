<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/file-message.bundle.css',
	'js' => 'dist/file-message.bundle.js',
	'rel' => [
		'im.v2.component.message.unsupported',
		'ui.vue3.directives.lazyload',
		'im.v2.model',
		'main.core.events',
		'im.v2.lib.progressbar',
		'im.v2.provider.service',
		'im.v2.lib.menu',
		'ui.icons.disk',
		'im.v2.lib.utils',
		'main.core',
		'im.v2.component.elements',
		'im.v2.component.message.elements',
		'im.v2.component.message.base',
		'im.v2.const',
	],
	'skip_core' => false,
];