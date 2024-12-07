<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot-creation.bundle.css',
	'js' => 'dist/copilot-creation.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.provider.service',
		'im.v2.component.message.base',
		'im.v2.component.elements',
	],
	'skip_core' => true,
];