<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/conference-creation.bundle.css',
	'js' => 'dist/conference-creation.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.notification',
		'im.public',
		'im.v2.component.elements',
		'im.v2.component.message.base',
	],
	'skip_core' => true,
];