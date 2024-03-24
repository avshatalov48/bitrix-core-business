<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/zoom-invite.bundle.css',
	'js' => 'dist/zoom-invite.bundle.js',
	'rel' => [
		'ui.vue3',
		'im.public',
		'im.v2.const',
		'im.v2.lib.call',
		'im.v2.lib.permission',
		'main.core',
		'im.v2.component.elements',
		'im.v2.component.message.base',
		'im.v2.component.message.elements',
		'im.v2.lib.utils',
	],
	'skip_core' => false,
];