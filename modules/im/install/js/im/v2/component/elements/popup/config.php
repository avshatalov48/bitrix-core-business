<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/popup.bundle.css',
	'js' => 'dist/popup.bundle.js',
	'rel' => [
		'main.popup',
		'main.core',
		'im.v2.lib.logger',
	],
	'skip_core' => false,
];
