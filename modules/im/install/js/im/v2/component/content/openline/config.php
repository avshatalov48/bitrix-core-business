<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/openline-content.bundle.css',
	'js' => 'dist/openline-content.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.dialog.chat',
		'im.v2.component.textarea',
		'im.v2.lib.logger',
	],
	'skip_core' => true,
];