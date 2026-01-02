<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot-roles-dialog.bundle.css',
	'js' => 'dist/copilot-roles-dialog.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ai.roles-dialog',
	],
	'skip_core' => true,
];
