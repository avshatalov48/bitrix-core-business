<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/starter.bundle.css',
	'js' => 'dist/starter.bundle.js',
	'rel' => [
		'ui.entity-selector',
		'ui.notification',
		'sidepanel',
		'main.core',
		'ui.dialogs.messagebox',
		'main.core.events',
	],
	'skip_core' => false,
];
