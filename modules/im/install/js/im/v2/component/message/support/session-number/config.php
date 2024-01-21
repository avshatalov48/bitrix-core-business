<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/session-number.js.bundle.css',
	'js' => 'dist/session-number.js.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.message.default',
	],
	'skip_core' => true,
];