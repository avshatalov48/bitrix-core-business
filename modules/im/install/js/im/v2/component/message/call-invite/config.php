<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

return [
	'css' => 'dist/call-invite.bundle.css',
	'js' => 'dist/call-invite.bundle.js',
	'rel' => [
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