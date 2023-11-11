<?php


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

return [
	'css' => 'dist/deleted.bundle.css',
	'js' => 'dist/deleted.bundle.js',
	'rel' => [
		'main.core',
		'im.v2.component.message.base',
		'im.v2.component.message.elements',
	],
	'skip_core' => false,
];