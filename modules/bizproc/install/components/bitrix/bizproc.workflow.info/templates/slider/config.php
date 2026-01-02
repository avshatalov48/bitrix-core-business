<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'script.css',
	'js' => 'script.js',
	'rel' => [
		'main.core.events',
		'ui.buttons',
		'bizproc.task',
		'ui.dialogs.messagebox',
		'main.core',
		'sidepanel',
	],
	'skip_core' => false,
];
