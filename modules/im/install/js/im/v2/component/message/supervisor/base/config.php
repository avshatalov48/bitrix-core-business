<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/supervisor-base.bundle.css',
	'js' => 'dist/supervisor-base.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.message.base',
	],
	'skip_core' => true,
];