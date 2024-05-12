<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/added-users.bundle.css',
	'js' => 'dist/added-users.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.public',
		'im.v2.component.message.base',
		'im.v2.component.elements',
	],
	'skip_core' => true,
];