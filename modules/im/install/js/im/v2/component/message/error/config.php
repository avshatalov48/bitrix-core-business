<?php


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

return [
	'css' => 'dist/error.bundle.css',
	'js' => 'dist/error.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.message.base',
		'im.v2.component.message.elements',
	],
	'skip_core' => true,
];