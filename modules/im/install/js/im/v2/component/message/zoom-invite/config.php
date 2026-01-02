<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/zoom-invite.bundle.css',
	'js' => 'dist/zoom-invite.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue3',
		'im.v2.lib.utils',
		'im.v2.component.message.call-invite',
	],
	'skip_core' => true,
];