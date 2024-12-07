<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/default.bundle.css',
	'js' => 'dist/default.bundle.js',
	'rel' => [
		'im.v2.component.message.elements',
		'im.v2.component.message.base',
		'main.core',
		'im.v2.lib.parser',
	],
	'skip_core' => false,
];