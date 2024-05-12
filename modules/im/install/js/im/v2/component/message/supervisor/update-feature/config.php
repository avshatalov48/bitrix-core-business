<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/update-feature.bundle.css',
	'js' => 'dist/update-feature.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.elements',
		'im.v2.component.message.supervisor.base',
	],
	'skip_core' => true,
];