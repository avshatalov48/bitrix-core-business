<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/util.bundle.css',
	'js' => 'dist/util.bundle.js',
	'rel' => [
		'main.core',
		'main.date',
		'main.popup',
		'pull.client',
		'ui.dialogs.messagebox',
		'ui.notification',
	],
	'skip_core' => false,
];